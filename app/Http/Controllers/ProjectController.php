<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Project::query();

        if ($user->hasRole('master')) {
            // See all
        } elseif ($user->hasRole('admin')) {
            // See own created
            $query->where('created_by', $user->id);
        } elseif ($user->hasRole('client')) {
            // See own projects
            $query->where('client_id', $user->clientProfile->id);
        } else {
            // Normal User
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $projects = $query->with('client', 'mediaFiles')->latest()->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user actually has a client profile if they are a client
        if ($user->hasRole('client') && !$user->clientProfile) {
            abort(403, 'Client profile not found.');
        }

        $clients = [];
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $clients = Client::all();
        }

        return view('projects.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'client_id' => 'required_without:user_client_id|exists:clients,id',
        ]);

        $project = new Project($validated);
        $project->created_by = $user->id;

        if ($user->hasRole('client')) {
            $project->client_id = $user->clientProfile->id;
        } else {
            $project->client_id = $request->client_id;
        }

        $project->save();

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $user = Auth::user();

        // 1. Master: Can see everything
        if ($user->hasRole('master')) {
             // Access granted
        } 
        // 2. Admin: Can see projects they created
        elseif ($user->hasRole('admin')) {
            if ($project->created_by !== $user->id) {
                abort(403, 'Unauthorized. You can only view projects you created.');
            }
        } 
        // 3. Client: Can see only their own projects
        elseif ($user->hasRole('client')) {
             if ($project->client_id !== ($user->clientProfile->id ?? null)) {
                abort(403, 'Unauthorized. This project belongs to another client.');
            }
        } 
        // 4. User: Can see only assigned projects
        else {
             if (!$project->assignees()->where('user_id', $user->id)->exists()) {
                abort(403, 'Unauthorized. You are not assigned to this project.');
            }
        }
        
        $project->load('mediaFiles', 'payments', 'assignees');
        return view('projects.show', compact('project'));
    }
}
