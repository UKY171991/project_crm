<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'city',
        'phone',
        'last_whatsapp_at',
        'address',
        'status',
    ];

    protected $casts = [
        'last_whatsapp_at' => 'datetime',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->company_name ?? ($this->user->name ?? 'Unnamed Lead');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(ClientFeedback::class)->orderBy('created_at', 'desc');
    }

    public function getPendingTasksCountAttribute()
    {
        return $this->projects()->whereIn('status', ['Pending', 'Running'])->count();
    }

    public function getCompletedTasksCountAttribute()
    {
        return $this->projects()->where('status', 'Completed')->count();
    }

    public function getTotalPendingPaymentAttribute()
    {
        return $this->projects->sum(function($project) {
            return $project->balance;
        });
    }

    public function getTotalCompletedPaymentAttribute()
    {
        return $this->projects()->with('payments')->get()->sum(function($project) {
            return $project->payments->where('payment_status', 'Paid')->sum('amount');
        });
    }

    public function getCurrencyAttribute()
    {
        return $this->projects->first()->currency ?? 'USD';
    }

    public function getProjectsStatusMessage()
    {
        $wipProjects = $this->projects()->whereIn('status', ['Pending', 'Running'])->get();
        $pendingPayProjects = $this->projects()->where('status', 'Pending Payment')->get();
        
        $message = "";
        
        if ($wipProjects->isNotEmpty()) {
            $message .= "*Work in Progress:*\n";
            foreach ($wipProjects as $project) {
                $message .= "- {$project->title} ({$project->status})\n";
            }
            $message .= "\n";
        }
        
        if ($pendingPayProjects->isNotEmpty()) {
            $message .= "*Projects Awaiting Payment:*\n";
            foreach ($pendingPayProjects as $project) {
                $message .= "- {$project->title}\n";
            }
            $message .= "\n";
        }
        
        return $message;
    }

    public function getPendingPaymentMessage()
    {
        $balance = $this->total_pending_payment;
        if ($balance <= 0) {
            return "";
        }
        
        return "*Total Outstanding Balance:* {$this->currency} " . number_format($balance, 2) . "\n";
    }

    public function getStatusSummaryMessage()
    {
        $hour = date('H');
        $greeting = "Good Morning";
        if ($hour >= 12 && $hour < 17) {
            $greeting = "Good Afternoon";
        } elseif ($hour >= 17) {
            $greeting = "Good Evening";
        }
        
        $clientName = $this->company_name ?? ($this->user->name ?? 'Client');
        $message = "{$greeting} {$clientName},\n\n";
        $message .= "Here is a quick update regarding your projects and account status:\n\n";
        
        $statusMsg = $this->getProjectsStatusMessage();
        $paymentMsg = $this->getPendingPaymentMessage();
        
        if (!$statusMsg && !$paymentMsg) {
            return "{$greeting} {$clientName},\n\nAll your projects are completed and payments are clear. Thank you for choosing us!";
        }
        
        $message .= $statusMsg;
        $message .= $paymentMsg;
        
        $message .= "\nPlease let us know if you need any assistance.\n\n";
        $message .= "Regards,\n" . config('app.name');
        
        return $message;
    }
}
