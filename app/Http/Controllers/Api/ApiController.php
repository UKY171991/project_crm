<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        $stats = [
            'total_projects' => 0,
            'running_projects' => 0,
            'completed_projects' => 0,
            'total_clients' => 0,
            'total_non_clients' => 0,
            'total_websites' => 0,
        ];

        $projectQuery = Project::query();
        if ($user->hasRole('admin')) { $projectQuery->where('created_by', $user->id); }
        elseif ($user->hasRole('client')) { $projectQuery->where('client_id', $user->clientProfile->id ?? 0); }
        elseif ($user->hasRole('user')) { $projectQuery->whereHas('assignees', function($q) use ($user) { $q->where('user_id', $user->id); }); }

        $stats['total_projects'] = (clone $projectQuery)->count();
        $stats['running_projects'] = (clone $projectQuery)->where('status', 'Running')->count();
        $stats['completed_projects'] = (clone $projectQuery)->where('status', 'Completed')->count();

        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $stats['total_clients'] = Client::has('projects')->count();
            $stats['total_non_clients'] = Client::doesntHave('projects')->count();
            $stats['total_websites'] = Website::count();
        } elseif ($user->hasRole('client')) {
            $stats['total_clients'] = 1;
            $stats['total_websites'] = Website::where('client_id', $user->clientProfile->id ?? 0)->count();
        }

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    public function projects(Request $request)
    {
        $user = Auth::user();
        $query = Project::with(['client', 'assignees']);

        if ($user->hasRole('admin')) { 
            $query->where('created_by', $user->id); 
        } elseif ($user->hasRole('client')) { 
            $query->where('client_id', $user->clientProfile->id ?? 0); 
        } elseif ($user->hasRole('user')) { 
            $query->whereHas('assignees', function($q) use ($user) { 
                $q->where('user_id', $user->id); 
            }); 
        }

        $projects = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects
        ]);
    }

    public function websites(Request $request)
    {
        $user = Auth::user();
        $query = Website::with('client');

        if ($user->hasRole('client')) {
            $query->where('client_id', $user->clientProfile->id ?? 0);
        }

        $websites = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $websites
        ]);
    }

    public function nonClients(Request $request)
    {
        $user = Auth::user();

        // Allow all users to see non-clients (leads)


        $clients = Client::with(['user', 'feedbacks.creator'])
            ->doesntHave('projects')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $clients
        ]);
    }
    public function viewNonClient($id)
    {
        $client = Client::with(['user', 'feedbacks.creator'])
            ->doesntHave('projects')
            ->find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lead not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    public function updateNonClientStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|max:255',
        ]);

        $client = Client::doesntHave('projects')->find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lead not found'
            ], 404);
        }

        $client->status = $request->status;
        $client->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully',
            'data' => $client
        ]);
    }

    public function addNonClientFeedback(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string',
            'status' => 'required|string|max:255',
            'next_schedule' => 'nullable|date',
        ]);

        $client = Client::doesntHave('projects')->find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lead not found'
            ], 404);
        }

        $feedback = \App\Models\ClientFeedback::create([
            'client_id' => $client->id,
            'feedback' => $request->feedback,
            'status' => $request->status,
            'next_schedule' => $request->next_schedule,
            'created_by' => Auth::id(),
        ]);

        // Automatically update the main lead status when new feedback is added
        $client->status = $request->status;
        $client->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback added successfully',
            'data' => $feedback->load('creator')
        ]);
    }
}
