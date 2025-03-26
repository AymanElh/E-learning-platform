<?php

namespace App\Providers;

use App\Events\CourseCreated;
use App\Listeners\CheckMentorBadges;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected array $listen = [
        // Other events...
        CourseCreated::class => [
            CheckMentorBadges::class,
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
