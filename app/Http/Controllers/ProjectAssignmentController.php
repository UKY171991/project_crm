<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectAssignmentController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userToAssign = User::findOrFail($request->user_id);
        $currentUser = auth()->user();

        // 1. Check Constraints
        if (!$currentUser->hasRole('master')) {
            // Count existing active projects
            $activeProjectsCount = $userToAssign->assignedProjects()
                ->whereIn('status', ['Pending', 'Running'])
                ->count();
                
            if ($activeProjectsCount >= 5) {
                return back()->with('error', 'User has reached the maximum limit of 5 active projects. Only Master User can override.');
            }
        }

        // 2. Assign
        $project->assignees()->attach($userToAssign->id, [
            'assigned_by' => $currentUser->id
        ]);

        return back()->with('success', 'User assigned successfully.');
    }

    public function destroy(Project $project, User $user)
    {
        $project->assignees()->detach($user->id);
        return back()->with('success', 'User unassigned successfully.');
    }
}
