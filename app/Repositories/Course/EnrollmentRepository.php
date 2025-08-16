<?php

namespace App\Repositories\Course;

use App\Interfaces\Course\EnrollmentRepositoryInterface;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function getAllEnrollments(): Collection
    {
        return Enrollment::all();
    }

    public function enroll(\App\Models\Course $course, int $userId): Enrollment|false|null
    {
        $alreadyEnrolled = $course->enrollments()->where('user_id', $userId)->exists();

        if ($alreadyEnrolled) {
            return false;
        }
        $enrollment  = Enrollment::create([
            'user_id' => $userId,
            'course_id' => $course->id,
            'status' => "pending",
            'enrolled_at' => now()
        ]);

        return $enrollment->load(['user', 'course']);
    }

    public function getEnrollmentByCourse(Course $course): Collection
    {
        return $course->enrollments()->with(['user', 'course'])->get();
    }

    public function getEnrollmentByUser(int $userId): Collection
    {
        return Enrollment::where('user_id', $userId)->with(['user', 'course'])->get();
    }

    public function getById(int $id)
    {
        return Enrollment::with(['user', 'course'])->find($id);
    }

    public function updateStatus(Course $course, int $enrollmentId, string $status): bool
    {
        $enrollment = $course->enrollments()->findOrFail($enrollmentId);
        return $enrollment->update(['status' => $status]);
    }

    public function cancelEnroll(int $id)
    {
        $enrollment = Enrollment::find($id);
        if (!$enrollment) {
            return false;
        }
        return $enrollment->delete();
    }

    public function getEnrollmentStatus(int $courseId, int $userId): bool
    {
        return Enrollment::where('user_id', $userId)->where('course_id', $courseId)->exists();
    }
}
