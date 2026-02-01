<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Project;
use App\Models\User;
use App\Models\MediaFile;
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

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function render()
    {
        // Reload relations for the view
        $this->project->load('mediaFiles', 'assignees', 'client');
        
        $assignableUsers = [];
        if (Auth::user()->hasRole('master') || Auth::user()->hasRole('admin')) {
             $assignableUsers = User::whereHas('role', function($q){ 
                $q->where('slug', 'user'); 
             })
             ->whereDoesntHave('assignedProjects', function($q) {
                 $q->where('project_id', $this->project->id);
             })
             ->get();
        }

        return view('livewire.project-detail-manager', [
            'assignableUsers' => $assignableUsers
        ]);
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
