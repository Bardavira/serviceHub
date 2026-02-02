<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Company;
use App\Jobs\NotifyTicketAssignee;
use App\Jobs\ProcessTicketAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketCreationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_ticket_and_jobs_are_dispatched(): void
    {
        Storage::fake('local');
        Bus::fake();

        $company = Company::factory()->create();
        $auth = User::factory()->for($company, 'company')->create();

        $assignee = User::factory()->for($company, 'company')->create();

        $project = Project::factory()->for($company, 'company')->create();

        $payload = [
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'title' => 'Printer not working',
            'description' => 'Office printer is jammed.',
            'attachment' => UploadedFile::fake()->createWithContent(
                'sample-attachment.json',
                json_encode(['device' => 'printer', 'severity' => 'high'])
            ),
        ];

        $this->actingAs($auth)
            ->post(route('tickets.store'), $payload)
            ->assertStatus(302);

        $ticket = Ticket::query()->latest('id')->firstOrFail();

        $this->assertSame($project->id, $ticket->project_id);
        $this->assertSame($assignee->id, $ticket->user_id);
        $this->assertNotNull($ticket->attachment_path);

        $this->assertDatabaseHas('ticket_details', [
            'ticket_id' => $ticket->id,
        ]);

        Bus::assertDispatched(NotifyTicketAssignee::class, fn ($job) => $job->ticketId === $ticket->id);
        Bus::assertDispatched(ProcessTicketAttachment::class, fn ($job) => $job->ticketId === $ticket->id);

        Storage::disk('local')->assertExists($ticket->attachment_path);
    }

    public function test_create_ticket_without_attachment_only_dispatches_notify_job(): void
    {
        Bus::fake();

        $company = Company::factory()->create();
        $auth = User::factory()->for($company, 'company')->create();
        $assignee = User::factory()->for($company, 'company')->create();
        $project = Project::factory()->for($company, 'company')->create();

        $payload = [
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'title' => 'Network down',
            'description' => 'No internet access.',
        ];

        $this->actingAs($auth)
            ->post(route('tickets.store'), $payload)
            ->assertStatus(302);

        $ticket = Ticket::query()->latest('id')->firstOrFail();

        Bus::assertDispatched(NotifyTicketAssignee::class, fn ($job) => $job->ticketId === $ticket->id);
        Bus::assertNotDispatched(ProcessTicketAttachment::class);
    }
}
