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
use App\Services\WhatsAppService;

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

    protected function whatsapp(): WhatsAppService
    {
        return new WhatsAppService();
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
            // Use exactly the status the user selected
            $newStatus = $this->requested_status;

            $oldStatus = $this->project->status;

            // Direct DB update — guaranteed to persist
            Project::where('id', $this->project->id)->update(['status' => $newStatus]);

            // Reload project for WhatsApp notification
            $updatedProject = Project::with('client')->findOrFail($this->project->id);

            // Send WhatsApp notification to client
            if ($updatedProject->client && $updatedProject->client->phone) {
                $this->whatsapp()->sendProjectStatusUpdate(
                    $updatedProject->client,
                    $updatedProject,
                    $oldStatus,
                    $newStatus
                );
            }

            // Redirect to reload page fresh from DB
            session()->flash('success', 'Project status updated to "' . $newStatus . '" successfully.');
            return redirect()->route('projects.show', $this->project->id);

        } else {
            // Non-admin: create a status change request
            if ($this->requested_status === $this->project->status) {
                return;
            }

            \App\Models\ProjectStatusChange::create([
                'project_id' => $this->project->id,
                'user_id'    => Auth::id(),
                'old_status' => $this->project->status,
                'new_status' => $this->requested_status,
                'status'     => 'pending'
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
            return redirect()->route('projects.show', $this->project->id);
        }
    }

    public function approveStatusChange($changeId)
    {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $change = \App\Models\ProjectStatusChange::findOrFail($changeId);

        // Use exactly the status from the approved request
        $newStatus = $change->new_status;
        $oldStatus = $this->project->status;

        // Direct DB update — guaranteed to persist
        Project::where('id', $this->project->id)->update(['status' => $newStatus]);

        // Mark request as approved
        $change->update([
            'status'       => 'approved',
            'processed_by' => Auth::id(),
            'processed_at' => now()
        ]);

        // Reload for notifications
        $updatedProject = Project::with('client')->findOrFail($this->project->id);

        // Send WhatsApp notification to client
        if ($updatedProject->client && $updatedProject->client->phone) {
            $this->whatsapp()->sendProjectStatusUpdate(
                $updatedProject->client,
                $updatedProject,
                $oldStatus,
                $newStatus
            );
        }

        // Notification to the requester
        $change->user->notify(new \App\Notifications\ProjectStatusChangedNotification(
            $updatedProject,
            'Your status change request for project ' . $updatedProject->title . ' was APPROVED.',
            route('projects.show', $updatedProject->id)
        ));

        // Redirect to reload page fresh from DB
        session()->flash('success', 'Status change approved. Project is now "' . $newStatus . '".');
        return redirect()->route('projects.show', $this->project->id);
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

    public function sendManualNotification()
    {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $project = Project::with('client')->findOrFail($this->project->id);
        $client = $project->client;

        if (!$client) {
            session()->flash('error', 'Client information not found.');
            return;
        }

        $whatsappSent = false;
        $email = $client->user ? $client->user->email : $client->email;
        $emailSent = false;

        // Ensure we have current data
        $project->refresh();

        // 1. Send WhatsApp if phone exists
        if ($client->phone) {
            $whatsappSent = $this->whatsapp()->sendProjectStatusUpdate(
                $client,
                $project,
                $project->status, 
                $project->status
            );
        }

        // 2. Send Email if email exists
        if ($email) {
            $messageBody = "Your project '{$project->title}' is currently '{$project->status}'.";
            $actionUrl = route('projects.show', $project->id);
            
            // If the client has a user account
            if ($client->user) {
                $client->user->notify(new \App\Notifications\ProjectStatusChangedNotification(
                    $project,
                    $messageBody,
                    $actionUrl
                ));
                $emailSent = true;
            } else {
                // Otherwise fallback to a standard notification if possible
                // For simplicity, we assume they have a user or email
                \Illuminate\Support\Facades\Notification::route('mail', $email)
                    ->notify(new \App\Notifications\ProjectStatusChangedNotification($project, $messageBody, $actionUrl));
                $emailSent = true;
            }
        }

        if ($whatsappSent && $emailSent) {
            session()->flash('success', 'Status notification sent via both WhatsApp and Email.');
        } elseif ($whatsappSent) {
            session()->flash('success', 'Status notification sent via WhatsApp.');
        } elseif ($emailSent) {
            session()->flash('success', 'Status notification sent via Email.');
        } else {
            session()->flash('error', 'No contact information available (Email or WhatsApp).');
        }
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
            'video' => 'required|mimes:mp4,mov,qt,webm,mkv,avi,wmv,flv,3gp|max:512000', // Increased to 500MB
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
