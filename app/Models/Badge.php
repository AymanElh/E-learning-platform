<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type'
    ];

    public function rules()
    {
        return $this->hasMany(BadgeRule::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges');
    }
}
