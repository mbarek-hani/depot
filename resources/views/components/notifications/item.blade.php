@props(['notification'])

@php
    $data = $notification->data;
    $isUnread = ! $notification->read_at;
@endphp

<a href="{{ route('notifications.open', $notification) }}" @class([
    'flex items-start gap-3.5 rounded-2xl border p-4 shadow-card transition hover:-translate-y-0.5',
    'border-brand-200 bg-white dark:border-brand-400/20 dark:bg-night-900' => $isUnread,
    'border-stone-200/80 bg-white/70 dark:border-night-800 dark:bg-night-900/60' => ! $isUnread,
])>
    <span @class([
        'flex size-10 shrink-0 items-center justify-center rounded-xl',
        'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300' => ($data['tone'] ?? '') === 'danger',
        'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300' => ($data['tone'] ?? '') !== 'danger',
    ])>
        <x-ui.icon :name="$data['icon'] ?? 'bell'" size="size-5" />
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-sm font-bold text-stone-900 dark:text-white">{{ $data['title'] ?? 'تنبيه' }}</h2>
            @if ($isUnread)
                <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[9px] font-extrabold text-brand-900 dark:bg-brand-400/15 dark:text-brand-200">جديد</span>
            @endif
        </div>
        <p class="mt-1 text-xs leading-6 text-stone-600 dark:text-stone-300">{{ $data['message'] ?? '' }}</p>
        <div class="mt-2 flex flex-wrap items-center gap-3 text-[10px] font-medium text-stone-400">
            <span>{{ $notification->created_at->translatedFormat('j M Y - H:i') }}</span>
            <span>{{ $notification->created_at->diffForHumans() }}</span>
            @if (isset($data['sku']))
                <span class="font-mono">{{ $data['sku'] }}</span>
            @endif
        </div>
    </div>

    <x-ui.icon name="chevron-left" size="size-4" class="mt-1 shrink-0 text-stone-300 dark:text-stone-600" />
</a>
