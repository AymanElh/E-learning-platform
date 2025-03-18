<?php

namespace App\Repositories;

use App\Interfaces\EnrollmentRepositoryInterface;
use App\Models\Enrollment;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function enroll(int $userId, int $courseId)
    {
        try {
            $isExist = Enrollment::where('user_id', $userId)->where('course_id', $courseId)->first();

            if($isExist) {
                return false;
            }

            return Enrollment::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => "pending"
            ]);
        } catch (\Exception $e) {
            \Log::error("Error creating a new enrollment: " . $e->getMessage());
            return null;
        }
    }

    public function getEnrollmentByCourse(int $courseId)
    {
        return Enrollment::where('course_id', $courseId)->with(['user', 'course'])->get();
    }

    public function getEnrollmentByUser(int $userId)
    {
        return Enrollment::where('user_id', $userId)->with(['user', 'course'])->get();
    }

    public function getById(int $id)
    {
        return Enrollment::with(['user', 'course'])->find($id);
    }

    public function updateStatus(int $id, string $status)
    {
        $enrollment = Enrollment::find($id);
        if(!$enrollment) {
            return null;
        }

        $enrollment->update(['status' => $status]);
        return $enrollment->refresh();
    }

    public function delete(int $id)
    {
        $enrollment = Enrollment::find($id);
        if(!$enrollment) {
            return false;
        }
        return $enrollment->delete();
    }

}
