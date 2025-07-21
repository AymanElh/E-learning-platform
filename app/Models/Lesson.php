<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{

    protected $fillable = [
        'course_id', 'section_id', 'title', 'description', 'lesson_type',
        'order_index', 'duration_minutes', 'is_free_preview', 'is_published'
    ];


    // Relationships

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function getCourseAttribute()
    {
        return $this->section->course;
    }
}
