<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = ['user_id', 'course_id', 'status', 'progress', 'order_id', 'enrolled_at', 'completed_at'];
    protected $with = ['user', 'course'];

    protected $casts = [
        'enrolled_at' => 'datetime'
    ];

    /**
     * Get the user that enrolled a course
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course related to enrollment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
