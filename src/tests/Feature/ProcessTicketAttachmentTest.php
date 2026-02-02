<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Ticket;
use App\Models\Company;
use App\Models\Project;
use App\Models\TicketDetail;
use App\Models\User;
use App\Jobs\ProcessTicketAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessTicketAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_enriches_ticket_detail_with_json_attachment(): void
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $project = Project::factory()->for($company, 'company')->create();
        $assignee = User::factory()->for($company, 'company')->create();

        $path = 'ticket_attachments/sample-attachment.json';
        Storage::disk('local')->put($path, json_encode(['cpu' => 'i7', 'ram' => '16gb']));

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'attachment_path' => $path,
            'attachment_original_name' => 'sample-attachment.json',
            'attachment_mime' => 'application/json',
        ]);

        TicketDetail::factory()->create([
            'ticket_id' => $ticket->id,
            'technical_data' => [],
        ]);

        (new ProcessTicketAttachment($ticket->id))->handle();

        $ticket->refresh();

        $data = $ticket->detail->technical_data;

        $this->assertIsArray($data);
        $this->assertArrayHasKey('attachment', $data);
        $this->assertSame('application/json', $data['attachment']['mime']);
        $this->assertSame('sample-attachment.json', $data['attachment']['original_name']);
        $this->assertSame($path, $data['attachment']['path']);

        $this->assertSame('json', $data['attachment']['data']['type']);
        $this->assertSame(['cpu' => 'i7', 'ram' => '16gb'], $data['attachment']['data']['value']);
        $this->assertArrayHasKey('meta', $data['attachment']['data']);
    }

    public function test_job_enriches_ticket_detail_with_text_attachment(): void
    {
        Storage::fake('local');

        $company = Company::factory()->create();
        $project = Project::factory()->for($company, 'company')->create();
        $assignee = User::factory()->for($company, 'company')->create();

        $path = 'ticket_attachments/sample-attachment.txt';
        Storage::disk('local')->put($path, "hello\nworld\n");

        $ticket = Ticket::factory()->create([
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'attachment_path' => $path,
            'attachment_original_name' => 'sample-attachment.txt',
            'attachment_mime' => 'text/plain',
        ]);

        TicketDetail::factory()->create([
            'ticket_id' => $ticket->id,
            'technical_data' => [],
        ]);

        (new ProcessTicketAttachment($ticket->id))->handle();

        $ticket->refresh();

        $data = $ticket->detail->technical_data;

        $this->assertSame('text', $data['attachment']['data']['type']);
        $this->assertSame("hello\nworld", trim($data['attachment']['data']['value']));
        $this->assertArrayHasKey('meta', $data['attachment']['data']);
        $this->assertArrayHasKey('length', $data['attachment']['data']['meta']);
        $this->assertArrayHasKey('preview', $data['attachment']['data']['meta']);
    }
}
