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

            // UPDATE ATTENDANCE SYNC
            $attendance = Attendance::find($request->attendance_id);
            if ($attendance && !$attendance->clock_out) {
                $totalSecs = Carbon::parse($attendance->clock_in)->diffInSeconds(Carbon::now());
                $attendance->update([
                    'total_seconds' => $totalSecs,
                    'updated_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Screenshot uploaded successfully',
                'screenshot_id' => $screenshot->id
            ], 200)->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            \Log::error('Screenshot upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    public function heartbeat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'attendance_id' => 'required|exists:attendances,id',
        ]);

        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', $request->user_id)
            ->whereNull('clock_out')
            ->first();

        if ($attendance) {
            $totalSecs = Carbon::parse($attendance->clock_in)->diffInSeconds(Carbon::now());
            $attendance->update([
                'total_seconds' => $totalSecs,
                'updated_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'net_seconds' => $totalSecs
            ])->header('Access-Control-Allow-Origin', '*');
        }

        return response()->json(['success' => false, 'message' => 'No active attendance found'], 404)
            ->header('Access-Control-Allow-Origin', '*');
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
            'message' => 'Invalid email or password'
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
        
        // Find ALL sessions for today to sum up total work
        $totalSeconds = Attendance::where('user_id', $request->user_id)
            ->where('date', $today)
            ->sum('total_seconds');

        // Check for active one to add live time
        $active = Attendance::where('user_id', $request->user_id)
            ->whereNull('clock_out')
            ->first();
            
        $idleSeconds = Attendance::where('user_id', $request->user_id)
            ->where('date', $today)
            ->sum('idle_seconds');

        if ($active) {
            $liveTime = Carbon::parse($active->clock_in)->diffInSeconds(Carbon::now());
            // We sum the previous ones + the current live one
            $otherSeconds = Attendance::where('user_id', $request->user_id)
                ->where('date', $today)
                ->where('id', '!=', $active->id)
                ->sum('total_seconds');
            
            $totalSeconds = $otherSeconds + $liveTime;
        }

        $netSeconds = max(0, $totalSeconds - $idleSeconds);
        $hours = floor($netSeconds / 3600);
        $minutes = floor(($netSeconds % 3600) / 60);
        $seconds = $netSeconds % 60;

        return response()->json([
            'success' => true,
            'total_seconds' => $totalSeconds,
            'net_seconds' => $netSeconds,
            'work_display' => "{$hours}h {$minutes}m {$seconds}s"
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
            $now = Carbon::now();
            $totalSecs = Carbon::parse($attendance->clock_in)->diffInSeconds($now);
            $attendance->update([
                'clock_out' => $now,
                'total_seconds' => $totalSecs
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
