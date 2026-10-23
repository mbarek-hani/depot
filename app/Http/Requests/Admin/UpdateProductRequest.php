<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('product')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:30'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'sku' => 'رمز المنتج',
            'category_id' => 'الصنف',
            'unit' => 'الوحدة',
            'low_stock_threshold' => 'حد المخزون المنخفض',
            'expiry_date' => 'تاريخ الانتهاء',
            'cost_price' => 'سعر التكلفة',
            'sale_price' => 'سعر البيع',
            'notes' => 'الملاحظات',
        ];
    }
}
