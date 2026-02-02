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

    /**
     * @param int $ticketId Ticket id to notify the assignee for.
     */
    public function __construct(public int $ticketId) {}

    /**
     * Loads the ticket and notifies the assigned user.
     *
     * @return void
     */
    public function handle(): void
    {
        $ticket = Ticket::query()
            ->with('user')
            ->findOrFail($this->ticketId);

        // Ticket assignee can be null (invalid data or ticket unassigned).
        if (!$ticket->user) {
            return;
        }

        $ticket->user->notify(new TicketAssigned($ticket->id));
    }
}
