<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $table = 'lesson_vides';

    protected $fillable = [
        'lesson_id',
        'video_url',
        'video_thumbnail',
        'video_duration',
        'video_quality',
        'video_size'
    ];

    protected $casts = [
        'video_duration' => 'integer',
        'video_size' => 'decimal:2',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->video_duration) {
            return "00:00";
        }

        $hours = floor($this->video_duration / 3600);
        $minutes = floor(($this->video_duration % 3600) / 60);
        $seconds = $this->video_duration % 60;

        if ($hours > 0) {
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }

        return sprintf("%02d:%02d", $minutes, $seconds);
    }

}
