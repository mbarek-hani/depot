@props(['users'])

<div class="space-y-3 lg:hidden">
    @foreach ($users as $user)
        <article class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-card dark:border-night-800 dark:bg-night-900">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <x-ui.avatar :name="$user->name" size="size-10" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-stone-900 dark:text-white">{{ $user->name }}</p>
                        <p class="mt-0.5 truncate text-[10px] text-stone-400">{{ $user->email }}</p>
                    </div>
                </div>
                <x-ui.badge :tone="$user->is_active ? 'success' : 'danger'" dot>{{ $user->is_active ? 'نشط' : 'موقوف' }}</x-ui.badge>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                <x-ui.badge :tone="$user->role->tone()">{{ $user->role->label() }}</x-ui.badge>
                @if ($user->isAdmin())
                    <x-ui.badge tone="brand">جميع التنبيهات</x-ui.badge>
                @else
                    @if ($user->receive_low_stock_alerts)
                        <x-ui.badge tone="warning">تنبيه المخزون</x-ui.badge>
                    @endif
                    @if ($user->receive_expiry_alerts)
                        <x-ui.badge tone="danger">تنبيه الصلاحية</x-ui.badge>
                    @endif
                @endif
                <x-ui.badge tone="neutral">{{ number_format($user->movements_count) }} حركة</x-ui.badge>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
                <x-ui.button :href="route('users.edit', $user)" variant="brand-soft" size="sm" icon="pencil">تعديل</x-ui.button>
                @if (! $user->isAdmin() && $user->id !== auth()->id())
                    <button type="button" x-on:click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')" class="flex size-9 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف المستخدم">
                        <x-ui.icon name="trash" size="size-4" />
                    </button>
                @endif
            </div>
        </article>
    @endforeach

    <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
        <x-ui.pagination :paginator="$users" />
    </div>
</div>
