<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Jobs\NotifyTicketAssignee;
use App\Jobs\ProcessTicketAttachment;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketController extends Controller
{
    /**
     * Lists tickets scoped to the authenticated user's company.
     *
     * @return \Inertia\Response
     */
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
                'assignee' => $t->user,
                'created_at' => $t->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Index', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Shows the ticket creation form with projects and assignee options scoped to the company.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $auth = Auth::user();

        $projects = Project::query()
            ->where('company_id', $auth->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $assignees = User::query()
            ->where('company_id', $auth->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Create', [
            'projects' => $projects,
            'assignees' => $assignees,
        ]);
    }

    /**
     * Shows a single ticket (scoped by authenticated user's company).
     *
     * @param Ticket $ticket
     * @return \Inertia\Response
     */
    public function show(Ticket $ticket)
    {
        $auth = Auth::user();

        $ticket = Ticket::query()
            ->with(['project:id,name,company_id', 'user:id,name', 'detail'])
            ->whereKey($ticket->id)
            ->whereHas('project', fn ($q) => $q->where('company_id', $auth->company_id))
            ->firstOrFail();

        return Inertia::render('Show', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'created_at' => $ticket->created_at?->toDateTimeString(),
                'project' => $ticket->project,
                'assignee' => $ticket->user,
                'attachment' => [
                    'original_name' => $ticket->attachment_original_name,
                    'mime' => $ticket->attachment_mime,
                    'path' => $ticket->attachment_path,
                ],
                'detail' => [
                    'technical_data' => $ticket->detail?->technical_data,
                ],
            ],
        ]);
    }

    /**
     * Persists a new ticket, optionally stores an attachment, then dispatches background jobs.
     *
     * @param StoreTicketRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTicketRequest $request)
    {
        $data = $request->validated();

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
                'user_id' => $data['user_id'],
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

        if (!empty($stored['path'])) {
            ProcessTicketAttachment::dispatch($ticketId)->afterCommit();
        }

        NotifyTicketAssignee::dispatch($ticketId)->afterCommit();

        return redirect()->route('tickets.index');
    }
}
