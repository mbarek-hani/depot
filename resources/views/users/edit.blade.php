<x-layouts.app title="تعديل مستخدم">
    <x-layout.page-header
        :title="'تعديل: '.$user->name"
        :description="$user->email"
        eyebrow="الإدارة"
    >
        <x-slot:actions>
            <x-ui.button :href="route('users.index')" variant="outline" icon="arrow-right">عودة للمستخدمين</x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    <div class="mt-6 max-w-3xl">
        <x-users.form
            :user="$user"
            :action="route('users.update', $user)"
            method="PATCH"
            submit-label="حفظ التعديلات"
        />
    </div>
</x-layouts.app>
