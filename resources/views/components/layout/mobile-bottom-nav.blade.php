@php
    $items = [
        ['route' => 'dashboard', 'active' => 'dashboard', 'label' => 'الرئيسية', 'icon' => 'home'],
        ['route' => 'products.index', 'active' => 'products.*', 'label' => 'المنتجات', 'icon' => 'cube'],
        ['route' => 'movements.index', 'active' => 'movements.*', 'label' => 'الحركات', 'icon' => 'arrows-right-left'],
        ['route' => 'notifications.index', 'active' => 'notifications.*', 'label' => 'الإشعارات', 'icon' => 'bell'],
    ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-stone-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-lg dark:border-night-800 dark:bg-night-900/95 lg:hidden" aria-label="التنقل السريع">
    <div class="grid grid-cols-5">
        @foreach ($items as $item)
            @php $isActive = request()->routeIs($item['active']); @endphp
            <a href="{{ route($item['route']) }}" @class([
                'flex flex-col items-center justify-center gap-0.5 py-2.5 text-[10px] font-bold transition',
                'text-brand-700 dark:text-brand-300' => $isActive,
                'text-stone-400 hover:text-stone-700 dark:text-stone-500 dark:hover:text-stone-300' => ! $isActive,
            ])>
                <x-ui.icon :name="$item['icon']" size="size-5" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <button type="button" x-on:click="sidebarOpen = true" class="flex flex-col items-center justify-center gap-0.5 py-2.5 text-[10px] font-bold text-stone-400 transition hover:text-stone-700 dark:text-stone-500 dark:hover:text-stone-300">
            <x-ui.icon name="menu" size="size-5" />
            <span>المزيد</span>
        </button>
    </div>
</nav>
