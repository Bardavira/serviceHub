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
     * Create a new notification instance.
     */
    public function __construct(public int $ticketId) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticket = Ticket::query()
            ->with(['project:id,name', 'detail'])
            ->findOrFail($this->ticketId);

        return (new MailMessage)
            ->subject("ServiceHub: Ticket #{$ticket->id} enriched")
            ->greeting("Hi {$notifiable->name},")
            ->line("A ticket was assigned to you.")
            ->line("Project: {$ticket->project?->name}")
            ->line("Title: {$ticket->title}")
            ->line("Description: {$ticket->description}")
            ->action('Open Ticket List', url('/tickets'))
            ->line('— ServiceHub');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}