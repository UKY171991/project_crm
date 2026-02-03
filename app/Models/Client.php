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
        'address',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
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

    public function getCurrencyAttribute()
    {
        return $this->projects->first()->currency ?? 'USD';
    }
}
