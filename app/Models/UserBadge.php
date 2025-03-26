<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $fillable = [
        'user_id',
        'badge_id',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the badge that was earned
     */
    public function badge(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
