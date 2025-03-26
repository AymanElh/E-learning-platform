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
        $this->checkMentorsFavoriteBadge($student, $event->enrollment);
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
}
