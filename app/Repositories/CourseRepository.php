<?php

namespace App\Repositories;

use App\Filters\V1\CourseFilter;
use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;

class CourseRepository implements CourseRepositoryInterface
{

    public function getAll(array $filters = [])
    {
        \Log::info('Fetching courses with filters', $filters);

        try {
            $query = Course::query();

            // Apply filters if needed
            if (isset($filters['category'])) {
                $query->where('category_id', $filters['category']);
            }

            if (isset($filters['difficulty'])) {
                $query->where('difficulty_level', $filters['difficulty']);
            }

            // Add any other filters or transformations here

            $courses = $query->with(['category', 'tags'])->paginate(); // Eager load relationships

            \Log::info('Courses fetched successfully', $courses->toArray());

            return $courses;
        } catch (\Exception $e) {
            \Log::error("Error fetching courses: " . $e->getMessage());
            throw $e; // Re-throw the exception to be caught in the controller
        }
    }

    /**
     * Get a course by its id.
     *
     * @param int $id
     * @return \App\Models\Course|null
     */
    public function getById(int $id): ?Course
    {
        return Course::with(['category', 'tags'])->find($id);
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
            'duration' => $data['duration'],
            'difficulty' => $data['difficulty'],
            'status' => $data['status'] ?? 'open',
            'category_id' => $data['category_id'],
            'user_id' => auth()->id()
        ]);

        // Sync tags if provided
        if (isset($data['tags']) && is_array($data['tags'])) {
            $course->tags()->sync($data['tags']);
        }

        return $course->load(['category', 'tags']);
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

        $course->update([
            'title' => $data['title'] ?? $course->title,
            'description' => $data['description'] ?? $course->description,
            'duration' => $data['duration'] ?? $course->duration,
            'difficulty' => $data['difficulty'] ?? $course->difficulty,
            'status' => $data['status'] ?? $course->status,
            'category_id' => $data['category_id'] ?? $course->category_id,
        ]);

        // Sync tags if provided
        if (isset($data['tags']) && is_array($data['tags'])) {
            $course->tags()->sync($data['tags']);
        }

        return $course->load(['category', 'tags']);
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

        return $course->load(['category', 'tags']);
    }

    public function syncTags(int $id, array $tagIds)
    {
        $course = Course::find($id);

        if (!$course) {
            return null;
        }

        // The sync method will replace all existing relationships
        $course->tags()->sync($tagIds);

        return $course->load(['category', 'tags']);
    }

    public function detachTags(int $id, array $tagIds)
    {
        $course = Course::find($id);

        if (!$course) {
            return null;
        }

        foreach ($tagIds as $tagId) {
            \DB::table('course_tag')
                ->where('course_id', $course->id)
                ->where('tag_id', $tagId)
                ->delete();
        }

        return $course->load(['category', 'tags']);
    }
}
