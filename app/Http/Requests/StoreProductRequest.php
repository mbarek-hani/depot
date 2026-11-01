<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:30'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => filled($this->sku) ? trim((string) $this->sku) : null,
            'quantity' => $this->quantity === null || $this->quantity === '' ? 0 : $this->quantity,
        ]);
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'sku' => 'رمز المنتج',
            'category_id' => 'الصنف',
            'unit' => 'الوحدة',
            'quantity' => 'الكمية',
            'low_stock_threshold' => 'حد المخزون المنخفض',
            'expiry_date' => 'تاريخ الانتهاء',
            'cost_price' => 'سعر التكلفة',
            'sale_price' => 'سعر البيع',
            'notes' => 'الملاحظات',
        ];
    }
}
