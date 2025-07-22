<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title', 'description', 'slug', 'duration',
        'difficulty', 'status', 'price', 'is_free',
        'is_published', 'is_featured', 'thumbnail_url',
        'total_students', 'instructor_id', 'category_id',
        'subcategory_id', 'published_at'
    ];


    /**
     * Get the category that owns the course.
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The tags that belong to the course.
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the section for the course, ordered by their index
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order_index');
    }

    /**
     * Get all lessons for the course through its sections.
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Section::class);
    }

    /**
     * Get course enrollments.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
