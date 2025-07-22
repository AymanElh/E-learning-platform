<?php

namespace App\Repositories\Course;

use App\Interfaces\Course\EnrollmentRepositoryInterface;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function enroll(\App\Models\Course $course, int $userId): Enrollment|false|null
    {
        $alreadyEnrolled = $course->enrollments()->where('user_id', $userId)->exists();

        if ($alreadyEnrolled) {
            return false;
        }

        return Enrollment::create([
            'user_id' => $userId,
            'course_id' => $course->id,
            'status' => "pending"
        ]);
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
        if (!$enrollment) {
            return null;
        }

        $enrollment->update(['status' => $status]);
        return $enrollment->refresh();
    }

    public function delete(int $id)
    {
        $enrollment = Enrollment::find($id);
        if (!$enrollment) {
            return false;
        }
        return $enrollment->delete();
    }

}
