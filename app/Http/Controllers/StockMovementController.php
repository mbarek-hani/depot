<?php

namespace App\Http\Controllers;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Http\Requests\Admin\StoreMovementRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(['in', 'out'])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'type' => $request->string('type')->toString(),
            'category_id' => $request->integer('category_id') ?: null,
            'from' => $request->date('from'),
            'to' => $request->date('to'),
        ];

        $baseQuery = StockMovement::query()
            ->with(['product', 'user'])
            ->search($filters['search'])
            ->when(in_array($filters['type'], ['in', 'out'], true), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when($filters['category_id'], fn (Builder $query, $id) => $query->whereHas('product', fn (Builder $query) => $query->where('category_id', $id)))
            ->when($filters['from'], fn (Builder $query, $date) => $query->where('occurred_at', '>=', $date->copy()->startOfDay()))
            ->when($filters['to'], fn (Builder $query, $date) => $query->where('occurred_at', '<=', $date->copy()->endOfDay()));

        $totals = [
            'in' => (float) (clone $baseQuery)->where('type', StockMovementType::In->value)->sum('quantity'),
            'out' => (float) (clone $baseQuery)->where('type', StockMovementType::Out->value)->sum('quantity'),
            'count' => (clone $baseQuery)->count(),
        ];

        $movements = $baseQuery
            ->latest('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return view('movements.index', [
            'movements' => $movements,
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => $filters,
            'totals' => $totals,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'quantity', 'unit']),
            'reasons' => MovementReason::cases(),
        ]);
    }

    public function store(StoreMovementRequest $request, StockService $stock): RedirectResponse
    {
        $data = $request->validated();
        $type = StockMovementType::from($data['type']);
        $reason = MovementReason::from($data['reason']);

        if (! in_array($reason, MovementReason::forType($type), true)) {
            throw ValidationException::withMessages([
                'reason' => 'سبب الحركة المحدد لا يتوافق مع نوع الحركة.',
            ]);
        }

        $product = Product::query()->findOrFail($data['product_id']);
        $occurredAt = filled($data['occurred_at'] ?? null) ? Carbon::parse($data['occurred_at']) : now();

        $movement = $stock->record(
            $product,
            $type,
            (float) $data['quantity'],
            $reason,
            $request->user(),
            $data['note'] ?? null,
            $occurredAt,
        );

        return back()->with(
            'status',
            'تم تسجيل حركة '.$type->label().' بمقدار '.number_format((float) $movement->quantity, 2).' '.$product->unit.' للمنتج «'.$product->name.'».',
        );
    }
}
