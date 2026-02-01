<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $stats = [
            'total_projects' => 0,
            'active_projects' => 0,
            'total_clients' => 0,
            'total_revenue' => 0,
        ];

        if ($user->hasRole('master')) {
            $stats['total_projects'] = Project::count();
            $stats['active_projects'] = Project::where('status', 'Running')->count();
            $stats['total_clients'] = Client::count();
            $stats['total_revenue'] = Payment::where('payment_status', 'Paid')->sum('amount');
        } elseif ($user->hasRole('admin')) {
            $stats['total_projects'] = Project::where('created_by', $user->id)->count();
            $stats['active_projects'] = Project::where('created_by', $user->id)->where('status', 'Running')->count();
            $stats['total_clients'] = Client::count(); // Simplified
            $stats['total_revenue'] = Payment::whereHas('project', function($q) use ($user) {
                $q->where('created_by', $user->id);
            })->where('payment_status', 'Paid')->sum('amount');
        } elseif ($user->hasRole('client')) {
            $client = $user->clientProfile;
            if ($client) {
                $stats['total_projects'] = Project::where('client_id', $client->id)->count();
                $stats['active_projects'] = Project::where('client_id', $client->id)->where('status', 'Running')->count();
                $stats['total_revenue'] = Payment::whereHas('project', function($q) use ($client) {
                    $q->where('client_id', $client->id);
                })->where('payment_status', 'Paid')->sum('amount');
            }
        } else {
            // User
            $stats['total_projects'] = $user->assignedProjects()->count();
            $stats['active_projects'] = $user->assignedProjects()->where('status', 'Running')->count();
        }

        // Get Recent Projects
        $recentProjects = Project::latest()->take(5);
        
        if ($user->hasRole('client') && $user->clientProfile) {
            $recentProjects->where('client_id', $user->clientProfile->id);
        } elseif ($user->hasRole('user')) {
             $recentProjects->whereHas('assignees', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $recentProjects = $recentProjects->get();

        return view('dashboard', compact('stats', 'recentProjects'));
    }
}
