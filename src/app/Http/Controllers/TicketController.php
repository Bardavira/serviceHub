<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyTicketAssignee;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Ticket;
use App\Models\Project;
use App\Models\TicketDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessTicketAttachment;

class TicketController extends Controller
{
    public function index()
    {
        $auth = Auth::user();

        $tickets = Ticket::query()
            ->with(['project:id,name', 'user:id,name'])
            ->whereHas('project', fn ($q) => $q->where('company_id', $auth->company_id))
            ->latest()
            ->paginate(10)
            ->through(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'description' => $t->description,
                'project' => $t->project,
                'assignee' => $t->user, // transparency on the UI
                'created_at' => $t->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create()
    {
        $auth = Auth::user();

        $projects = Project::query()
            ->where('company_id', $auth->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Assignee options: users from same company
        $assignees = User::query()
            ->where('company_id', $auth->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Tickets/Create', [
            'projects' => $projects,
            'assignees' => $assignees,
        ]);
    }

    public function store(Request $request)
    {
        $auth = Auth::user();

        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'], // <- assignee stored as tickets.user_id
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:2048', 'mimes:txt,json'],
        ]);

        // Scope checks: project and assignee must belong to same company as auth user
        $projectOk = Project::query()
            ->whereKey($data['project_id'])
            ->where('company_id', $auth->company_id)
            ->exists();

        $assigneeOk = User::query()
            ->whereKey($data['user_id'])
            ->where('company_id', $auth->company_id)
            ->exists();

        abort_unless($projectOk && $assigneeOk, 403);

        $stored = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $stored = [
                'path' => $file->store('ticket_attachments', 'local'),
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
            ];
        }

        $ticketId = DB::transaction(function () use ($data, $stored) {
            $ticket = Ticket::create([
                'project_id' => $data['project_id'],
                'user_id' => $data['user_id'], // assignee/responsible
                'title' => $data['title'],
                'description' => $data['description'],
                'attachment_path' => $stored['path'] ?? null,
                'attachment_original_name' => $stored['original_name'] ?? null,
                'attachment_mime' => $stored['mime'] ?? null,
            ]);

            TicketDetail::create([
                'ticket_id' => $ticket->id,
                'technical_data' => null,
            ]);

            return $ticket->id;
        });


        if(!empty($stored['path'])) {
            ProcessTicketAttachment::dispatch($ticketId)->afterCommit();
        }

        NotifyTicketAssignee::dispatch($ticketId)->afterCommit();

        return redirect()->route('tickets.index');
    }
}