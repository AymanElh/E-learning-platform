<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'provider',
        'amount',
        'currency',
        'status',
        'provider_response',
        'processed_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

}
