<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Screenshot;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScreenshotController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'attendance_id' => 'required|exists:attendances,id',
                'image_data' => 'required|string'
            ]);

            // Decode base64 image
            $imageData = $request->image_data;
            
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
            
            $fileName = 'ss_' . time() . '_' . $request->user_id . '.' . $extension;
            $path = 'screenshots/' . $fileName;
            
            // Save to storage
            \Storage::disk('public')->put($path, base64_decode($img));

            // Create database record
            $screenshot = Screenshot::create([
                'user_id' => $request->user_id,
                'attendance_id' => $request->attendance_id,
                'path' => $path,
                'captured_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Screenshot uploaded successfully',
                'screenshot_id' => $screenshot->id
            ], 200)->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            \Log::error('Screenshot upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Screenshot upload failed',
                'error' => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    public function getActiveAttendance(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        // If no active session and auto_start is requested, create one
        if (!$attendance && $request->auto_start) {
            $attendance = Attendance::create([
                'user_id' => $request->user_id,
                'date' => Carbon::today(),
                'clock_in' => Carbon::now(),
            ]);
        }

        if ($attendance) {
            return response()->json([
                'success' => true,
                'attendance_id' => $attendance->id,
                'clocked_in' => true
            ])->header('Access-Control-Allow-Origin', '*');
        }

        return response()->json([
            'success' => false,
            'clocked_in' => false
        ])->header('Access-Control-Allow-Origin', '*');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (\Auth::attempt($credentials)) {
            $user = \Auth::user();
            return response()->json([
                'success' => true,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401)->header('Access-Control-Allow-Origin', '*');
    }

    public function getWorkStats(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $today = Carbon::today();
        
        // 1. Find ALL active sessions for today
        $activeSessions = Attendance::where('user_id', $request->user_id)
            ->whereNull('clock_out')
            ->get();

        foreach ($activeSessions as $session) {
            $liveTotal = Carbon::parse($session->clock_in)->diffInSeconds(Carbon::now());
            // Update each active session with live time
            $session->update(['total_seconds' => $liveTotal]);
        }

        // 2. Now calculate totals after syncing
        $totalSeconds = Attendance::where('user_id', $request->user_id)
            ->where('date', $today)
            ->sum('total_seconds');

        $idleSeconds = Attendance::where('user_id', $request->user_id)
            ->where('date', $today)
            ->sum('idle_seconds');

        $netSeconds = max(0, $totalSeconds - $idleSeconds);
        $hours = floor($netSeconds / 3600);
        $mins = floor(($netSeconds % 3600) / 60);
        $secs = $netSeconds % 60;
        
        $workDisplay = sprintf('%dh %dm %ds', $hours, $mins, $secs);

        return response()->json([
            'success' => true,
            'net_seconds' => $netSeconds,
            'work_display' => $workDisplay,
            'idle_display' => floor($idleSeconds / 60) . 'm'
        ])->header('Access-Control-Allow-Origin', '*');
    }

    public function clockOut(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully'
            ])->header('Access-Control-Allow-Origin', '*');
        }

        return response()->json([
            'success' => false,
            'message' => 'No active session found'
        ])->header('Access-Control-Allow-Origin', '*');
    }
}
