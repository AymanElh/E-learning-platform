<?php

namespace App\Interfaces;

interface EnrollmentRepositoryInterface
{
    public function enroll(int $userId, int $courseId);
    public function getEnrollmentByCourse(int $courseId);
    public function getEnrollmentByUser(int $userId);
    public function getById(int $id);
    public function updateStatus(int $id, string $status);
    public function delete(int $id);
}
