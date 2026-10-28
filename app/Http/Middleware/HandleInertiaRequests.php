<?php

namespace App\Http\Middleware;

use App\Support\Present;
use App\Support\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'theme' => $user->theme ?? 'system',
                    'created_at' => $user->created_at?->translatedFormat('j F Y'),
                    'updated_human' => $user->updated_at?->diffForHumans(),
                ] : null,
            ],
            'app' => [
                'name' => Settings::associationName(),
                'currency' => Settings::currency(),
                'today' => now()->translatedFormat('l، j F Y'),
            ],
            'settings' => Settings::all(),
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notifications' => fn () => $user ? [
                'unread' => $user->unreadNotifications()->count(),
                'latest' => $user->notifications()->latest()->take(5)->get()
                    ->map(fn ($notification) => Present::notification($notification))
                    ->all(),
            ] : [
                'unread' => 0,
                'latest' => [],
            ],
        ];
    }
}
