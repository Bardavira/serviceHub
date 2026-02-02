<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Company;
use App\Models\Project;
use App\Jobs\NotifyTicketAssignee;
use App\Notifications\TicketAssigned;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotifyTicketAssigneeTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_ticket_assigned_notification_to_assignee(): void
    {
        Notification::fake();

        $company = Company::factory()->create();
        $project = Project::factory()->for($company, 'company')->create();
        $assignee = User::factory()->for($company, 'company')->create();

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'title' => 'Test',
            'description' => 'Test',
        ]);

        (new NotifyTicketAssignee($ticket->id))->handle();

        Notification::assertSentTo(
            $assignee,
            TicketAssigned::class,
            fn (TicketAssigned $n) => $n->ticketId === $ticket->id
        );
    }
}
