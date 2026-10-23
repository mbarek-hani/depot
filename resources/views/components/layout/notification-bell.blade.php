@props(['latest' => collect(), 'unread' => 0])

<div
    x-data="notificationBell"
    data-unread-url="{{ route('notifications.unread-count') }}"
    data-unread-count="{{ $unread }}"
    class="relative"
>
    <button
        type="button"
        x-on:click="open = ! open"
        class="relative inline-flex size-10 items-center justify-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-stone-300 hover:text-stone-900 dark:border-night-800 dark:bg-night-900 dark:text-stone-300 dark:hover:border-stone-700 dark:hover:text-white"
        aria-label="الإشعارات"
        :aria-expanded="open.toString()"
    >
        <x-ui.icon name="bell" size="size-5" />
        <span
            x-show="unread > 0"
            x-cloak
            x-text="label(unread)"
            class="absolute -end-1.5 -top-1.5 flex min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[10px] font-extrabold leading-none text-white ring-2 ring-white dark:ring-night-900"
        ></span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute end-0 z-50 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-pop dark:border-night-800 dark:bg-night-900"
        x-on:click.outside="open = false"
    >
        <div class="flex items-center justify-between border-b border-stone-100 px-4 py-3 dark:border-night-800">
            <div>
                <p class="text-sm font-extrabold text-stone-900 dark:text-white">الإشعارات</p>
                <p class="text-[11px] text-stone-500 dark:text-stone-400">
                    <span x-show="unread > 0" x-text="unread + ' غير مقروء'"></span>
                    <span x-show="unread === 0">لا توجد إشعارات جديدة</span>
                </p>
            </div>
            <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-brand-700 hover:underline dark:text-brand-300">عرض الكل</a>
        </div>

        <div class="max-h-96 overflow-y-auto scrollbar-thin">
            @forelse ($latest as $notification)
                @php $data = $notification->data; @endphp
                <a href="{{ route('notifications.open', $notification) }}" class="flex gap-3 border-b border-stone-50 px-4 py-3 transition last:border-0 hover:bg-stone-50 dark:border-night-800/70 dark:hover:bg-night-800/60">
                    <span @class([
                        'mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl',
                        'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300' => ($data['tone'] ?? '') === 'danger',
                        'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300' => ($data['tone'] ?? '') !== 'danger',
                    ])>
                        <x-ui.icon :name="$data['icon'] ?? 'bell'" size="size-4.5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2">
                            <span class="truncate text-xs font-bold text-stone-900 dark:text-white">{{ $data['title'] ?? 'تنبيه' }}</span>
                            @if (! $notification->read_at)
                                <span class="size-1.5 shrink-0 rounded-full bg-brand-500"></span>
                            @endif
                        </span>
                        <span class="mt-0.5 line-clamp-2 block text-[11px] leading-5 text-stone-500 dark:text-stone-400">{{ $data['message'] ?? '' }}</span>
                        <span class="mt-1 block text-[10px] font-medium text-stone-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            @empty
                <div class="px-4 py-10 text-center">
                    <span class="mx-auto flex size-11 items-center justify-center rounded-full bg-stone-100 text-stone-400 dark:bg-night-800 dark:text-stone-500">
                        <x-ui.icon name="bell" size="size-5" />
                    </span>
                    <p class="mt-3 text-xs font-semibold text-stone-500 dark:text-stone-400">لا توجد إشعارات حتى الآن</p>
                </div>
            @endforelse
        </div>

        @if ($latest->isNotEmpty())
            <div class="border-t border-stone-100 p-2 dark:border-night-800">
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-center text-[11px] font-bold text-stone-500 transition hover:bg-stone-50 hover:text-stone-900 dark:text-stone-400 dark:hover:bg-night-800 dark:hover:text-white">
                        تعليم الكل كمقروء
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
