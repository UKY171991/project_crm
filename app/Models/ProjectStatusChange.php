<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStatusChange extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'old_status',
        'new_status',
        'status',
        'processed_by',
        'processed_at',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
