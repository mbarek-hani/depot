<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Present;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->string('filter')->toString();

        $notifications = $request->user()
            ->notifications()
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unread = $request->user()->unreadNotifications()->count();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications->through(fn (DatabaseNotification $notification) => Present::notification($notification)),
            'filter' => $filter,
            'unread' => $unread,
        ]);
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless(
            (int) $notification->notifiable_id === (int) $request->user()->id
            && $notification->notifiable_type === $request->user()::class,
            404,
        );

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        $productId = (int) ($notification->data['product_id'] ?? 0);

        if ($productId > 0 && ! Product::query()->find($productId)) {
            return redirect()
                ->route('notifications.index')
                ->with('status', 'المنتج المرتبط بهذا التنبيه مؤرشف ولم يعد متاحًا.');
        }

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'تم تعليم جميع الإشعارات كمقروءة.');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
