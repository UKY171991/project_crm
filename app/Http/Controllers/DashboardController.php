<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Payment;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $month = $request->get('month', date('n')); // Default to current month
        $year = $request->get('year', date('Y'));
        
        $stats = [
            'total_projects' => 0,
            'active_projects' => 0,
            'total_clients' => 0,
            'total_revenue' => '',
        ];

        // 1. Basic Stats
        $baseQuery = Project::query();
        if ($user->hasRole('admin')) { $baseQuery->where('created_by', $user->id); }
        elseif ($user->hasRole('client')) { $baseQuery->where('client_id', $user->clientProfile->id); }
        elseif ($user->hasRole('user')) { $baseQuery->whereHas('assignees', function($q) use ($user) { $q->where('user_id', $user->id); }); }

        $stats['total_projects'] = (clone $baseQuery)->count();
        $stats['running_projects'] = (clone $baseQuery)->where('status', 'Running')->count();
        $stats['pending_projects'] = (clone $baseQuery)->where('status', 'Pending')->count();
        $stats['completed_projects'] = (clone $baseQuery)->where('status', 'Completed')->count();
        $stats['canceled_projects'] = (clone $baseQuery)->where('status', 'Canceled')->count();
        
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $stats['total_clients'] = Client::count();
            $stats['total_users'] = User::whereHas('role', function($q){ $q->where('slug', 'user'); })->count();
        } elseif ($user->hasRole('client')) {
            $stats['total_clients'] = 1; 
            $stats['total_users'] = 0;
        } else {
            $stats['total_clients'] = 0;
            $stats['total_users'] = 0;
        }

        // Work Hours Stat
        $stats['today_work_hours'] = '0h 0m 0s';
        if (!$user->hasRole('client')) {
            $todayWorkSeconds = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->sum('total_seconds');
            
            $todayIdleSeconds = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->sum('idle_seconds');
            
            $activeSession = \App\Models\Attendance::where('user_id', $user->id)
                ->whereNull('clock_out')
                ->latest()
                ->first();
                
            if ($activeSession) {
                // Total until now including live active session
                $todayWorkSeconds = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereDate('date', Carbon::today())
                    ->sum('total_seconds');
            }
            
            $netSeconds = max(0, $todayWorkSeconds - $todayIdleSeconds);
            
            $hours = floor($netSeconds / 3600);
            $mins = floor(($netSeconds % 3600) / 60);
            $secs = $netSeconds % 60;
            
            $stats['today_work_hours'] = sprintf('%dh %dm %ds', $hours, $mins, $secs);
        }

        // 2. Revenue Calculation (Filtered by Month/Year)
        $revenueQuery = Payment::whereIn('payment_status', ['Paid', 'Partial']);
        if ($year) { $revenueQuery->whereYear('payment_date', $year); }
        if ($month) { $revenueQuery->whereMonth('payment_date', $month); }
        
        if ($user->hasRole('admin')) {
            $revenueQuery->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); });
        } elseif ($user->hasRole('client') && $user->clientProfile) {
            $revenueQuery->whereHas('project', function($q) use ($user) { $q->where('client_id', $user->clientProfile->id); });
        }

        $revenues = $revenueQuery->select('currency', DB::raw('sum(amount) as total'))
            ->groupBy('currency')
            ->get();
        
        // 3. Expense Calculation (Filtered by Month/Year)
        $expenseQuery = \App\Models\Expense::where('status', 'Paid');
        if ($year) { $expenseQuery->whereYear('expense_date', $year); }
        if ($month) { $expenseQuery->whereMonth('expense_date', $month); }
        
        if ($user->hasRole('admin')) {
             $expenseQuery->where('user_id', $user->id);
        }
        $expenses = $expenseQuery->select('currency', DB::raw('sum(amount) as total'))
            ->groupBy('currency')
            ->get();

        $revenueMap = $revenues->pluck('total', 'currency');
        $expenseMap = $expenses->pluck('total', 'currency');
        $currencies = $revenueMap->keys()->concat($expenseMap->keys())->unique();

        $revenueStrings = [];
        $expenseStrings = [];
        $profitStrings = [];

        foreach($currencies as $curr) {
            $rev = $revenueMap->get($curr, 0);
            $exp = $expenseMap->get($curr, 0);
            $profit = $rev - $exp;

            if ($rev > 0) $revenueStrings[] = $curr . ' ' . number_format($rev, 0);
            if ($exp > 0) $expenseStrings[] = $curr . ' ' . number_format($exp, 0);
            $profitStrings[] = $curr . ' ' . number_format($profit, 0);
        }

        $stats['total_revenue'] = !empty($revenueStrings) ? implode(' / ', $revenueStrings) : '0';
        $stats['total_expense'] = !empty($expenseStrings) ? implode(' / ', $expenseStrings) : '0';
        $stats['total_profit'] = !empty($profitStrings) ? implode(' / ', $profitStrings) : '0';

        // 3b. Pending Expenses Calculation
        $pendingExpenseQuery = \App\Models\Expense::where('status', 'Pending');
        if ($year) { $pendingExpenseQuery->whereYear('expense_date', $year); }
        if ($month) { $pendingExpenseQuery->whereMonth('expense_date', $month); }
        if ($user->hasRole('admin')) { $pendingExpenseQuery->where('user_id', $user->id); }

        $pendingExpenses = $pendingExpenseQuery->select('currency', DB::raw('sum(amount) as total'))
            ->groupBy('currency')
            ->get();
        
        $pendingExpStrings = [];
        foreach($pendingExpenses as $pe) {
            if ($pe->total > 0) $pendingExpStrings[] = $pe->currency . ' ' . number_format($pe->total, 0);
        }
        $stats['total_pending_expense'] = !empty($pendingExpStrings) ? implode(' / ', $pendingExpStrings) : '0'; 


        // 2b. Pending Payments (Filtered by Month/Year)
        $pPendingQuery = Project::where('status', '!=', 'Canceled');
        if ($year) { $pPendingQuery->whereYear('end_date', $year); }
        if ($month) { $pPendingQuery->whereMonth('end_date', $month); }
        
        if ($user->hasRole('admin')) $pPendingQuery->where('created_by', $user->id);
        elseif ($user->hasRole('client') && $user->clientProfile) $pPendingQuery->where('client_id', $user->clientProfile->id);
        
        $pPendingGrouped = $pPendingQuery->get()->groupBy('currency');
        $pendingStrings = [];
        foreach($pPendingGrouped as $curr => $projs) {
             $totalBalance = $projs->sum(fn($p) => $p->balance);
             if ($totalBalance > 0) {
                 $pendingStrings[] = ($curr ?: 'USD') . ' ' . number_format($totalBalance, 0);
             }
        }
        $stats['total_pending'] = !empty($pendingStrings) ? implode(' / ', $pendingStrings) : '0';

        // 3. Chart Data: Monthly Income vs Expense vs Pending (Current Year or Selected Year)
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $incomeData = array_fill(0, 12, 0);
        $expenseData = array_fill(0, 12, 0);
        $pendingData = array_fill(0, 12, 0);
        $pendingExpData = array_fill(0, 12, 0);

        $chartYear = $year ?: date('Y');

        for ($m = 1; $m <= 12; $m++) {
            $startDate = Carbon::create($chartYear, $m, 1)->startOfMonth();
            $endDate = Carbon::create($chartYear, $m, 1)->endOfMonth();

            // Income (Paid/Partial Payments)
            $incomeQuery = Payment::whereIn('payment_status', ['Paid', 'Partial'])
                ->whereBetween('payment_date', [$startDate, $endDate]);
            if ($user->hasRole('admin')) {
                $incomeQuery->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); });
            } elseif ($user->hasRole('client')) {
                $incomeQuery->whereHas('project', function($q) use ($user) { $q->where('client_id', $user->clientProfile->id); });
            }
            $incomeData[$m-1] = (float) $incomeQuery->sum('amount');

            // Expense
            $expQuery = \App\Models\Expense::where('status', 'Paid')->whereBetween('expense_date', [$startDate, $endDate]);
            if ($user->hasRole('admin')) {
                $expQuery->where('user_id', $user->id);
            }
            $expenseData[$m-1] = (float) $expQuery->sum('amount');

            // Pending Income (Project Balances due this month)
            $pQuery = Project::where('status', '!=', 'Canceled')->whereBetween('end_date', [$startDate, $endDate]);
            if ($user->hasRole('admin')) {
                $pQuery->where('created_by', $user->id);
            } elseif ($user->hasRole('client')) {
                $pQuery->where('client_id', $user->clientProfile->id);
            }
            
            $pendingTotal = 0;
            $projectsDue = $pQuery->get();
            foreach($projectsDue as $proj) {
                $pendingTotal += $proj->balance;
            }
            $pendingData[$m-1] = (float) $pendingTotal;

            // Pending Expense
            $pExpQuery = \App\Models\Expense::where('status', 'Pending')->whereBetween('expense_date', [$startDate, $endDate]);
            if ($user->hasRole('admin')) {
                $pExpQuery->where('user_id', $user->id);
            }
            $pendingExpData[$m-1] = (float) $pExpQuery->sum('amount');
        }

        $barDatasets = [
            [
                'label' => 'Income',
                'backgroundColor' => '#28a745',
                'data' => $incomeData
            ],
            [
                'label' => 'Expense',
                'backgroundColor' => '#dc3545',
                'data' => $expenseData
            ],
            [
                'label' => 'Pending Income',
                'backgroundColor' => '#ffc107',
                'data' => $pendingData
            ],
            [
                'label' => 'Pending Expense',
                'backgroundColor' => '#fd7e14',
                'data' => $pendingExpData
            ]
        ];

        // 4. Project Status Chart Data
        $statusCounts = ['Pending' => 0, 'Running' => 0, 'Completed' => 0, 'Canceled' => 0];
        $pQuery = Project::query();
        if ($user->hasRole('admin')) { $pQuery->where('created_by', $user->id); }
        elseif ($user->hasRole('client')) { $pQuery->where('client_id', $user->clientProfile->id); }
        elseif ($user->hasRole('user')) { $pQuery->whereHas('assignees', function($q) use ($user) { $q->where('user_id', $user->id); }); }

        $counts = $pQuery->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $statusData = array_merge($statusCounts, $counts);

        // 5. Recent Projects
        $recentProjectsQuery = Project::latest()->take(5);
        if ($user->hasRole('admin')) { $recentProjectsQuery->where('created_by', $user->id); }
        elseif ($user->hasRole('client') && $user->clientProfile) { $recentProjectsQuery->where('client_id', $user->clientProfile->id); }
        elseif ($user->hasRole('user')) { 
            // Regular users see only their assigned projects
            $recentProjectsQuery->whereHas('assignees', function($q) use ($user) { 
                $q->where('user_id', $user->id); 
            }); 
        }
        // Master sees all projects (no filter)
        $recentProjects = $recentProjectsQuery->get();

        // 6. Recent Transactions (Payments) - Only for Master & Admin
        $recentTransactions = collect();
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $recentTransactionsQuery = Payment::with('project.client')->latest();
            if ($user->hasRole('admin')) {
                $recentTransactionsQuery->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); });
            }
            $recentTransactions = $recentTransactionsQuery->take(5)->get();
        }

        $years = range(date('Y'), date('Y') - 5);

        return view('dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentTransactions' => $recentTransactions,
            'barLabels' => json_encode($barLabels),
            'barDatasets' => json_encode($barDatasets),
            'statusLabels' => json_encode(array_keys($statusData)),
            'statusData' => json_encode(array_values($statusData)),
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'years' => $years,
        ]);
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        if (!Auth::user()->hasRole('master')) {
            abort(403);
        }

        Artisan::call('optimize:clear');
        return back()->with('success', 'System cache cleared successfully!');
    }

    /**
     * Run database migrations.
     */
    public function runMigration()
    {
        if (!Auth::user()->hasRole('master')) {
            abort(403);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return back()->with('success', 'Migrations executed successfully: ' . $output);
        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }
    /**
     * Run composer update.
     */
    public function runComposerUpdate()
    {
        if (!Auth::user()->hasRole('master')) {
            abort(403);
        }

        try {
            // Composer update can take a while
            set_time_limit(600); 
            
            // Try to find composer or use default command
            // On many systems 'composer' is in the path.
            // We use the --no-interaction flag to prevent hang
            $process = \Illuminate\Support\Facades\Process::timeout(600)->run('composer update --no-interaction');

            if ($process->successful()) {
                return back()->with('success', 'Composer update executed successfully: ' . $process->output());
            } else {
                return back()->with('error', 'Composer update failed: ' . $process->errorOutput());
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Composer update failed: ' . $e->getMessage());
        }
    }
    /**
     * Fix storage link issue.
     */
    public function fixStorageLink()
    {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        try {
            // 1. Fix APP_URL in .env if it's localhost but we're on a domain
            $currentUrl = url('/');
            $envAppUrl = config('app.url');
            
            if (str_contains($envAppUrl, '127.0.0.1') || str_contains($envAppUrl, 'localhost')) {
                if (!str_contains($currentUrl, '127.0.0.1') && !str_contains($currentUrl, 'localhost')) {
                    $envPath = base_path('.env');
                    if (file_exists($envPath)) {
                        $content = file_get_contents($envPath);
                        $content = preg_replace('/^APP_URL=.*$/m', 'APP_URL=' . $currentUrl, $content);
                        file_put_contents($envPath, $content);
                        Artisan::call('config:clear');
                    }
                }
            }

            // 2. Fix Storage Link
            $link = public_path('storage');
            if (file_exists($link)) {
                // On some hosting, it might be a folder instead of a link
                if (is_link($link)) {
                    unlink($link);
                } else {
                    // Force delete if it's a directory (might be a failed previous attempt)
                    $this->deleteDirectory($link);
                }
            }

            Artisan::call('storage:link');
            
            return back()->with('success', 'System maintenance completed! APP_URL updated and Storage Link recreated. Images should now load.');
        } catch (\Exception $e) {
            return back()->with('error', 'Maintenance failed: ' . $e->getMessage());
        }
    }

    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}
