<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Project;
use App\Models\User;
use App\Models\MediaFile;
use App\Models\ProjectRemark;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProjectDetailManager extends Component
{
    use WithFileUploads;

    public $project;
    
    // Uploads
    public $photo;
    public $video;
    
    // Assignments
    public $user_to_assign;

    // Remarks
    public $new_remark;

    // Status Change
    public $requested_status;

    public function mount(Project $project)
    {
        $this->project = $project;
        // Initialize with current status
        $this->requested_status = $project->status;
    }

    public function render()
    {
        // Reload relations for the view
        $this->project->load('mediaFiles', 'assignees', 'client', 'projectRemarks.user');
        
        $assignableUsers = [];
        if (Auth::user()->hasRole('master') || Auth::user()->hasRole('admin')) {
             $assignableUsers = User::whereHas('role', function($q){ 
                $q->where('slug', 'user'); 
             })
             ->whereDoesntHave('assignedProjects', function($q) {
                 $q->where('project_id', $this->project->id);
             })
             ->where('is_active', true)
             ->get();
        }
        
        $pendingStatusChange = \App\Models\ProjectStatusChange::where('project_id', $this->project->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('livewire.project-detail-manager', [
            'assignableUsers' => $assignableUsers,
            'pendingStatusChange' => $pendingStatusChange
        ]);
    }

    public function requestStatusChange()
    {
        if (Auth::user()->hasRole('master') || Auth::user()->hasRole('admin')) {
            // Direct update for admin/master
            $this->project->status = $this->requested_status;
            $this->project->save();
            session()->flash('success', 'Project status updated successfully.');
        } else {
            // Create request for user
            if ($this->requested_status == $this->project->status) {
                return;
            }
            
            \App\Models\ProjectStatusChange::create([
                'project_id' => $this->project->id,
                'user_id' => Auth::id(),
                'old_status' => $this->project->status,
                'new_status' => $this->requested_status,
                'status' => 'pending'
            ]);

            // Notify Master and Admins
            $admins = User::whereHas('role', function($q) {
                $q->whereIn('slug', ['master', 'admin']);
            })->get();
            
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\ProjectStatusChangedNotification(
                $this->project,
                'New status change request for project: ' . $this->project->title . ' (by ' . Auth::user()->name . ')',
                route('projects.show', $this->project->id)
            ));
            
            session()->flash('success', 'Status change request submitted for approval.');
        }
    }

    public function approveStatusChange($changeId)
    {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $change = \App\Models\ProjectStatusChange::findOrFail($changeId);
        
        // Update project status
        $this->project->status = $change->new_status;
        $this->project->save();
        
        // Mark request as approved
        $change->update([
            'status' => 'approved',
            'processed_by' => Auth::id(),
            'processed_at' => now()
        ]);
        
        // Notification to the requester
        $change->user->notify(new \App\Notifications\ProjectStatusChangedNotification(
            $this->project,
            'Your status change request for project ' . $this->project->title . ' was APPROVED.',
            route('projects.show', $this->project->id)
        ));
        
        // Sync the component property
        $this->requested_status = $this->project->status;
        
        session()->flash('success', 'Status change approved.');
    }

    public function rejectStatusChange($changeId)
    {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $change = \App\Models\ProjectStatusChange::findOrFail($changeId);
        
        // Mark request as rejected
        $change->update([
            'status' => 'rejected',
            'processed_by' => Auth::id(),
            'processed_at' => now()
        ]);
        
        // Notification to the requester
        $change->user->notify(new \App\Notifications\ProjectStatusChangedNotification(
            $this->project,
            'Your status change request for project ' . $this->project->title . ' was REJECTED.',
            route('projects.show', $this->project->id)
        ));
        
        session()->flash('success', 'Status change rejected.');
    }

    public function addRemark()
    {
        $this->validate([
            'new_remark' => 'required|string|min:2'
        ]);

        ProjectRemark::create([
            'project_id' => $this->project->id,
            'user_id' => Auth::id(),
            'remark' => $this->new_remark
        ]);

        $this->new_remark = '';
        session()->flash('remark_success', 'Remark added successfully.');
    }

    public function deleteRemark($remarkId)
    {
        $remark = ProjectRemark::findOrFail($remarkId);
        
        if (!Auth::user()->hasRole('master') && Auth::id() != $remark->user_id) {
            abort(403);
        }

        $remark->delete();
        session()->flash('remark_success', 'Remark deleted.');
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:10240', // 10MB
        ]);

        $this->handleUpload($this->photo, 'image', 'images');
        $this->photo = null; 
        session()->flash('success', 'Image uploaded successfully.');
    }

    public function updatedVideo()
    {
        $this->validate([
            'video' => 'mimes:mp4,mov,qt,webm,mkv,avi,wmv|max:204800', // 200MB
        ]);

        $this->handleUpload($this->video, 'video', 'videos');
        $this->video = null;
        session()->flash('success', 'Video uploaded successfully.');
    }

    private function handleUpload($file, $type, $folderName)
    {
         $fileName = Str::random(20) . '.' . $file->extension();
         // Store on 'public' disk
         $path = $file->storeAs(
            "projects/{$this->project->id}/{$folderName}", 
            $fileName,
            'public'
        );

        MediaFile::create([
            'project_id' => $this->project->id,
            'file_type' => $type,
            'file_path' => $path, // Stored as "projects/1/images/xyz.jpg"
            'file_name' => $fileName,
            'size_kb' => round($file->getSize() / 1024),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function deleteMedia($mediaId)
    {
        $media = MediaFile::findOrFail($mediaId);
        
        // Authorization: Master, Admin, or Uploader
        if(!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin') && $media->uploaded_by != Auth::id()) {
            abort(403);
        }

        if(Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }
        $media->delete();
        session()->flash('success', 'File deleted.');
    }

    public function assignUser()
    {
        $this->validate([
            'user_to_assign' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($this->user_to_assign);
        $currentUser = Auth::user();

        // Limit Check
        if (!$currentUser->hasRole('master')) {
             $activeCount = $user->assignedProjects()
                ->whereIn('status', ['Pending', 'Running'])
                ->count();
            
            if ($activeCount >= 5) {
                $this->addError('user_to_assign', 'User has max 5 active projects.');
                return;
            }
        }

        $this->project->assignees()->attach($user->id, [
            'assigned_by' => $currentUser->id
        ]);

        $this->user_to_assign = ''; // Reset
        session()->flash('success', 'User assigned.');
    }

    public function removeUser($userId)
    {
        $this->project->assignees()->detach($userId);
        session()->flash('success', 'User removed.');
    }
}
