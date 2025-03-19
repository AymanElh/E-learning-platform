<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'video_path',
        'duration'
    ];

    /**
     * Get the course that owns the video.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }


}
