<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_create_ticket_for_project_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $auth = User::factory()->for($companyA, 'company')->create();
        $assigneeA = User::factory()->for($companyA, 'company')->create();

        $projectB = Project::factory()->for($companyB, 'company')->create();

        $payload = [
            'project_id' => $projectB->id,
            'user_id' => $assigneeA->id,
            'title' => 'Bad scope',
            'description' => 'Should be forbidden',
        ];

        $this->actingAs($auth)
            ->post(route('tickets.store'), $payload)
            ->assertStatus(403);
    }

    public function test_user_cannot_view_ticket_from_other_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $auth = User::factory()->for($companyA, 'company')->create();

        $projectB = Project::factory()->for($companyB, 'company')->create();
        $assigneeB = User::factory()->for($companyB, 'company')->create();

        $ticketB = Ticket::factory()->create([
            'project_id' => $projectB->id,
            'user_id' => $assigneeB->id,
            'title' => 'Other company ticket',
            'description' => 'No access',
        ]);

        $this->actingAs($auth)
            ->get(route('tickets.show', $ticketB))
            ->assertStatus(404); // your controller uses firstOrFail() after scoping
    }
}
