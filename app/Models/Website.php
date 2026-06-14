<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Str;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'url',
        'domain_name',
        'domain_expiry_date',
        'ssl_expiry_date',
        'hosting_provider',
        'hosting_expiry_date',
        'server_ip',
        'php_version',
        'cms',
        'admin_url',
        'admin_username',
        'admin_password',
        'notes',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($website) {
            $website->slug = Str::slug($website->name) . '-' . Str::random(5);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $casts = [
        'domain_expiry_date' => 'date',
        'ssl_expiry_date' => 'date',
        'hosting_expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function renewals()
    {
        return $this->hasMany(WebsiteRenewal::class)->orderBy('renewal_date', 'desc');
    }
}
