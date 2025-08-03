<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'course_id',
        'amount',
        'currency',
        'status',
        'payment_provider',
        'payment_intent_id',
        'payment_status',
        'payment_metadata',
        'completed_at',
    ];

    protected $casts = [
        'payment_metadata' => 'array',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // relationships

    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    /**
     * Generate a unique order number
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($order) {
            if(!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }
}
