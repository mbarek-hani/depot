<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'is_active' => ['boolean'],
            'receive_expiry_alerts' => ['boolean'],
            'receive_low_stock_alerts' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'is_active' => 'حالة الحساب',
            'receive_expiry_alerts' => 'تنبيهات انتهاء الصلاحية',
            'receive_low_stock_alerts' => 'تنبيهات المخزون المنخفض',
        ];
    }
}
