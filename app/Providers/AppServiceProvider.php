<?php

namespace App\Providers;

use App\Interfaces\AuthRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\TagRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CourseRepository;
use App\Repositories\TagRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;


/**
 * @OA\Info(
 *     title="E-Learning",
 *     version="1.0.0"
 * )
 */
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
