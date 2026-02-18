<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Setting;
use App\Mail\CronTasksMail;
use Illuminate\Support\Facades\Mail;

class CronController extends Controller
{
    /**
     * Send email for pending tasks and payments.
     * 
     * URL: /cron/pending-tasks?cron_key=YOUR_KEY
     */
    public function sendPendingTasksEmail(Request $request)
    {
        $storedKey = Setting::get('cron_key', 'crm_tasks_cron_2026');
        $providedKey = $request->query('cron_key');

        if ($providedKey !== $storedKey) {
            return response()->json(['error' => 'Unauthorized. Invalid cron key.'], 401);
        }

        // Fetch all projects with full details once
        $allProjects = Project::with(['client.user', 'assignees', 'projectRemarks' => function($q){
            $q->latest()->limit(1);
        }])->latest()->get();

        // 1. Filter pending projects (not completed)
        $pendingProjects = $allProjects->reject(function ($project) {
            return $project->status === 'Completed';
        });

        // 2. Filter projects with pending payments (balance > 0)
        $pendingPayments = $allProjects->filter(function ($project) {
            return $project->balance > 0;
        });

        // 3. Calculate totals by currency
        $totals = $pendingPayments->groupBy('currency')->map(function ($items) {
            return $items->sum('balance');
        });

        // 4. Get the recipient email from settings
        $recipientEmail = Setting::get('cron_email', 'uky171991@gmail.com');

        try {
            \Log::info("Attempting to send cron email to: " . $recipientEmail);
            Mail::to($recipientEmail)->send(new CronTasksMail($pendingProjects, $pendingPayments, $totals));
            \Log::info("Cron email sent trigger completed.");
            
            return response()->json([
                'success' => true,
                'message' => 'Cron email sent successfully to ' . $recipientEmail,
                'summary' => [
                    'pending_projects_count' => $pendingProjects->count(),
                    'pending_payments_count' => $pendingPayments->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
