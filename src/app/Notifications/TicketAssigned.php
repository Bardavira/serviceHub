<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param int $ticketId Ticket id used to build the email content.
     */
    public function __construct(public int $ticketId) {}

    /**
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticket = Ticket::query()
            ->with(['project:id,name', 'detail'])
            ->findOrFail($this->ticketId);

        $projectName = $ticket->project?->name ?? 'N/A';

        return (new MailMessage)
            ->subject("ServiceHub: Ticket #{$ticket->id} assigned")
            ->greeting("Hi {$notifiable->name},")
            ->line('A ticket was assigned to you.')
            ->line("Project: {$projectName}")
            ->line("Title: {$ticket->title}")
            ->line("Description: {$ticket->description}")
            ->action('Open Tickets', url('/tickets'))
            ->line('— ServiceHub');
    }

    /**
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
