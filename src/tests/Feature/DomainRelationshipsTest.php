<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_many_projects(): void
    {
        $company = Company::factory()
            ->has(Project::factory()->count(2), 'projects')
            ->create();

        $this->assertCount(2, $company->projects);
        $this->assertTrue($company->projects->every(fn ($p) => $p->company_id === $company->id));
    }

    public function test_project_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->for($company, 'company')->create();

        $this->assertNotNull($project->company);
        $this->assertSame($company->id, $project->company->id);
    }

    public function test_project_has_many_tickets(): void
    {
        $project = Project::factory()->create();

        Ticket::factory()->count(3)->for($project, 'project')->create();

        $project->refresh();

        $this->assertCount(3, $project->tickets);
        $this->assertTrue($project->tickets->every(fn ($t) => $t->project_id === $project->id));
    }

    public function test_ticket_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $ticket = Ticket::factory()->for($project, 'project')->create();

        $this->assertNotNull($ticket->project);
        $this->assertSame($project->id, $ticket->project->id);
    }

    public function test_ticket_belongs_to_assignee_user(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($user, 'user')->create();

        $this->assertNotNull($ticket->user);
        $this->assertSame($user->id, $ticket->user->id);
    }

    public function test_ticket_has_one_ticket_detail(): void
    {
        $ticket = Ticket::factory()->create();

        TicketDetail::factory()->for($ticket, 'ticket')->create([
            'technical_data' => ['attachment' => ['type' => 'json', 'value' => ['x' => 1], 'meta' => []]],
        ]);

        $ticket->refresh();

        $this->assertNotNull($ticket->detail);
        $this->assertSame($ticket->id, $ticket->detail->ticket_id);
    }

    public function test_ticket_detail_belongs_to_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        $detail = TicketDetail::factory()->for($ticket, 'ticket')->create();

        $this->assertNotNull($detail->ticket);
        $this->assertSame($ticket->id, $detail->ticket->id);
    }

    public function test_user_has_one_profile(): void
    {
        $user = User::factory()->create();

        UserProfile::factory()->for($user, 'user')->create([
            'phone' => '+55 11 99999-9999',
            'role' => 'Technician',
        ]);

        $user->refresh();

        $this->assertNotNull($user->profile);
        $this->assertSame($user->id, $user->profile->user_id);
    }

    public function test_user_profile_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->for($user, 'user')->create();

        $this->assertNotNull($profile->user);
        $this->assertSame($user->id, $profile->user->id);
    }

    public function test_user_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company, 'company')->create();

        $this->assertNotNull($user->company);
        $this->assertSame($company->id, $user->company->id);
    }
}
