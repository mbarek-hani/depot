<x-layouts.app title="إضافة مستخدم">
    <x-layout.page-header
        title="إضافة مستخدم جديد"
        description="سيتمكن المستخدم من تسجيل الدخول وعرض البيانات والتقارير فقط."
        eyebrow="الإدارة"
    >
        <x-slot:actions>
            <x-ui.button :href="route('users.index')" variant="outline" icon="arrow-right">عودة للمستخدمين</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 max-w-3xl">
        <x-users.form
            :action="route('users.store')"
            submit-label="إنشاء الحساب"
        />
    </div>
</x-layouts.app>
