<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\User;
use App\Models\UserProfile;

use function Pest\Laravel\assertDatabaseHas;

it('company has many projects', function () {
    $company = Company::factory()->create();
    $projects = Project::factory()->count(2)->create([
        'company_id' => $company->id,
    ]);

    expect($company->projects)->toHaveCount(2);
    expect($company->projects->pluck('id')->all())->toEqualCanonicalizing($projects->pluck('id')->all());
});

it('project belongs to company', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->create([
        'company_id' => $company->id,
    ]);

    expect($project->company)->not->toBeNull();
    expect($project->company->id)->toBe($company->id);
});

it('project has many tickets', function () {
    $project = Project::factory()->create();
    $assignee = User::factory()->create(['company_id' => $project->company_id]);

    $tickets = Ticket::factory()->count(2)->create([
        'project_id' => $project->id,
        'user_id' => $assignee->id,
    ]);

    expect($project->tickets)->toHaveCount(2);
    expect($project->tickets->pluck('id')->all())->toEqualCanonicalizing($tickets->pluck('id')->all());
});

it('ticket belongs to project', function () {
    $project = Project::factory()->create();
    $assignee = User::factory()->create(['company_id' => $project->company_id]);

    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'user_id' => $assignee->id,
    ]);

    expect($ticket->project)->not->toBeNull();
    expect($ticket->project->id)->toBe($project->id);
});

it('ticket belongs to assignee user', function () {
    $project = Project::factory()->create();
    $assignee = User::factory()->create(['company_id' => $project->company_id]);

    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'user_id' => $assignee->id,
    ]);

    expect($ticket->user)->not->toBeNull();
    expect($ticket->user->id)->toBe($assignee->id);
});

it('ticket has one ticket detail', function () {
    $project = Project::factory()->create();
    $assignee = User::factory()->create(['company_id' => $project->company_id]);

    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'user_id' => $assignee->id,
    ]);

    $detail = TicketDetail::factory()->create([
        'ticket_id' => $ticket->id,
        'technical_data' => ['foo' => 'bar'],
    ]);

    expect($ticket->detail)->not->toBeNull();
    expect($ticket->detail->id)->toBe($detail->id);
    expect($ticket->detail->technical_data)->toMatchArray(['foo' => 'bar']);
});

it('ticket detail belongs to ticket', function () {
    $project = Project::factory()->create();
    $assignee = User::factory()->create(['company_id' => $project->company_id]);

    $ticket = Ticket::factory()->create([
        'project_id' => $project->id,
        'user_id' => $assignee->id,
    ]);

    $detail = TicketDetail::factory()->create([
        'ticket_id' => $ticket->id,
    ]);

    expect($detail->ticket)->not->toBeNull();
    expect($detail->ticket->id)->toBe($ticket->id);
});

it('user has one profile', function () {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($user->profile)->not->toBeNull();
    expect($user->profile->id)->toBe($profile->id);
    expect($user->profile->phone)->toBe($profile->phone);
    expect($user->profile->role)->toBe($profile->role);
});

it('user profile belongs to user', function () {
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($profile->user)->not->toBeNull();
    expect($profile->user->id)->toBe($user->id);
});

it('user belongs to company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    expect($user->company)->not->toBeNull();
    expect($user->company->id)->toBe($company->id);
});
