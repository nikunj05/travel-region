<?php

namespace App\Providers;

use App\Interfaces\AuthInterface;
use App\Interfaces\BlogInterface;
use App\Interfaces\CmsInterface;
use App\Repositories\AuthRepository;
use App\Repositories\BlogRepository;
use App\Repositories\CmsRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthInterface::class, AuthRepository::class);
        $this->app->bind(BlogInterface::class, BlogRepository::class);
        $this->app->bind(CmsInterface::class, CmsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
