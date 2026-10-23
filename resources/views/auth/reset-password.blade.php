<x-layouts.guest title="إعادة تعيين كلمة المرور" heading="اختر كلمة مرور جديدة" description="يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.input
            name="email"
            label="البريد الإلكتروني"
            type="email"
            icon="mail"
            required
            autocomplete="username"
        />

        <x-ui.input
            name="password"
            label="كلمة المرور الجديدة"
            type="password"
            icon="lock"
            required
            autocomplete="new-password"
            hint="8 أحرف على الأقل."
        />

        <x-ui.input
            name="password_confirmation"
            label="تأكيد كلمة المرور"
            type="password"
            icon="lock"
            required
            autocomplete="new-password"
        />

        <x-ui.button type="submit" size="lg" class="w-full">
            حفظ كلمة المرور
        </x-ui.button>
    </form>
</x-layouts.guest>
