<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingNote;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CoachingNoteController extends Controller
{
    /**
     * Display notes for a specific client
     */
    public function clientNotes(User $client)
    {
        // Authorize access to this client
        $this->authorize('view', $client);
        
        // Load the client with basic relationships
        $client->load(['company', 'profile', 'assignedCoach']);
        
        // Get all notes for this client with session information
        $notes = CoachingNote::where('client_id', $client->id)
            ->with(['session', 'coach', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Tenant/coach/client/ClientNotes', [
            'client' => $client,
            'notes' => $notes,
            'can' => [
                'update' => auth()->user()->can('update', $client),
                'delete' => auth()->user()->can('create', CoachingNote::class), // If they can create notes, they can delete their own
            ]
        ]);
    }

    /**
     * Display notes for a specific session
     */
    public function sessionNotes(CoachingSession $session)
    {
        // Authorize access to this session (through client)
        $this->authorize('view', $session->client);
        
        // Load session with relationships
        $session->load(['client', 'coach']);
        
        // Get notes for this session
        $notes = CoachingNote::where('session_id', $session->id)
            ->with(['coach', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Tenant/coach/coaching-session/SessionNotes', [
            'session' => $session,
            'notes' => $notes,
            'can' => [
                'update' => auth()->user()->can('update', $session->client),
                'delete' => auth()->user()->can('create', CoachingNote::class), // If they can create notes, they can delete their own
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // This could be used for a general notes listing if needed
        $this->authorize('viewAny', CoachingNote::class);
        
        $query = CoachingNote::with(['client', 'session', 'coach']);
        
        // Scope notes based on user role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see their own notes
            $query->where('coach_id', auth()->id());
        }
        
        $notes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return Inertia::render('Tenant/coach/coaching-notes/Index', [
            'notes' => $notes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', CoachingNote::class);
        
        // Handle different contexts (client or session)
        $client = null;
        $session = null;
        
        if ($request->has('client_id')) {
            $client = User::findOrFail($request->client_id);
            $this->authorize('view', $client);
        }
        
        if ($request->has('session_id')) {
            $session = CoachingSession::findOrFail($request->session_id);
            $this->authorize('view', $session->client);
            $client = $session->client;
        }

        //If no client or session, redirect back with an error
        if (!$client && !$session) {
            return redirect()->back()->with('error', 'No client or session found');
        }
        
        return Inertia::render('Tenant/coach/coaching-notes/CreateNote', [
            'client' => $client,
            'session' => $session
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', CoachingNote::class);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'client_id' => 'required|exists:users,id',
            'session_id' => 'nullable|exists:coaching_sessions,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);
        
        // Verify coach can access this client
        $client = User::findOrFail($request->client_id);
        $this->authorize('view', $client);
        
        $note = CoachingNote::create([
            'title' => $request->title,
            'content' => $request->content,
            'client_id' => $request->client_id,
            'session_id' => $request->session_id,
            'coach_id' => auth()->id(),
        ]);

        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('attachments', $fileName);

                $note->attachments()->create([
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        
        // Redirect based on context
        if ($request->session_id) {
            return to_route('tenant.coaching-sessions.notes', $request->session_id);
        } else {
            return to_route('tenant.clients.notes', $request->client_id);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CoachingNote $note)
    {
        $this->authorize('view', $note->client);
        
        $note->load(['client', 'session', 'coach', 'attachments']);
        
        return Inertia::render('Tenant/coach/coaching-notes/Show', [
            'note' => $note
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoachingNote $note)
    {
        $this->authorize('view', $note->client);
        $this->authorize('update', $note);
        
        $note->load(['client', 'session', 'attachments']);
        
        return Inertia::render('Tenant/coach/coaching-notes/EditNote', [
            'note' => $note,
            'client' => $note->client,
            'canDelete' => auth()->user()->can('delete', $note),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoachingNote $note)
    {
        $this->authorize('view', $note->client);
        $this->authorize('update', $note);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);
        
        $note->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('attachments', $fileName);

                $note->attachments()->create([
                    'original_name' => $originalName,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        
        // Redirect based on context
        if ($note->session_id) {
            return to_route('tenant.coaching-sessions.notes', $note->session_id);
        } else {
            return to_route('tenant.clients.notes', $note->client_id);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoachingNote $note)
    {
        $this->authorize('view', $note->client);
        $this->authorize('delete', $note);
        
        $clientId = $note->client_id;
        $sessionId = $note->session_id;
        
        $note->delete();
        
        // Redirect based on context
        if ($sessionId) {
            return to_route('tenant.coaching-sessions.notes', $sessionId);
        } else {
            return to_route('tenant.clients.notes', $clientId);
        }
    }
}
