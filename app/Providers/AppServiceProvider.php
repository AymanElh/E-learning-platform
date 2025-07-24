<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Interfaces\Admin\PermissionRepositoryInterface;
use App\Interfaces\Admin\RoleRepositoryInterface;
use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Interfaces\Content\CategoryRepositoryInterface;
use App\Interfaces\Content\TagRepositoryInterface;
use App\Interfaces\Course\CourseRepositoryInterface;
use App\Interfaces\Course\EnrollmentRepositoryInterface;
use App\Interfaces\Course\LessonRepositoryInterface;
use App\Interfaces\Course\SectionRepositoryInterface;
use App\Repositories\Admin\PermissionRepository;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Content\CategoryRepository;
use App\Repositories\Content\TagRepository;
use App\Repositories\Course\CourseRepository;
use App\Repositories\Course\EnrollmentRepository;
use App\Repositories\Course\SectionRepository;
use App\Repositories\Course\LessonRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(EnrollmentRepositoryInterface::class, Enrollmentrepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
        $this->app->bind(LessonRepositoryInterface::class, LessonRepository::class);

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }
}
