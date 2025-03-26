<?php

namespace App\Providers;

use App\Events\CourseCreated;
use App\Events\EnrollmentCreated;
use App\Listeners\CheckMentorBadges;
use App\Listeners\CheckStudentBadges;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected array $listen = [
        // Other events...
        CourseCreated::class => [
            CheckMentorBadges::class,
        ],

        EnrollmentCreated::class => [
            CheckMentorBadges::class,
            CheckStudentBadges::class
        ],
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
