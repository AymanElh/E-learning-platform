<?php

namespace App\Repositories\Course;

use App\Interfaces\Course\SectionRepositoryInterface;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

class SectionRepository implements SectionRepositoryInterface
{

    public function getAll(): Collection
    {
        return Section::with(['course', 'lessons'])->orderBy('order_index')->get();
    }

    public function getById(Course $course, int $id): ?object
    {
        return $course->sections()->findOrFail($id);
    }

    public function getByCourse(int $course_id): Collection
    {
        return Section::where('course_id', $course_id)
            ->orderBy('order_index')
            ->get();
    }

    public function store(array $data): Section
    {
        $nextOrderIdx = Section::where('course_id', $data['course_id'])->max('order_index');
        $data['order_index'] = $data['order_index'] ?? $nextOrderIdx ?? 0;

        $section = Section::create($data);
        return $section->load(['course', 'lessons']);
    }

    public function update(int $id, array $data): ?Section
    {
        $section = Section::find($id);

        if (!$section) {
            return null;
        }

        $section->update($data);
        return $section->load(['course', 'lessons']);
    }

    public function delete(int $id): bool
    {
        $section = Section::find($id);
        if(!$section) {
            return false;
        }

        return $section->delete();
    }
}
