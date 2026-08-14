<?php

namespace App\Providers;

use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navigation', function ($view) {
            if (auth()->check()) {
                $view->with('adminNotificationData', AdminNotificationService::getNotifications());
            } else {
                $view->with('adminNotificationData', ['notifications' => [], 'unread_count' => 0, 'counts' => ['all' => 0, 'checkins' => 0, 'overdue' => 0, 'requests' => 0]]);
            }
        });
    }
}