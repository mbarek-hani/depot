<?php

namespace App\Http\Requests;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'reason' => ['required', Rule::enum(MovementReason::class)],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'occurred_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'المنتج',
            'type' => 'نوع الحركة',
            'reason' => 'سبب الحركة',
            'quantity' => 'الكمية',
            'occurred_at' => 'تاريخ الحركة',
            'note' => 'الملاحظات',
        ];
    }
}
