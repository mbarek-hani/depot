@php
    $user = auth()->user();
    $unreadCount = $unreadCount ?? ($user?->unreadNotifications()->count() ?? 0);
@endphp

<nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2 scrollbar-thin" aria-label="التنقل الرئيسي">
    <p class="px-3 pb-1 pt-3 text-[10px] font-bold uppercase tracking-widest text-stone-400 dark:text-stone-600">القائمة</p>

    <x-layout.nav-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">لوحة التحكم</x-layout.nav-link>
    <x-layout.nav-link :href="route('products.index')" icon="cube" :active="request()->routeIs('products.*')">المنتجات</x-layout.nav-link>
    <x-layout.nav-link :href="route('movements.index')" icon="arrows-right-left" :active="request()->routeIs('movements.*')">الدخول والخروج</x-layout.nav-link>
    <x-layout.nav-link :href="route('reports.index')" icon="chart-bar" :active="request()->routeIs('reports.*')">التقارير</x-layout.nav-link>
    <x-layout.nav-link :href="route('notifications.index')" icon="bell" :active="request()->routeIs('notifications.*')" :badge="$unreadCount ?: null">الإشعارات</x-layout.nav-link>

    @if ($user?->isAdmin())
        <p class="px-3 pb-1 pt-5 text-[10px] font-bold uppercase tracking-widest text-stone-400 dark:text-stone-600">الإدارة</p>
        <x-layout.nav-link :href="route('users.index')" icon="users" :active="request()->routeIs('users.*')">المستخدمون</x-layout.nav-link>
        <x-layout.nav-link :href="route('categories.index')" icon="tag" :active="request()->routeIs('categories.*')">الأصناف</x-layout.nav-link>
    @endif

    <p class="px-3 pb-1 pt-5 text-[10px] font-bold uppercase tracking-widest text-stone-400 dark:text-stone-600">الحساب</p>
    <x-layout.nav-link :href="route('settings.index')" icon="settings" :active="request()->routeIs('settings.*')">الإعدادات</x-layout.nav-link>
</nav>
