<?php

namespace App\Interfaces\Course;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

interface SectionRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(Course $course, int $id): ?object;
    public function getByCourse(int $course_id): Collection;
    public function store(array $data): Section;
    public function update(int $id, array $data): ?Section;
    public function delete(int $id): bool;
}
