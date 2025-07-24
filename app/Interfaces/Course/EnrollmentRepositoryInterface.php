<?php

namespace App\Interfaces\Course;


use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface EnrollmentRepositoryInterface
{
    public function enroll(\App\Models\Course $course, int $userId);
    public function getEnrollmentByCourse(Course $course): Collection;
    public function getEnrollmentByUser(int $userId): Collection;
    public function getById(int $id);
    public function updateStatus(Course $course, int $enrollmentId, string $status);
    public function cancelEnroll(int $id);
}
