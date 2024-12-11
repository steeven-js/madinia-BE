<?php

namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'stripe_payment_id',
        'stripe_customer_id',
        'amount',
        'currency',
        'status',
        'payment_method_type',
        'metadata',
        'paid_at',
        'cancelled_at',
        'refunded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
