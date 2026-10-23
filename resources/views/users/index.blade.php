<x-layouts.app title="المستخدمون">
    <x-layout.page-header
        title="المستخدمون"
        description="أنشئ حسابات للاطلاع على المخزون والتقارير دون صلاحية تعديل."
        eyebrow="الإدارة"
    >
        <x-slot:actions>
            <x-ui.button :href="route('users.create')" icon="plus">إضافة مستخدم</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 space-y-4">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <x-ui.stat-card label="إجمالي المستخدمين" :value="number_format($summary['total'])" icon="users" tone="brand" />
            <x-ui.stat-card label="مديرو النظام" :value="number_format($summary['admins'])" icon="shield" tone="info" />
            <x-ui.stat-card label="الحسابات النشطة" :value="number_format($summary['active'])" icon="check-circle" tone="success" />
            <x-ui.stat-card label="مستخدمو العرض" :value="number_format($summary['restricted'])" icon="eye" tone="neutral" />
        </div>

        <div class="flex items-start gap-2.5 rounded-xl border border-stone-200 bg-white/70 p-3.5 text-[11px] leading-5 text-stone-500 dark:border-night-800 dark:bg-night-900/60 dark:text-stone-400">
            <x-ui.icon name="info" size="size-4" class="mt-0.5 shrink-0 text-brand-600 dark:text-brand-300" />
            <p>لا يوجد تسجيل عام. حسابات المستخدمين تُنشأ هنا فقط، وتملك صلاحية قراءة البيانات وتنزيل التقارير دون أي تعديل على المخزون.</p>
        </div>

        <x-users.filters :filters="$filters" />

        @if ($users->isEmpty())
            <div class="rounded-2xl border border-stone-200/80 bg-white shadow-card dark:border-night-800 dark:bg-night-900">
                <x-ui.empty-state icon="users" title="لا يوجد مستخدمون مطابقون" description="جرّب تغيير الفلاتر أو أضف مستخدمًا جديدًا.">
                    <x-slot:actions>
                        <x-ui.button :href="route('users.create')" icon="plus">إضافة مستخدم</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            </div>
        @else
            <x-users.table :users="$users" />
            <x-users.cards :users="$users" />

            @foreach ($users as $user)
                @if (! $user->isAdmin() && $user->id !== auth()->id())
                    <x-ui.modal name="delete-user-{{ $user->id }}" title="تأكيد حذف المستخدم" size="sm">
                        <div class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm text-rose-800 dark:bg-rose-500/10 dark:text-rose-200">
                            <x-ui.icon name="warning" size="size-5" class="mt-0.5 shrink-0" />
                            <p>سيتم حذف حساب «{{ $user->name }}» نهائيًا. ستبقى حركات المخزون المرتبطة به محفوظة دون اسم المستخدم.</p>
                        </div>
                        <x-slot:footer>
                            <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'delete-user-{{ $user->id }}')">إلغاء</x-ui.button>
                            <form method="POST" action="{{ route('users.destroy', $user) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="danger" icon="trash">حذف المستخدم</x-ui.button>
                            </form>
                        </x-slot:footer>
                    </x-ui.modal>
                @endif
            @endforeach
        @endif
    </div>
</x-layouts.app>
