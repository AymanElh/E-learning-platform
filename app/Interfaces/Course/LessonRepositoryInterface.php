<?php

namespace App\Interfaces\Course;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

interface LessonRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?Lesson;
    public function getLessonBySectionByCourse(Course $course, int $sectionId, int $lessonId): ?Lesson;
    public function getBySection(int $sectionId): Collection;
    public function getByCourse(int $courseId): Collection;
    public function store(array $data): Lesson;
    public function update(int $id, array $data): ?Lesson;
    public function delete(Course $course, int $sectionId, int $id): bool;
}
