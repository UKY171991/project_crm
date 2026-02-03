<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSalary extends Model
{
    protected $fillable = ['user_id', 'base_salary', 'currency', 'working_days_per_month', 'daily_working_hours'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
