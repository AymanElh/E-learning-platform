<?php

namespace App\Listeners;

use App\Events\CourseCreated;
use App\Models\Badge;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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

    public function checkCourseCreatorBadge($mentor): void
    {
        $coursesCount = $mentor->courses()->count();
        $badge = Badge::where('name', 'course-creator')->first();
        if(!$badge) {
            return;
        }

        $data = [
            'course_count' => $coursesCount
        ];

        $this->badgeService->checkBadge($mentor, $badge, $data);
    }
}
