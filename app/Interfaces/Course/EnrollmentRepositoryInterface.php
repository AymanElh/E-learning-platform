<?php

namespace App\Interfaces\Course;


interface EnrollmentRepositoryInterface
{
    public function enroll(\App\Models\Course $course, int $userId);
    public function getEnrollmentByCourse(int $courseId);
    public function getEnrollmentByUser(int $userId);
    public function getById(int $id);
    public function updateStatus(int $id, string $status);
    public function delete(int $id);
}
