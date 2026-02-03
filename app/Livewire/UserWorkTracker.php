<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Screenshot;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserWorkTracker extends Component
{
    public $todaySeconds = 0;
    public $idleSeconds = 0;
    public $currentAttendanceId = null;

    protected $listeners = ['force-refresh' => 'refreshStats'];

    public function mount()
    {
        if (Auth::user() && Auth::user()->hasRole('client')) {
            return;
        }
        // Removed automatic clock-in from website. Done via Extension now.
        // $this->autoClockIn();
        // $this->refreshStats();
    }

    public function autoClockIn()
    {
        $user = Auth::user();
        if (!$user || $user->hasRole('client')) return;

        // Check if already clocked in for today
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if (!$activeSession) {
            $session = Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today(),
                'clock_in' => Carbon::now(),
            ]);
            $this->currentAttendanceId = $session->id;
        } else {
            $this->currentAttendanceId = $activeSession->id;
        }
    }

    public function refreshStats()
    {
        $user = Auth::user();
        if (!$user) return;

        $today = Carbon::today();
        
        $this->todaySeconds = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->sum('total_seconds');

        $this->idleSeconds = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->sum('idle_seconds');

        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if ($activeSession) {
            $this->currentAttendanceId = $activeSession->id;
            // Live calculation
            $liveTotal = Carbon::parse($activeSession->clock_in)->diffInSeconds(Carbon::now());
            // We update the record in DB to keep it synced
            $activeSession->update(['total_seconds' => $liveTotal]);
            
            $this->todaySeconds = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->sum('total_seconds');
        }
    }

    public function recordActivity($idleInc = 0)
    {
        if (Auth::user() && Auth::user()->hasRole('client')) return;
        
        if ($this->currentAttendanceId) {
            $attendance = Attendance::find($this->currentAttendanceId);
            if ($attendance) {
                $now = Carbon::now();
                $totalSec = Carbon::parse($attendance->clock_in)->diffInSeconds($now);
                
                $attendance->update([
                    'total_seconds' => $totalSec,
                    'idle_seconds' => $attendance->idle_seconds + $idleInc
                ]);
            }
        }
        $this->refreshStats();
    }

    public function uploadScreenshot($imageData)
    {
        try {
            \Log::info('Screenshot upload called', [
                'user_id' => Auth::id(),
                'attendance_id' => $this->currentAttendanceId,
                'data_length' => strlen($imageData)
            ]);

            if (!$this->currentAttendanceId) {
                \Log::warning('No current attendance ID for screenshot');
                return;
            }

            if (!Auth::check()) {
                \Log::warning('User not authenticated for screenshot');
                return;
            }

            // Handle different image formats (PNG or JPEG)
            $img = $imageData;
            $extension = 'png';
            
            if (strpos($imageData, 'data:image/jpeg;base64,') === 0) {
                $img = str_replace('data:image/jpeg;base64,', '', $imageData);
                $extension = 'jpg';
            } elseif (strpos($imageData, 'data:image/png;base64,') === 0) {
                $img = str_replace('data:image/png;base64,', '', $imageData);
                $extension = 'png';
            }

            $img = str_replace(' ', '+', $img);
            $fileName = 'ss_' . time() . '_' . Auth::id() . '.' . $extension;
            $path = 'screenshots/' . $fileName;
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($img));
            \Log::info('Screenshot file saved', ['path' => $path]);

            $screenshot = Screenshot::create([
                'user_id' => Auth::id(),
                'attendance_id' => $this->currentAttendanceId,
                'path' => $path,
                'captured_at' => Carbon::now()
            ]);
            
            \Log::info('Screenshot record created', ['id' => $screenshot->id]);
            
        } catch (\Exception $e) {
            \Log::error('Screenshot upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        $netSeconds = max(0, $this->todaySeconds - $this->idleSeconds);
        
        $hours = floor($netSeconds / 3600);
        $mins = floor(($netSeconds % 3600) / 60);
        $secs = $netSeconds % 60;
        
        $workDisplay = sprintf('%dh %dm %ds', $hours, $mins, $secs);

        return view('livewire.user-work-tracker', [
            'workDisplay' => $workDisplay,
            'idleDisplay' => floor($this->idleSeconds / 60) . 'm'
        ]);
    }
}
