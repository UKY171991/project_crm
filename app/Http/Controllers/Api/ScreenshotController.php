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
                'image_data' => 'nullable|string',
                'image' => 'nullable|string',
                'captured_at' => 'nullable|date'
            ]);

            // Decode base64 image - support both field names
            $imageData = $request->image_data ?? $request->image;
            
            if (!$imageData) {
                return response()->json(['success' => false, 'message' => 'No image data provided'], 400);
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
            
            // Use captured_at timestamp if provided, otherwise use current time
            $capturedAt = $request->captured_at ? Carbon::parse($request->captured_at) : Carbon::now();
            
            $fileName = 'ss_' . $capturedAt->timestamp . '_' . $request->user_id . '.' . $extension;
            $path = 'screenshots/' . $fileName;
            
            // Check for duplicate screenshots (same user, attendance, within 5 seconds)
            $recentDuplicate = Screenshot::where('user_id', $request->user_id)
                ->where('attendance_id', $request->attendance_id)
                ->where('captured_at', '>=', $capturedAt->copy()->subSeconds(5))
                ->where('captured_at', '<=', $capturedAt->copy()->addSeconds(5))
                ->first();
            
            if ($recentDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate screenshot detected (within 5 seconds)',
                    'screenshot_id' => $recentDuplicate->id
                ], 200)->header('Access-Control-Allow-Origin', '*');
            }
            
            // Save to storage
            \Storage::disk('public')->put($path, base64_decode($img));

            // Create database record
            $screenshot = Screenshot::create([
                'user_id' => $request->user_id,
                'attendance_id' => $request->attendance_id,
                'path' => $path,
                'captured_at' => $capturedAt
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
                'screenshot_id' => $screenshot->id,
                'captured_at' => $capturedAt->toIso8601String()
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
            $user->load('role'); // Load role relationship
            
            return response()->json([
                'success' => true,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->slug : 'user'
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

    public function activityTrack(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'attendance_id' => 'required|exists:attendances,id',
                'url' => 'required|string',
                'title' => 'nullable|string',
                'tracked_at' => 'nullable|date',
                'type' => 'nullable|string'
            ]);

            $trackedAt = $request->tracked_at ? Carbon::parse($request->tracked_at) : Carbon::now();

            // Check for duplicate activity (same URL within 30 seconds)
            $recentDuplicate = \DB::table('activity_logs')
                ->where('user_id', $request->user_id)
                ->where('attendance_id', $request->attendance_id)
                ->where('url', $request->url)
                ->where('tracked_at', '>=', $trackedAt->copy()->subSeconds(30))
                ->where('tracked_at', '<=', $trackedAt->copy()->addSeconds(30))
                ->first();

            if ($recentDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate activity detected'
                ], 200)->header('Access-Control-Allow-Origin', '*');
            }

            // Store activity log
            \DB::table('activity_logs')->insert([
                'user_id' => $request->user_id,
                'attendance_id' => $request->attendance_id,
                'url' => $request->url,
                'title' => $request->title ?? 'Unknown',
                'type' => $request->type ?? 'url',
                'tracked_at' => $trackedAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Update attendance sync
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
                'message' => 'Activity tracked successfully'
            ], 200)->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            \Log::error('Activity tracking failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Tracking failed: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    public function getProjects(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role' => 'nullable|string'
            ]);

            $user = \App\Models\User::with('role')->find($request->user_id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404)->header('Access-Control-Allow-Origin', '*');
            }

            $roleSlug = $request->role ?? ($user->role ? $user->role->slug : 'user');

            \Log::info('Fetching projects for user', [
                'user_id' => $request->user_id,
                'role_slug' => $roleSlug
            ]);

            // If master/admin, show all pending/running projects
            // If regular user, show only their assigned projects
            $query = \App\Models\Project::with('client')
                ->whereIn('status', ['Pending', 'Running']);

            if ($roleSlug !== 'master' && $roleSlug !== 'admin') {
                // Regular user - only their projects
                $query->whereHas('assignees', function($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            }

            $projects = $query->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function($project) {
                    return [
                        'id' => $project->id,
                        'name' => $project->title ?? $project->name ?? 'Untitled',
                        'status' => $project->status,
                        'client_name' => $project->client->name ?? 'N/A'
                    ];
                });

            \Log::info('Projects fetched', [
                'count' => $projects->count(),
                'role' => $roleSlug
            ]);

            return response()->json([
                'success' => true,
                'projects' => $projects,
                'role' => $roleSlug
            ])->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            \Log::error('Get projects failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch projects: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    public function getPendingPayments(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
        }

        try {
            // Get pending payments
            $payments = \App\Models\Payment::with(['project.client'])
                ->where('status', 'Pending')
                ->orderBy('payment_date', 'asc')
                ->limit(10)
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => number_format($payment->amount, 2),
                        'currency' => $payment->currency ?? '$',
                        'project_name' => $payment->project->name ?? 'N/A',
                        'client_name' => $payment->project->client->name ?? 'N/A',
                        'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('M d, Y') : 'N/A'
                    ];
                });

            return response()->json([
                'success' => true,
                'payments' => $payments
            ])->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            \Log::error('Get pending payments failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments: ' . $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }
}
