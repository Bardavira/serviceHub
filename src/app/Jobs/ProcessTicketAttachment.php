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

    public function __construct(public int $ticketId) {}

    public function handle(): void
    {
        $ticket = Ticket::query()
            ->with(['detail', 'user']) // user = assignee
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
        $mime = (string) ($ticket->attachment_mime ?? '');

        // Start from existing technical_data (array) or empty
        $technical = $ticket->detail->technical_data ?? [];

        // Build a normalized “attachment payload”
        $attachmentPayload = $this->parseAttachment($contents, $mime);

        // Merge in a safe namespace to avoid overwriting other keys
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

    private function parseAttachment(string $contents, string $mime): array
    {
        // Prefer JSON if mime says so, else try decoding
        $decoded = null;

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
            ];
        }

        $text = trim($contents);
        $preview = mb_substr($text, 0, 300);
        if (mb_strlen($text) > 300) {
            $preview .= '…';
        }

        return [
            'type' => 'text',
            'length' => strlen($text),
            'preview' => $preview,
        ];
    }
}