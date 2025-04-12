<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use App\Events\EnrollmentCreated;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 *
 */
class CheckMentorBadges
{
    /**
     * Create the event listener.
     */
    public function __construct(public BadgeService $badgeService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handleCourseCreated(CourseCreated $event): void
    {
        $mentor = $event->user;

        $this->checkCourseCreatorBadge($mentor);
    }

    private function handleEnrollmentCreated(EnrollmentCreated $event): void
    {
        // Get the course associated with this enrollment
        $course = $event->enrollment->course;

        // Get the mentor (course creator)
        $mentor = $course->user;

        if (!$mentor) {
            \Log::error("Course {$course->id} has no associated mentor");
            return;
        }

        \Log::info("Checking badges for mentor {$mentor->id} after new enrollment");

        // Check if mentor now qualifies for Popular Mentor badge
        $this->checkPopularMentorBadge($mentor);
    }

    /**
     * Check if the mentor has already the badge and count the created courses.
     *
     * @param $mentor
     * @return void
     */
    private function checkCourseCreatorBadge($mentor): void
    {
        $courseCount = $mentor->courses()->count();
        \Log::info("Mentor {$mentor->id} now has {$courseCount} published courses");

        $badge = Badge::where('slug', 'course-creator')->first();

        if (!$badge) {
            \Log::error("Course Creator badge not found in database");
            return;
        }

        $context = [
            'courses_published_count' => $courseCount,
        ];

        $this->badgeService->checkAndAwardBadge($mentor, $badge, $context);
    }

    private function checkPopularMentorBadge($mentor): void
    {
        // Count unique students enrolled across all courses by this mentor
        $uniqueStudentsCount = $this->countUniqueStudents($mentor);
        \Log::info("Mentor {$mentor->id} now has {$uniqueStudentsCount} unique students");

        // Check if mentor has the Popular Mentor badge
        $badge = Badge::where('slug', 'popular-mentor')->first();

        if (!$badge) {
            \Log::error("Popular Mentor badge not found in database");
            return;
        }

        // Use the BadgeService to check requirements and award badge if needed
        $context = [
            'total_students' => $uniqueStudentsCount,
        ];

        $this->badgeService->checkAndAwardBadge($mentor, $badge, $context);
    }

    private function countUniqueStudents($mentor): int
    {
        // Get IDs of all courses by this mentor
        $courseIds = $mentor->courses()->pluck('id')->toArray();

        if (empty($courseIds)) {
            return 0;
        }

        // Count unique students enrolled in these courses
        $uniqueStudentCount = \App\Models\Enrollment::whereIn('course_id', $courseIds)
            ->where('status', '!=', 'cancelled') // Only count active enrollments
            ->distinct('user_id')
            ->count('user_id');

        return $uniqueStudentCount;
    }
}
