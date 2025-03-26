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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rules()
    {
        return $this->hasMany(BadgeRule::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges');
    }

    /**
     * Check if this badge is for mentors
     */
    public function isForMentors(): bool
    {
        return in_array($this->type, ['mentor', 'both']);
    }

    /**
     * Check if this badge is for students
     */
    public function isForStudents(): bool
    {
        return in_array($this->type, ['student', 'both']);
    }
}
