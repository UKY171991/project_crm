<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\UserSalary;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\Carbon;

class HRManager extends Component
{
    public $activeTab = 'salary';
    
    // Salary Config
    public $userId, $baseSalary, $workingDays, $workingHours;
    
    // Holiday Config
    public $holidayDate, $holidayName, $holidayType = 'Festival';

    // Salary Calculation
    public $calcMonth, $calcYear, $calcUserId;
    public $calcResult = null;

    // Edit tracking
    public $editingSalaryId = null;
    public $editingHolidayId = null;

    public function mount()
    {
        $this->calcMonth = date('m');
        $this->calcYear = date('Y');
    }

    public function saveSalaryConfig()
    {
        $this->validate([
            'userId' => 'required',
            'baseSalary' => 'required|numeric',
            'workingDays' => 'required|integer',
            'workingHours' => 'required|integer',
        ]);

        UserSalary::updateOrCreate(
            ['user_id' => $this->userId],
            [
                'base_salary' => $this->baseSalary,
                'working_days_per_month' => $this->workingDays,
                'daily_working_hours' => $this->workingHours
            ]
        );

        session()->flash('success', 'Salary configuration saved.');
        $this->reset(['userId', 'baseSalary', 'workingDays', 'workingHours', 'editingSalaryId']);
    }

    public function saveHoliday()
    {
        $this->validate([
            'holidayDate' => 'required|date',
            'holidayName' => 'required|string',
        ]);

        Holiday::updateOrCreate(
            ['date' => $this->holidayDate],
            ['name' => $this->holidayName, 'type' => $this->holidayType]
        );

        session()->flash('success', 'Holiday added.');
        $this->reset(['holidayDate', 'holidayName', 'editingHolidayId']);
    }

    public function editSalary($id)
    {
        $salary = UserSalary::findOrFail($id);
        $this->editingSalaryId = $id;
        $this->userId = $salary->user_id;
        $this->baseSalary = $salary->base_salary;
        $this->workingDays = $salary->working_days_per_month;
        $this->workingHours = $salary->daily_working_hours;
    }

    public function deleteSalary($id)
    {
        UserSalary::findOrFail($id)->delete();
        session()->flash('success', 'Salary configuration deleted.');
    }

    public function editHoliday($id)
    {
        $holiday = Holiday::findOrFail($id);
        $this->editingHolidayId = $id;
        $this->holidayDate = $holiday->date->format('Y-m-d');
        $this->holidayName = $holiday->name;
        $this->holidayType = $holiday->type;
    }

    public function deleteHoliday($id)
    {
        Holiday::findOrFail($id)->delete();
        session()->flash('success', 'Holiday deleted.');
    }

    public function calculateSalary()
    {
        $this->validate([
            'calcUserId' => 'required',
            'calcMonth' => 'required',
            'calcYear' => 'required',
        ]);

        $user = User::find($this->calcUserId);
        $salaryConfig = UserSalary::where('user_id', $this->calcUserId)->first();

        if (!$salaryConfig) {
            session()->flash('error', 'Salary configuration not found for this user.');
            return;
        }

        $startDate = Carbon::create($this->calcYear, $this->calcMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Total Working Seconds
        $totalWorkSeconds = Attendance::where('user_id', $this->calcUserId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('total_seconds');
        
        $idleSeconds = Attendance::where('user_id', $this->calcUserId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('idle_seconds');

        $netWorkSeconds = max(0, $totalWorkSeconds - $idleSeconds);
        $netWorkHours = $netWorkSeconds / 3600;

        // 2. Holidays & Leaves
        $holidaysCount = Holiday::whereBetween('date', [$startDate, $endDate])->count();
        $leavesCount = Leave::where('user_id', $this->calcUserId)
            ->where('status', 'Approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        // 3. Calculation logic
        // Formula: (Base Salary / Working Days / Working Hours) * Net Work Hours
        $hourlyRate = $salaryConfig->base_salary / $salaryConfig->working_days_per_month / $salaryConfig->daily_working_hours;
        $payableSalary = $hourlyRate * $netWorkHours;

        $this->calcResult = [
            'user_id' => $this->calcUserId,
            'user_name' => $user->name,
            'month' => $startDate->format('F Y'),
            'month_year' => $startDate->format('Y-m'),
            'base_salary' => $salaryConfig->base_salary,
            'currency' => $salaryConfig->currency,
            'net_hours' => round($netWorkHours, 2),
            'idle_hours' => round($idleSeconds / 3600, 2),
            'holidays' => $holidaysCount,
            'leaves' => $leavesCount,
            'payable' => round($payableSalary, 2),
            'working_days' => $salaryConfig->working_days_per_month,
            'daily_hours' => $salaryConfig->daily_working_hours,
        ];
    }

    public function generateSlip()
    {
        if (!$this->calcResult) {
            session()->flash('error', 'Please calculate salary first.');
            return;
        }

        // Return a redirect to a new route that will generate the PDF
        return redirect()->route('hr.salary-slip', [
            'user_id' => $this->calcResult['user_id'],
            'month' => $this->calcResult['month_year']
        ]);
    }

    public function render()
    {
        return view('livewire.h-r-manager', [
            'users' => User::whereHas('role', function($q) {
                $q->whereIn('slug', ['master', 'admin', 'user']);
            })->get(),
            'salaries' => UserSalary::with('user')->get(),
            'holidays' => Holiday::orderBy('date', 'desc')->get(),
        ]);
    }
}
