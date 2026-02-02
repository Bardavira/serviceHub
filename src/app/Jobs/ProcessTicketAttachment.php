<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessTicketAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int $ticketId Ticket id to enrich technical_data for.
     */
    public function __construct(public int $ticketId) {}

    /**
     * Reads the stored attachment and merges a normalized payload into TicketDetail.technical_data.
     *
     * @return void
     */
    public function handle(): void
    {
        $ticket = Ticket::query()
            ->with(['detail'])
            ->findOrFail($this->ticketId);

        if (!$ticket->attachment_path) {
            return;
        }

        if (!$ticket->detail) {
            Log::warning('Ticket has no detail; cannot enrich technical_data', [
                'ticket_id' => $ticket->id,
            ]);
            return;
        }

        $disk = 'local';
        $path = $ticket->attachment_path;

        if (!Storage::disk($disk)->exists($path)) {
            Log::warning('Attachment not found', ['ticket_id' => $ticket->id, 'path' => $path]);
            return;
        }

        $contents = Storage::disk($disk)->get($path);
        $mime = $this->defineMime($ticket, $disk);

        // Start from existing technical_data (or empty).
        $technical = $ticket->detail->technical_data ?? [];

        // Normalize attachment payload into a consistent structure.
        $attachmentPayload = $this->parseAttachment($contents, $mime);

        // Store under a dedicated namespace to avoid overwriting other keys.
        $technical['attachment'] = [
            'processed_at' => now()->toIso8601String(),
            'mime' => $mime ?: null,
            'original_name' => $ticket->attachment_original_name ?? null,
            'path' => $path,
            'data' => $attachmentPayload,
        ];

        $ticket->detail->update([
            'technical_data' => $technical,
        ]);
    }

    /**
     * Resolves the attachment MIME type (stored value first, then Storage detection).
     *
     * @param Ticket $ticket
     * @param string $disk
     * @return string
     */
    public function defineMime(Ticket $ticket, string $disk): string
    {
        if ($ticket->attachment_mime) {
            return (string) $ticket->attachment_mime;
        }

        $detected = Storage::disk($disk)->mimeType($ticket->attachment_path);
        if ($detected) {
            return (string) $detected;
        }

        return '';
    }

    /**
     * Parses attachment contents into a consistent payload:
     * type + value + meta (text preview / json keys).
     *
     * @param string $contents
     * @param string $mime
     * @return array{type:string,value:mixed,meta:array<string,mixed>}
     */
    private function parseAttachment(string $contents, string $mime): array
    {
        $decoded = null;

        // Prefer JSON when MIME indicates it, otherwise attempt best-effort decoding.
        if (str_contains($mime, 'json')) {
            $decoded = json_decode($contents, true);
        } else {
            $try = json_decode($contents, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = $try;
            }
        }

        if (is_array($decoded)) {
            return [
                'type' => 'json',
                'value' => $decoded,
                'meta' => [
                    'keys' => array_slice(array_keys($decoded), 0, 20),
                ],
            ];
        }

        $text = trim($contents);

        $maxPreview = 300;
        $length = mb_strlen($text);

        // Keep a short preview to avoid storing large blobs.
        $preview = mb_substr($text, 0, $maxPreview);
        if ($length > $maxPreview) {
            $preview .= '…';
        }

        return [
            'type' => 'text',
            'value' => $text,
            'meta' => [
                'length' => $length,
                'preview' => $preview,
            ],
        ];
    }
}
