<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'type',
        'renewal_date',
        'new_expiry_date',
        'amount',
        'currency',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'renewal_date' => 'date',
        'new_expiry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
