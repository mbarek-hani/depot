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
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'reason' => ['nullable', 'required_if:type,out', Rule::enum(MovementReason::class)],
            'expiry_date' => ['nullable', 'date', 'prohibited_if:type,out'],
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
            'expiry_date' => 'تاريخ الانتهاء',
            'quantity' => 'الكمية',
            'occurred_at' => 'تاريخ الحركة',
            'note' => 'الملاحظات',
        ];
    }
}
