@props(['href', 'icon', 'active' => false, 'badge' => null])

<a
    href="{{ $href }}"
    @class([
        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
        'bg-brand-400 text-night-950 shadow-sm' => $active,
        'text-stone-600 hover:bg-stone-100 hover:text-stone-900 dark:text-stone-400 dark:hover:bg-white/5 dark:hover:text-white' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
>
    <x-ui.icon :name="$icon" size="size-5" @class(['shrink-0', 'text-night-800' => $active, 'text-stone-400 group-hover:text-stone-700 dark:text-stone-500 dark:group-hover:text-stone-300' => ! $active]) />
    <span class="min-w-0 flex-1 truncate">{{ $slot }}</span>
    @if ($badge)
        <span @class([
            'min-w-5 rounded-full px-1.5 py-0.5 text-center text-[10px] font-extrabold',
            'bg-night-950 text-brand-300' => $active,
            'bg-rose-500 text-white' => ! $active,
        ])>{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>
