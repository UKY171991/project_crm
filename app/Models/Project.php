<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'client_id',
        'title',
        'description',
        'budget',
        'currency',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    public function getTotalPaidAttribute()
    {
        return $this->payments()
            ->where('payment_status', 'Paid')
            ->where('currency', $this->currency ?: 'USD')
            ->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->budget - $this->total_paid;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'project_assignees', 'project_id', 'user_id')
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function mediaFiles()
    {
        return $this->hasMany(MediaFile::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
