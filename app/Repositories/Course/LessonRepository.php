<?php

namespace App\Repositories\Course;

use App\Interfaces\Course\LessonRepositoryInterface;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

class LessonRepository implements LessonRepositoryInterface
{

    public function getAll(): Collection
    {
        return Lesson::with(['section', 'section.course'])->orderBy('order_index')->get();
    }

    public function getById(int $id): ?Lesson
    {
        return Lesson::with(['section', 'section.course'])->find($id);
    }

    public function getLessonBySectionByCourse(Course $course, int $sectionId, int $lessonId): ?Lesson
    {
        $section = $course->sections()->findOrFail($sectionId);
        $lesson = $section->lessons()->findOrFail($lessonId);

        return $lesson;
    }

    public function getBySection(int $sectionId): Collection
    {
        return Lesson::where('section_id', $sectionId)
            ->orderBy('order_index')
            ->get();
    }

    public function getByCourse(int $courseId): Collection
    {
        return Course::find($courseId)->lessons;
    }

    public function store(array $data): Lesson
    {
        $nextOrder = Lesson::where('section_id', $data['section_id'])->max('order_index') + 1;
        $data['order_index'] = $data['order_index'] ?? $nextOrder;

        $lesson = Lesson::create($data);
        return $lesson->load(['section', 'section.course']);
    }

    public function update(int $id, array $data): ?Lesson
    {
        $lesson = Lesson::find($id);

        if (!$lesson) {
            return null;
        }

        $lesson->update($data);
        return $lesson->load(['section', 'section.course']);
    }

    public function delete(Course $course, int $sectionId, int $id): bool
    {
        $lesson = $this->getLessonBySectionByCourse($course, $sectionId, $id);

        if (!$lesson) {
            return false;
        }

        return $lesson->delete();
    }
}
