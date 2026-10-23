@props(['users'])

<div class="hidden overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900 lg:block">
    <div class="overflow-x-auto scrollbar-thin">
        <table class="w-full min-w-[50rem]">
            <thead>
                <tr class="border-b border-stone-100 bg-stone-50/70 text-[11px] font-bold text-stone-500 dark:border-night-800 dark:bg-night-950/60 dark:text-stone-400">
                    <th class="px-5 py-3.5 text-start font-bold">المستخدم</th>
                    <th class="px-4 py-3.5 text-start font-bold">الدور</th>
                    <th class="px-4 py-3.5 text-start font-bold">الحالة</th>
                    <th class="px-4 py-3.5 text-start font-bold">التنبيهات</th>
                    <th class="px-4 py-3.5 text-start font-bold">الحركات</th>
                    <th class="px-5 py-3.5 text-end font-bold">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-night-800">
                @foreach ($users as $user)
                    <tr class="transition hover:bg-stone-50/60 dark:hover:bg-night-800/30">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :name="$user->name" size="size-9" />
                                <div class="min-w-0">
                                    <p class="max-w-56 truncate text-xs font-bold text-stone-900 dark:text-white">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-[10px] font-medium text-brand-700 dark:text-brand-300">(أنت)</span>
                                        @endif
                                    </p>
                                    <p class="mt-0.5 max-w-56 truncate text-[10px] text-stone-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <x-ui.badge :tone="$user->role->tone()">{{ $user->role->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3.5">
                            <x-ui.badge :tone="$user->is_active ? 'success' : 'danger'" dot>{{ $user->is_active ? 'نشط' : 'موقوف' }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3.5">
                            @if ($user->isAdmin())
                                <span class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">جميع التنبيهات</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @if ($user->receive_low_stock_alerts)
                                        <x-ui.badge tone="warning">المخزون</x-ui.badge>
                                    @endif
                                    @if ($user->receive_expiry_alerts)
                                        <x-ui.badge tone="danger">الصلاحية</x-ui.badge>
                                    @endif
                                    @if (! $user->receive_low_stock_alerts && ! $user->receive_expiry_alerts)
                                        <span class="text-[10px] text-stone-400">لا يوجد</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-xs font-semibold text-stone-700 tabular dark:text-stone-200">{{ number_format($user->movements_count) }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.button :href="route('users.edit', $user)" variant="ghost" size="icon" icon="pencil" aria-label="تعديل المستخدم" />

                                @if (! $user->isAdmin() && $user->id !== auth()->id())
                                    <button type="button" x-on:click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')" class="inline-flex size-10 items-center justify-center rounded-xl text-rose-500 transition hover:bg-rose-50 dark:hover:bg-rose-500/10" aria-label="حذف المستخدم">
                                        <x-ui.icon name="trash" size="size-4.5" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-ui.pagination :paginator="$users" />
</div>
