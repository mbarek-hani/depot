@props(['filters'])

<x-ui.card>
    <form method="GET" action="{{ route('users.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
        <div class="lg:col-span-5">
            <x-ui.input name="search" :value="$filters['search']" placeholder="ابحث بالاسم أو البريد..." icon="search" aria-label="البحث في المستخدمين" />
        </div>

        <div class="lg:col-span-3">
            <x-ui.select name="role" :selected="$filters['role']" placeholder="كل الأدوار" aria-label="الدور">
                <option value="admin" @selected($filters['role'] === 'admin')>مدير النظام</option>
                <option value="user" @selected($filters['role'] === 'user')>مستخدم</option>
            </x-ui.select>
        </div>

        <div class="lg:col-span-2">
            <x-ui.select name="status" :selected="$filters['status']" placeholder="كل الحالات" aria-label="الحالة">
                <option value="active" @selected($filters['status'] === 'active')>نشط</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>موقوف</option>
            </x-ui.select>
        </div>

        <div class="flex items-center gap-2 lg:col-span-2">
            <x-ui.button type="submit" icon="filter" class="w-full">تصفية</x-ui.button>
            <x-ui.button :href="route('users.index')" variant="ghost">مسح</x-ui.button>
        </div>
    </form>
</x-ui.card>
