<?php

namespace App\Listeners;

use App\Events\CourseCreated;
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
    public function handle(CourseCreated $event): void
    {
        $mentor = $event->user;

        $this->checkCourseCreatorBadge($mentor);
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
}
