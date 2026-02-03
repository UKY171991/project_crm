<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $dateRange = 'today';
    public $userId = null;

    protected $listeners = ['attendance-updated' => '$refresh'];

    public function mount()
    {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) {
            $this->userId = Auth::id();
        }
    }

    public function render()
    {
        $query = Attendance::with('user')->latest();

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->dateRange == 'today') {
            $query->whereDate('date', Carbon::today());
        } elseif ($this->dateRange == 'week') {
            $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($this->dateRange == 'month') {
            $query->whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year);
        }

        $logs = $query->paginate(20);
        
        $users = [];
        if (Auth::user()->hasRole('master') || Auth::user()->hasRole('admin')) {
            $users = \App\Models\User::whereHas('role', function($q) {
                $q->whereIn('slug', ['master', 'admin', 'user']);
            })->get();
        }

        return view('livewire.attendance-manager', [
            'logs' => $logs,
            'users' => $users
        ]);
    }
}
