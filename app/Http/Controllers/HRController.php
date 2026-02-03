<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSalary;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HRController extends Controller
{
    public function generateSalarySlip(Request $request)
    {
        $userId = $request->user_id;
        $monthYear = $request->month; // Format: Y-m (e.g., 2026-02)
        
        $user = User::findOrFail($userId);
        $salaryConfig = UserSalary::where('user_id', $userId)->firstOrFail();
        
        $startDate = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Calculate work hours
        $totalWorkSeconds = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('total_seconds');
        
        $idleSeconds = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('idle_seconds');
        
        $netWorkSeconds = max(0, $totalWorkSeconds - $idleSeconds);
        $netWorkHours = $netWorkSeconds / 3600;
        
        // Holidays & Leaves
        $holidaysCount = Holiday::whereBetween('date', [$startDate, $endDate])->count();
        $leavesCount = Leave::where('user_id', $userId)
            ->where('status', 'Approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();
        
        // Calculate salary
        $hourlyRate = $salaryConfig->base_salary / $salaryConfig->working_days_per_month / $salaryConfig->daily_working_hours;
        $payableSalary = $hourlyRate * $netWorkHours;
        
        $data = [
            'user' => $user,
            'salary_config' => $salaryConfig,
            'month' => $startDate->format('F Y'),
            'net_hours' => round($netWorkHours, 2),
            'idle_hours' => round($idleSeconds / 3600, 2),
            'total_hours' => round($totalWorkSeconds / 3600, 2),
            'holidays' => $holidaysCount,
            'leaves' => $leavesCount,
            'payable' => round($payableSalary, 2),
            'generated_at' => now()->format('d M Y, h:i A'),
        ];
        
        return view('hr.salary-slip', $data);
    }
}
