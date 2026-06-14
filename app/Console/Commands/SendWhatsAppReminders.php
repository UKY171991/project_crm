<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send-reminders';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send scheduled WhatsApp reminders to clients based on project status and frequency';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsappService)
    {
        if (!config('services.whatsapp.enabled')) {
            $this->info('WhatsApp notifications are disabled in settings.');
            return;
        }

        $projects = Project::where('reminder_enabled', true)
            ->where('status', '!=', 'Canceled')
            ->with('client.user')
            ->get();

        $this->info('Checking ' . $projects->count() . ' projects for reminders...');
        $sentCount = 0;

        foreach ($projects as $project) {
            if (!$project->client || !$project->client->phone) {
                continue;
            }

            if ($this->shouldSendReminder($project)) {
                $message = $this->formatReminderMessage($project);
                
                $this->info("Sending reminder to: " . $project->client->company_name . " (" . $project->client->phone . ")");
                
                $success = $whatsappService->sendTextMessage($project->client->phone, $message);

                if ($success) {
                    $project->last_reminder_at = now();
                    $project->save();
                    $sentCount++;
                    $this->info("Reminder sent successfully.");
                } else {
                    $this->error("Failed to send reminder.");
                }
            }
        }

        $this->info("Done! Sent $sentCount reminders.");
        Log::info("WhatsApp scheduled reminders sent: $sentCount");
    }

    /**
     * Determine if a reminder should be sent based on frequency and last sent timestamp
     */
    private function shouldSendReminder($project)
    {
        if (!$project->last_reminder_at) {
            return true;
        }

        $now = now();
        $lastSent = Carbon::parse($project->last_reminder_at);

        switch ($project->reminder_frequency) {
            case 'daily':
                return $lastSent->diffInHours($now) >= 23;
            
            case 'weekly':
                return $lastSent->diffInDays($now) >= 6;
            
            case 'monthly':
                return $lastSent->diffInDays($now) >= 28;
            
            default:
                return false;
        }
    }

    /**
     * Format the reminder message based on project status
     */
    private function formatReminderMessage($project)
    {
        $clientName = $project->client->user->name ?? $project->client->company_name;
        
        // Determine greeting based on time
        $hour = date('H');
        $greeting = "Good Morning";
        if ($hour >= 12 && $hour < 17) {
            $greeting = "Good Afternoon";
        } elseif ($hour >= 17) {
            $greeting = "Good Evening";
        }
        
        // Get template based on status
        $template = match($project->status) {
            'Pending' => config('services.whatsapp.reminder_pending') ?? "{greeting} {name}, your project {title} is currently pending.",
            'Running' => config('services.whatsapp.reminder_running') ?? "{greeting} {name}, your project {title} is currently running.",
            'Pending Payment' => config('services.whatsapp.reminder_pending_payment') ?? "{greeting} {name}, your project {title} is completed. Please clear the balance of {currency} {balance}.",
            'Completed' => config('services.whatsapp.reminder_completed') ?? "{greeting} {name}, your project {title} is completed. Thank you!",
            default => "{greeting} {name}, reminder regarding project {title}. Current status: " . $project->status
        };
        
        // Replace placeholders
        return str_replace(
            ['{greeting}', '{name}', '{title}', '{currency}', '{balance}'],
            [$greeting, $clientName, $project->title, $project->currency, number_format($project->balance, 2)],
            $template
        );
    }
}
