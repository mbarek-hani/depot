<?php

namespace App\Providers;

use App\Support\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['components.layouts.app', 'components.layouts.guest'], function ($view) {
            $view->with('associationName', Settings::associationName());

            if (auth()->check()) {
                $user = auth()->user();

                $view->with([
                    'unreadCount' => $user->unreadNotifications()->count(),
                    'latestNotifications' => $user->notifications()->latest()->take(5)->get(),
                ]);
            } else {
                $view->with([
                    'unreadCount' => 0,
                    'latestNotifications' => collect(),
                ]);
            }
        });

        View::share('currency', Settings::currency());
    }
}
