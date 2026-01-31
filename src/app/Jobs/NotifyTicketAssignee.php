<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use App\Notifications\TicketAssigned;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyTicketAssignee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $ticketId) {}

    public function handle(): void
    {
        $ticket = Ticket::query()
            ->with('user') // user = assignee
            ->findOrFail($this->ticketId);

        // Safety: user might be null if data got weird
        if (!$ticket->user) {
            return;
        }

        $ticket->user->notify(new TicketAssigned($ticket->id));
    }
}
