<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'status',
        'payment_data',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_data' => 'array',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function enrollment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
