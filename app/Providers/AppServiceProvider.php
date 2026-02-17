<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        View::composer('backend.partial.header', function ($view) {
            $headerNotifications = collect();
            $unreadHeaderNotificationsCount = 0;

            if (!Auth::check()) {
                $view->with(compact('headerNotifications', 'unreadHeaderNotificationsCount'));
                return;
            }

            $user = Auth::user();
            $notificationService = app(NotificationService::class);
            $headerNotifications = $notificationService->forUser($user, 'header')->take(10)->values();

            $unreadHeaderNotificationsCount = $headerNotifications->filter(function ($notification) {
                return (bool) $notification->is_read === false;
            })->count();

            $view->with(compact('headerNotifications', 'unreadHeaderNotificationsCount'));
        });
    }
}
