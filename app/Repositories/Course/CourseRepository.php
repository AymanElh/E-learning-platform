<?php

namespace App\Repositories\Course;

use App\Interfaces\Course\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseRepository implements CourseRepositoryInterface
{
    /**
     * Get all courses with relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Course::with(['instructor', 'sections', 'lessons', 'category', 'tags'])->get();
    }

    /**
     * Get a course by its id.
     *
     * @param int $id
     * @return \App\Models\Course|null
     */
    public function getById(int $id): ?Course
    {
        return Course::with(['instructor', 'category', 'tags', 'sections.lessons'])->find($id);
    }

    /**
     * Store a new course.
     *
     * @param array $data
     * @return \App\Models\Course
     */
    public function store(array $data): Course
    {
        $course = Course::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'slug' => Str::slug($data['title']),
            'duration' => $data['duration'] ?? 0,
            'difficulty' => $data['difficulty'],
            'status' => $data['status'] ?? 'open',
            'price' => $data['price'],
            'is_free' => $data['is_free'],
            'is_featured' => $data['is_featured'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? auth()->id(),
            'category_id' => $data['category_id'] ?? null,
            'subcategory_id' => $data['subcategory_id'] ?? null,
            'published_at' => now(),
        ]);

        // Sync tags if provided
        if (isset($data['tags']) && is_array($data['tags'])) {
            $course->tags()->sync($data['tags']);
        }

        return $course->load(['instructor', 'category', 'tags']);
    }

    /**
     * Update a course.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Course|null
     */
    public function update(int $id, array $data): ?Course
    {
        $course = Course::find($id);

        if (!$course) {
            return null;
        }

        $updateData = [
            'title' => $data['title'] ?? $course->title,
            'description' => $data['description'] ?? $course->description,
            'duration' => $data['duration'] ?? $course->duration,
            'difficulty' => $data['difficulty'] ?? $course->difficulty,
            'status' => $data['status'] ?? $course->status,
            'price' => $data['price'] ?? $course->price,
            'is_free' => $data['is_free'] ?? $course->is_free,
            'is_published' => $data['is_published'] ?? $course->is_published,
            'is_featured' => $data['is_featured'] ?? $course->is_featured,
            'thumbnail_url' => $data['thumbnail_url'] ?? $course->thumbnail_url,
            'instructor_id' => $data['instructor_id'] ?? $course->instructor_id,
            'category_id' => $data['category_id'] ?? $course->category_id,
            'subcategory_id' => $data['subcategory_id'] ?? $course->subcategory_id,
            'published_at' => $data['published_at'] ?? $course->published_at,
        ];

        // Handle slug update if title is provided
        if (isset($data['title'])) {
            $updateData['slug'] = isset($data['slug']) ? $data['slug'] : Str::slug($data['title']);
        } elseif (isset($data['slug'])) {
            $updateData['slug'] = $data['slug'];
        }

        $course->update($updateData);

        // Sync tags if provided
        if (isset($data['tags']) && is_array($data['tags'])) {
            $course->tags()->sync($data['tags']);
        }

        return $course->load(['instructor', 'category', 'tags']);
    }

    /**
     * Delete a course.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $course = Course::find($id);

        if (!$course) {
            return false;
        }

        return $course->delete();
    }

    public function attachTags(int $id, array $tagIds)
    {
        $course = Course::find($id);

        if (!$course) {
            return null;
        }

        $course->tags()->attach($tagIds);

        return $course->load(['instructor', 'category', 'tags']);
    }

    public function syncTags(int $id, array $tagIds)
    {
        $course = Course::find($id);

        if (!$course) {
            return null;
        }

        // The sync method will replace all existing relationships
        $course->tags()->sync($tagIds);

         return $course->load(['instructor', 'category', 'tags']);
    }

    public function detachTags(int $id, array $tagIds)
    {
        $course = Course::find($id);

        if (!$course) {
            return null;
        }

        foreach ($tagIds as $tagId) {
            DB::table('course_tag')
                ->where('course_id', $course->id)
                ->where('tag_id', $tagId)
                ->delete();
        }

        return $course->load(['instructor', 'category', 'tags']);
    }

    public function getOpenCourses(): Collection
    {
        return Course::with(['category', 'tags', 'instructor', 'sections', 'lessons'])->where('status', 'open')->get();
    }


}
