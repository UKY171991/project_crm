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
        $stats['pending_payment_projects'] = (clone $baseQuery)->where('status', 'Pending Payment')->count();
        $stats['completed_projects'] = (clone $baseQuery)->where('status', 'Completed')->count();
        $stats['canceled_projects'] = (clone $baseQuery)->where('status', 'Canceled')->count();
        
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $stats['total_clients'] = Client::has('projects')->count();
            $stats['total_non_clients'] = Client::doesntHave('projects')->count();
            $stats['total_users'] = User::whereHas('role', function($q){ $q->where('slug', 'user'); })->count();
        } elseif ($user->hasRole('client')) {
            $stats['total_clients'] = 1; 
            $stats['total_non_clients'] = 0;
            $stats['total_users'] = 0;
        } else {
            $stats['total_clients'] = 0;
            $stats['total_non_clients'] = 0;
            $stats['total_users'] = 0;
        }

        // 2. REVENUE / EXPENSE (FILTERED)
        $revenueQuery = Payment::whereIn('payment_status', ['Paid', 'Partial']);
        if ($year) { $revenueQuery->whereYear('payment_date', $year); }
        if ($month) { $revenueQuery->whereMonth('payment_date', $month); }
        if ($user->hasRole('admin')) {
            $revenueQuery->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); });
        } elseif ($user->hasRole('client') && $user->clientProfile) {
            $revenueQuery->whereHas('project', function($q) use ($user) { $q->where('client_id', $user->clientProfile->id); });
        }
        $revenues = $revenueQuery->select('currency', DB::raw('sum(amount) as total'))->groupBy('currency')->get();
        $revenueMap = $revenues->pluck('total', 'currency');

        $expenseQuery = \App\Models\Expense::where('status', 'Paid');
        if ($year) { $expenseQuery->whereYear('expense_date', $year); }
        if ($month) { $expenseQuery->whereMonth('expense_date', $month); }
        if ($user->hasRole('admin')) { $expenseQuery->where('user_id', $user->id); }
        $expenses = $expenseQuery->select('currency', DB::raw('sum(amount) as total'))->groupBy('currency')->get();
        $expenseMap = $expenses->pluck('total', 'currency');

        $currencies = $revenueMap->keys()->concat($expenseMap->keys())->unique();
        $revenueStrings = []; $expenseStrings = []; $profitStrings = [];
        foreach($currencies as $curr) {
            $rev = $revenueMap->get($curr, 0); $exp = $expenseMap->get($curr, 0); $profit = $rev - $exp;
            if ($rev > 0) $revenueStrings[] = $curr . ' ' . number_format($rev, 0);
            if ($exp > 0) $expenseStrings[] = $curr . ' ' . number_format($exp, 0);
            $profitStrings[] = $curr . ' ' . number_format($profit, 0);
        }
        $stats['total_revenue'] = !empty($revenueStrings) ? implode(' / ', $revenueStrings) : '0';
        $stats['total_expense'] = !empty($expenseStrings) ? implode(' / ', $expenseStrings) : '0';
        $stats['total_profit'] = !empty($profitStrings) ? implode(' / ', $profitStrings) : '0';

        // 2b. PENDING (FILTERED)
        $pPendingQuery = Project::where('status', '!=', 'Canceled');
        if ($year) { $pPendingQuery->whereYear('end_date', $year); }
        if ($month) { $pPendingQuery->whereMonth('end_date', $month); }
        if ($user->hasRole('admin')) $pPendingQuery->where('created_by', $user->id);
        elseif ($user->hasRole('client') && $user->clientProfile) $pPendingQuery->where('client_id', $user->clientProfile->id);
        $pPendingGrouped = $pPendingQuery->get()->groupBy('currency');
        $pendingStrings = [];
        foreach($pPendingGrouped as $curr => $projs) {
             $totalBalance = $projs->sum(fn($p) => $p->balance);
             if ($totalBalance > 0) $pendingStrings[] = ($curr ?: 'USD') . ' ' . number_format($totalBalance, 0);
        }
        $stats['total_pending'] = !empty($pendingStrings) ? implode(' / ', $pendingStrings) : '0';

        $pExpQuery = \App\Models\Expense::where('status', 'Pending');
        if ($year) { $pExpQuery->whereYear('expense_date', $year); }
        if ($month) { $pExpQuery->whereMonth('expense_date', $month); }
        if ($user->hasRole('admin')) $pExpQuery->where('user_id', $user->id);
        $pExpenses = $pExpQuery->select('currency', DB::raw('sum(amount) as total'))->groupBy('currency')->get();
        $pExpStrings = [];
        foreach($pExpenses as $pe) { if ($pe->total > 0) $pExpStrings[] = $pe->currency . ' ' . number_format($pe->total, 0); }
        $stats['total_pending_expense'] = !empty($pExpStrings) ? implode(' / ', $pExpStrings) : '0';


        // 3. ALL TIME STATS
        $allTimeStats = [];
        $atRevQuery = Payment::whereIn('payment_status', ['Paid', 'Partial']);
        if ($user->hasRole('admin')) { $atRevQuery->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); }); }
        elseif ($user->hasRole('client') && $user->clientProfile) { $atRevQuery->whereHas('project', function($q) use ($user) { $q->where('client_id', $user->clientProfile->id); }); }
        $atRevenues = $atRevQuery->select('currency', DB::raw('sum(amount) as total'))->groupBy('currency')->get();
        $atRevMap = $atRevenues->pluck('total', 'currency');

        $atExpQuery = \App\Models\Expense::where('status', 'Paid');
        if ($user->hasRole('admin')) { $atExpQuery->where('user_id', $user->id); }
        $atExpenses = $atExpQuery->select('currency', DB::raw('sum(amount) as total'))->groupBy('currency')->get();
        $atExpMap = $atExpenses->pluck('total', 'currency');

        $atCurrs = $atRevMap->keys()->concat($atExpMap->keys())->unique();
        $atRevStr = []; $atExpStr = []; $atProfStr = [];
        foreach($atCurrs as $curr) {
            $rev = $atRevMap->get($curr, 0); $exp = $atExpMap->get($curr, 0); $profit = $rev - $exp;
            if ($rev > 0) $atRevStr[] = $curr . ' ' . number_format($rev, 0);
            if ($exp > 0) $atExpStr[] = $curr . ' ' . number_format($exp, 0);
            $atProfStr[] = $curr . ' ' . number_format($profit, 0);
        }
        $allTimeStats['total_revenue'] = !empty($atRevStr) ? implode(' / ', $atRevStr) : '0';
        $allTimeStats['total_expense'] = !empty($atExpStr) ? implode(' / ', $atExpStr) : '0';
        $allTimeStats['total_profit'] = !empty($atProfStr) ? implode(' / ', $atProfStr) : '0';

        $atPendingQ = Project::where('status', '!=', 'Canceled');
        if ($user->hasRole('admin')) $atPendingQ->where('created_by', $user->id);
        elseif ($user->hasRole('client') && $user->clientProfile) $atPendingQ->where('client_id', $user->clientProfile->id);
        $atPGrouped = $atPendingQ->get()->groupBy('currency');
        $atPStr = [];
        foreach($atPGrouped as $curr => $projs) {
             $totalBalance = $projs->sum(fn($p) => $p->balance);
             if ($totalBalance > 0) $atPStr[] = ($curr ?: 'USD') . ' ' . number_format($totalBalance, 0);
        }
        $allTimeStats['total_pending'] = !empty($atPStr) ? implode(' / ', $atPStr) : '0';

        $atPExpQ = \App\Models\Expense::where('status', 'Pending');
        if ($user->hasRole('admin')) $atPExpQ->where('user_id', $user->id);
        $atPE = $atPExpQ->select('currency', DB::raw('sum(amount) as total'))->groupBy('currency')->get();
        $atPEStr = [];
        foreach($atPE as $pe) { if ($pe->total > 0) $atPEStr[] = $pe->currency . ' ' . number_format($pe->total, 0); }
        $allTimeStats['total_pending_expense'] = !empty($atPEStr) ? implode(' / ', $atPEStr) : '0';

        // 4. CHART DATA (MONTHLY)
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $incomeData = array_fill(0, 12, 0); $expenseData = array_fill(0, 12, 0);
        $chartYear = $year ?: date('Y');
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($chartYear, $m, 1)->startOfMonth(); $end = Carbon::create($chartYear, $m, 1)->endOfMonth();
            $incQ = Payment::whereIn('payment_status', ['Paid', 'Partial'])->whereBetween('payment_date', [$start, $end]);
            if ($user->hasRole('admin')) { $incQ->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); }); }
            $incomeData[$m-1] = (float) $incQ->sum('amount');
            $exQ = \App\Models\Expense::where('status', 'Paid')->whereBetween('expense_date', [$start, $end]);
            if ($user->hasRole('admin')) { $exQ->where('user_id', $user->id); }
            $expenseData[$m-1] = (float) $exQ->sum('amount');
        }
        $barDatasets = [
            ['label' => 'Income', 'backgroundColor' => '#28a745', 'data' => $incomeData],
            ['label' => 'Expense', 'backgroundColor' => '#dc3545', 'data' => $expenseData],
        ];

        // 5. CHART DATA (YEARLY - ALL TIME)
        $years = range(date('Y'), date('Y') - 5);
        $atLabels = array_reverse($years);
        $atIncData = []; $atExpData = [];
        foreach ($atLabels as $y) {
            $atIncQ = Payment::whereIn('payment_status', ['Paid', 'Partial'])->whereYear('payment_date', $y);
            if ($user->hasRole('admin')) { $atIncQ->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); }); }
            $atIncData[] = (float) $atIncQ->sum('amount');
            $atExQ = \App\Models\Expense::where('status', 'Paid')->whereYear('expense_date', $y);
            if ($user->hasRole('admin')) { $atExQ->where('user_id', $user->id); }
            $atExpData[] = (float) $atExQ->sum('amount');
        }
        $atDatasets = [
            ['label' => 'Income', 'backgroundColor' => '#28a745', 'data' => $atIncData],
            ['label' => 'Expense', 'backgroundColor' => '#dc3545', 'data' => $atExpData],
        ];

        // 6. Project Status / Recent Items
        $pStatusQ = Project::query();
        if ($user->hasRole('admin')) { $pStatusQ->where('created_by', $user->id); }
        $statusCounts = $pStatusQ->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();

        $recentProjects = Project::latest()->take(5);
        if ($user->hasRole('admin')) { $recentProjects->where('created_by', $user->id); }
        $recentProjects = $recentProjects->get();

        $recentTransactions = collect();
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $transQ = Payment::with('project.client')->latest();
            if ($user->hasRole('admin')) { $transQ->whereHas('project', function($q) use ($user) { $q->where('created_by', $user->id); }); }
            $recentTransactions = $transQ->take(5)->get();
        }

        $scheduledCalls = collect();
        if ($user->hasRole('master') || $user->hasRole('admin')) {
            $scheduledCalls = \App\Models\ClientFeedback::with('client.user')
                ->whereNotNull('next_schedule')
                ->whereDate('next_schedule', '>=', now()->toDateString())
                ->orderBy('next_schedule', 'asc')
                ->take(10)
                ->get();
        }

        return view('dashboard', [
            'stats' => $stats, 'allTimeStats' => $allTimeStats,
            'recentProjects' => $recentProjects, 'recentTransactions' => $recentTransactions,
            'scheduledCalls' => $scheduledCalls,
            'barLabels' => json_encode($barLabels), 'barDatasets' => json_encode($barDatasets),
            'atLabels' => json_encode($atLabels), 'atDatasets' => json_encode($atDatasets),
            'statusLabels' => json_encode(array_keys($statusCounts)), 'statusData' => json_encode(array_values($statusCounts)),
            'selectedMonth' => $month, 'selectedYear' => $year, 'years' => $years,
        ]);
    }

    public function clearCache() {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) abort(403);
        Artisan::call('optimize:clear');
        return back()->with('success', 'System cache cleared successfully!');
    }

    public function runMigration() {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) abort(403);
        try {
            Artisan::call('migrate', ['--force' => true]);
            return back()->with('success', 'Migrations executed successfully: ' . Artisan::output());
        } catch (\Exception $e) { return back()->with('error', 'Migration failed: ' . $e->getMessage()); }
    }

    public function runComposerUpdate() {
        if (!Auth::user()->hasRole('master')) abort(403);
        try {
            set_time_limit(600);
            $process = \Illuminate\Support\Facades\Process::timeout(600)->run('composer update --no-interaction');
            if ($process->successful()) return back()->with('success', 'Composer update executed successfully: ' . $process->output());
            else return back()->with('error', 'Composer update failed: ' . $process->errorOutput());
        } catch (\Exception $e) { return back()->with('error', 'Composer update failed: ' . $e->getMessage()); }
    }

    public function fixStorageLink() {
        if (!Auth::user()->hasRole('master') && !Auth::user()->hasRole('admin')) abort(403);
        try {
            $currentUrl = url('/'); $envAppUrl = config('app.url');
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
            $link = public_path('storage');
            if (file_exists($link)) { if (is_link($link)) unlink($link); else $this->deleteDirectory($link); }
            Artisan::call('storage:link');
            return back()->with('success', 'Maintenance completed!');
        } catch (\Exception $e) { return back()->with('error', 'Maintenance failed: ' . $e->getMessage()); }
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
