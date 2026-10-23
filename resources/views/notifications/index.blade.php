<x-layouts.app title="الإشعارات">
    <x-layout.page-header
        title="الإشعارات"
        description="تنبيهات المخزون المنخفض وتواريخ الصلاحية."
        eyebrow="التنبيهات"
    >
        <x-slot:actions>
            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <x-ui.button type="submit" variant="outline" icon="check">تعليم الكل كمقروء</x-ui.button>
                </form>
            @endif
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('notifications.index') }}" @class([
                'rounded-xl px-3.5 py-2 text-xs font-bold transition',
                'bg-brand-400 text-night-950' => $filter !== 'unread',
                'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300' => $filter === 'unread',
            ])>
                الكل
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" @class([
                'rounded-xl px-3.5 py-2 text-xs font-bold transition',
                'bg-brand-400 text-night-950' => $filter === 'unread',
                'border border-stone-200 bg-white text-stone-600 hover:border-stone-300 dark:border-night-800 dark:bg-night-900 dark:text-stone-300' => $filter !== 'unread',
            ])>
                غير المقروءة
                @if ($unread > 0)
                    <span class="ms-1 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[9px] font-extrabold text-white">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
            </a>
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <x-ui.empty-state icon="bell" title="لا توجد إشعارات" :description="$filter === 'unread' ? 'قرأت جميع الإشعارات. أحسنت!' : 'ستظهر هنا تنبيهات المخزون وتواريخ الصلاحية.'" />
            </div>
        @else
            <div class="space-y-3">
                @foreach ($notifications as $notification)
                    <x-notifications.item :notification="$notification" />
                @endforeach
            </div>

            <x-ui.card padded="false">
                <x-ui.pagination :paginator="$notifications" />
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
