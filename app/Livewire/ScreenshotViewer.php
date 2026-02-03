<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Screenshot;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class ScreenshotViewer extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $selectedUser = '';
    public $selectedDate = '';
    public $viewMode = 'grid'; // grid or list
    
    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }
    
    public function deleteScreenshot($id)
    {
        $screenshot = Screenshot::findOrFail($id);
        
        // Delete file
        \Storage::disk('public')->delete($screenshot->path);
        
        // Delete record
        $screenshot->delete();
        
        session()->flash('success', 'Screenshot deleted successfully.');
    }
    
    public function render()
    {
        $query = Screenshot::with(['user', 'attendance'])
            ->latest('captured_at');
        
        // Filter by user
        if ($this->selectedUser) {
            $query->where('user_id', $this->selectedUser);
        }
        
        // Filter by date
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate);
            $query->whereDate('captured_at', $date);
        }
        
        $screenshots = $query->paginate(24);
        
        $users = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['master', 'admin', 'user']);
        })->get();
        
        return view('livewire.screenshot-viewer', [
            'screenshots' => $screenshots,
            'users' => $users,
        ]);
    }
}
