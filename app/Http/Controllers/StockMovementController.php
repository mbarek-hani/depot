<?php

namespace App\Http\Controllers;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Http\Requests\StoreMovementRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Support\Format;
use App\Support\Present;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController extends Controller
{
    public function index(Request $request): Response
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

        return Inertia::render('Movements/Index', [
            'movements' => $movements->through(fn (StockMovement $movement) => Present::movement($movement)),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $filters['search'],
                'type' => $filters['type'],
                'category_id' => $filters['category_id'],
                'from' => $filters['from']?->format('Y-m-d'),
                'to' => $filters['to']?->format('Y-m-d'),
            ],
            'totals' => [
                'in' => $totals['in'],
                'out' => $totals['out'],
                'count' => $totals['count'],
                'in_formatted' => Format::quantity($totals['in']),
                'out_formatted' => Format::quantity($totals['out']),
                'net_formatted' => Format::quantity($totals['in'] - $totals['out']),
            ],
            'products' => Product::query()->with('activeBatches')->orderBy('name')->get(['id', 'name', 'sku', 'quantity', 'unit'])
                ->map(fn (Product $product) => Present::productOption($product))->all(),
            'reasons' => collect(MovementReason::cases())->map(fn (MovementReason $reason) => [
                'value' => $reason->value,
                'label' => $reason->label(),
            ])->all(),
        ]);
    }

    public function store(StoreMovementRequest $request, StockService $stock): RedirectResponse
    {
        $data = $request->validated();
        $type = StockMovementType::from($data['type']);
        $reason = $type === StockMovementType::Out ? MovementReason::from($data['reason']) : null;
        $expiryDate = $type === StockMovementType::In && filled($data['expiry_date'] ?? null)
            ? Carbon::parse($data['expiry_date'])
            : null;

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
            $expiryDate,
        );

        return back()->with(
            'status',
            'تم تسجيل حركة '.$type->label().' بمقدار '.number_format((float) $movement->quantity, 2).' '.$product->unit.' للمنتج «'.$product->name.'».',
        );
    }
}
