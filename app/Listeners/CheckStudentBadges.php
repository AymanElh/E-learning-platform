<?php

namespace App\Listeners;

use App\Events\EnrollmentCreated;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckStudentBadges
{
    protected BadgeService $badgeService;

    /**
     * Create the event listener.
     */
    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Handle the event.
     */
    public function handle(EnrollmentCreated $event): void
    {
        $student = $event->enrollment->user;
        if (!$student) {
            \Log::error("Enrollment {$event->enrollment->id} has no associated user");
            return;
        }

        \Log::info("Checking badges for student {$student->id} after new enrollment");

        $this->checkDedicatedStudentBadge($student);
        $this->checkMentorsFanBadge($student, $event->enrollment);
    }

    private function checkDedicatedStudentBadge($student): void
    {
        $uniqueCoursesCount = $this->countUniqueCourses($student);
        \Log::info("Student {$student->id} is now enrolled in {$uniqueCoursesCount} different courses");

        $badge = Badge::where('slug', 'dedicated-student')->first();

        if (!$badge) {
            \Log::error("Dedicated Student badge not found in database");
            return;
        }

        $context = [
            'enrolled_courses_count' => $uniqueCoursesCount,
        ];

        $this->badgeService->checkAndAwardBadge($student, $badge, $context);
    }

    private function countUniqueCourses($student): int
    {
        return $student->enrollments()
            ->where('status', '!=', 'cancelled') // Only count active enrollments
            ->distinct('course_id')
            ->count('course_id');
    }

    private function checkMentorsFanBadge($student, $newEnrollment): void
    {
        // Get the mentor of the course the student just enrolled in
        $course = $newEnrollment->course;
        if (!$course) {
            \Log::error("Enrollment {$newEnrollment->id} has no associated course");
            return;
        }

        $mentorId = $course->user_id;
        if (!$mentorId) {
            \Log::error("Course {$course->id} has no associated mentor");
            return;
        }

        // Count how many courses from this mentor the student is enrolled in
        $coursesFromMentorCount = $this->countCoursesFromMentor($student->id, $mentorId);
        \Log::info("Student {$student->id} is now enrolled in {$coursesFromMentorCount} courses from mentor {$mentorId}");

        // Find the Mentor's Fan badge
        $badge = Badge::where('slug', 'mentors-fan')->first();

        if (!$badge) {
            \Log::error("Mentor's Fan badge not found in database");
            return;
        }

        \Log::info("Checking Mentor's Fan badge for student {$student->id}", [
            'student_id' => $student->id,
            'mentor_id' => $mentorId,
            'courses_count' => $coursesFromMentorCount,
            'badge_id' => $badge->id,
            'badge_name' => $badge->name,
            'badge_requirements' => $badge->rules->toArray()
        ]);

        // Check if the student meets the requirements for this badge
        $context = [
            'courses_from_same_mentor' => $coursesFromMentorCount,
            'mentor_id' => $mentorId,
        ];

        $this->badgeService->checkAndAwardBadge($student, $badge, $context);
    }

    private function countCoursesFromMentor(int $studentId, int $mentorId): int
    {
        // PROBLEM: The previous version used distinct() which may not work on all DB systems

        // FIX: Use a more compatible query approach with better logging
        $coursesCount = \DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->where('enrollments.user_id', $studentId)
            ->where('enrollments.status', '!=', 'cancelled')
            ->where('courses.user_id', $mentorId)
            ->select(\DB::raw('COUNT(DISTINCT enrollments.course_id) as count'))
            ->first()
            ->count ?? 0;

        // Add detailed logging
        \Log::info("Student {$studentId} has {$coursesCount} courses from mentor {$mentorId}", [
            'student_id' => $studentId,
            'mentor_id' => $mentorId,
            'sql' => \DB::getQueryLog()[count(\DB::getQueryLog())-1]['query'] ?? 'Query not logged'
        ]);

        return $coursesCount;
    }
}
