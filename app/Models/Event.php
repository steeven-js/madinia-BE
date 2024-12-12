<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'firebaseId',
        'title',
        'scheduled_date',
        'status',
        'price',
        'is_active',
        'last_updated',
        'stripe_event_id',  // Ajouté
        'stripe_price_id',  // Ajouté
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'activated_at' => 'datetime',
        'last_updated' => 'datetime',
        'is_active' => 'boolean'
    ];
}
