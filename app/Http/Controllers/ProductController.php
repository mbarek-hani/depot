<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\AlertService;
use App\Services\StockService;
use App\Support\Format;
use App\Support\Present;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $sort = $request->string('sort')->toString() ?: 'created_at|desc';
        [$sortColumn, $sortDirection] = array_pad(explode('|', $sort, 2), 2, 'asc');

        if (! in_array($sortColumn, ['name', 'quantity', 'expiry_date', 'created_at'], true)) {
            $sortColumn = 'created_at';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'category_id' => $request->integer('category_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'sort' => $sortColumn.'|'.$sortDirection,
        ];

        $defaultThreshold = Settings::lowStockThreshold();
        $rows = $this->stockRows($filters, $sortColumn, $sortDirection, $defaultThreshold);
        $categories = Category::query()->orderBy('name')->get();
        $categoryColors = $categories->mapWithKeys(fn (Category $category) => [$category->id => $category->colorClasses()]);
        $warningDays = Settings::expiryWarningDays();

        $summary = [
            'total' => Product::count(),
            'low' => Product::lowStock()->count(),
            'out' => Product::outOfStock()->count(),
            'expiring' => StockMovement::query()
                ->activeBatches()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', today())
                ->whereDate('expiry_date', '<=', today()->addDays($warningDays))
                ->count(),
        ];

        return Inertia::render('Products/Index', [
            'products' => $rows->through(fn (object $row) => Present::stockRow(
                $row,
                $categoryColors->get($row->category_id),
                $defaultThreshold,
            )),
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ])->all(),
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    protected function stockRows(array $filters, string $sortColumn, string $sortDirection, float $defaultThreshold)
    {
        $batchQuery = DB::table('stock_movements as m')
            ->join('products as p', 'p.id', '=', 'm.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('m.type', StockMovementType::In->value)
            ->where('m.quantity_remaining', '>', 0)
            ->select([
                'm.id as batch_id',
                'p.id as product_id',
                'p.category_id',
                'p.name',
                'p.sku',
                'p.unit',
                'p.quantity',
                'p.low_stock_threshold',
                'p.cost_price',
                'p.sale_price',
                'p.created_at',
                'p.updated_at',
                'c.name as category_name',
                'c.color as category_color_key',
                'm.expiry_date as batch_expiry_date',
                'm.quantity_remaining as row_quantity',
            ]);

        $productQuery = DB::table('products as p')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('stock_movements as m')
                    ->whereColumn('m.product_id', 'p.id')
                    ->where('m.type', StockMovementType::In->value)
                    ->where('m.quantity_remaining', '>', 0);
            })
            ->select([
                DB::raw('NULL as batch_id'),
                'p.id as product_id',
                'p.category_id',
                'p.name',
                'p.sku',
                'p.unit',
                'p.quantity',
                'p.low_stock_threshold',
                'p.cost_price',
                'p.sale_price',
                'p.created_at',
                'p.updated_at',
                'c.name as category_name',
                'c.color as category_color_key',
                DB::raw('NULL as batch_expiry_date'),
                'p.quantity as row_quantity',
            ]);

        $this->applyProductFilters($batchQuery, $filters);
        $this->applyProductFilters($productQuery, $filters);

        $status = $filters['status'];
        $includeUnbatched = ! in_array($status, ['expiring', 'expired'], true);

        if (in_array($status, ['low', 'out', 'available'], true)) {
            $this->applyStockStatus($batchQuery, $status, $defaultThreshold);
            $this->applyStockStatus($productQuery, $status, $defaultThreshold);
        }

        if ($status === 'expiring' || $status === 'expired') {
            $warningDays = Settings::expiryWarningDays();

            $batchQuery
                ->whereNotNull('m.expiry_date')
                ->when($status === 'expiring', fn ($query) => $query
                    ->whereDate('m.expiry_date', '>=', today())
                    ->whereDate('m.expiry_date', '<=', today()->addDays($warningDays)))
                ->when($status === 'expired', fn ($query) => $query
                    ->whereDate('m.expiry_date', '<', today()));
        }

        $union = $includeUnbatched ? $batchQuery->unionAll($productQuery) : $batchQuery;
        $query = DB::query()->fromSub($union, 'rows');

        if ($sortColumn === 'expiry_date') {
            $query
                ->orderByRaw('CASE WHEN rows.batch_expiry_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('rows.batch_expiry_date', $sortDirection);
        } else {
            $query->orderBy('rows.'.$sortColumn, $sortDirection);
        }

        return $query->paginate(12)->withQueryString();
    }

    protected function applyProductFilters($query, array $filters): void
    {
        $query
            ->when($filters['search'], fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('p.name', 'like', "%{$search}%")
                    ->orWhere('p.sku', 'like', "%{$search}%")
                    ->orWhere('p.notes', 'like', "%{$search}%");
            }))
            ->when($filters['category_id'], fn ($query, $categoryId) => $query->where('p.category_id', $categoryId));
    }

    protected function applyStockStatus($query, string $status, float $defaultThreshold): void
    {
        match ($status) {
            'low' => $query
                ->where('p.quantity', '>', 0)
                ->whereRaw('p.quantity <= COALESCE(p.low_stock_threshold, ?)', [$defaultThreshold]),
            'out' => $query->where('p.quantity', '<=', 0),
            'available' => $query
                ->where('p.quantity', '>', 0)
                ->whereRaw('p.quantity > COALESCE(p.low_stock_threshold, ?)', [$defaultThreshold]),
            default => null,
        };
    }

    public function create(): Response
    {
        Gate::authorize('create', Product::class);

        return Inertia::render('Products/Create', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'defaultThreshold' => Settings::lowStockThreshold(),
            'currency' => Settings::currency(),
        ]);
    }

    public function store(StoreProductRequest $request, StockService $stock, AlertService $alerts): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $data = $request->validated();
        $initialQuantity = (float) ($data['quantity'] ?? 0);
        $initialExpiry = filled($data['expiry_date'] ?? null) ? Carbon::parse($data['expiry_date']) : null;
        unset($data['quantity'], $data['expiry_date']);

        $product = DB::transaction(function () use ($data, $initialQuantity, $initialExpiry, $request, $stock) {
            $product = Product::create($data);

            if ($initialQuantity > 0) {
                $stock->record(
                    $product,
                    StockMovementType::In,
                    $initialQuantity,
                    null,
                    $request->user(),
                    'الرصيد الافتتاحي عند إنشاء المنتج',
                    null,
                    $initialExpiry,
                );
            }

            return $product;
        });

        $alerts->evaluateProduct($product);

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'تم إضافة المنتج «'.$product->name.'» إلى المخزون بنجاح.');
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'activeBatches']);

        $movements = $product->movements()
            ->with('user')
            ->latest('occurred_at')
            ->paginate(10);

        $totals = [
            'in' => (float) $product->movements()->where('type', StockMovementType::In->value)->sum('quantity'),
            'out' => (float) $product->movements()->where('type', StockMovementType::Out->value)->sum('quantity'),
        ];

        return Inertia::render('Products/Show', [
            'product' => Present::product($product),
            'batches' => $product->activeBatches->map(fn (StockMovement $batch) => Present::batch($batch))->values()->all(),
            'movements' => $movements->through(fn (StockMovement $movement) => Present::movement($movement)),
            'totals' => [
                'in' => $totals['in'],
                'out' => $totals['out'],
                'in_formatted' => Format::quantity($totals['in']),
                'out_formatted' => Format::quantity($totals['out']),
            ],
        ]);
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        $product->load('category');

        return Inertia::render('Products/Edit', [
            'product' => Present::product($product),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'defaultThreshold' => Settings::lowStockThreshold(),
            'currency' => Settings::currency(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, AlertService $alerts): RedirectResponse
    {
        Gate::authorize('update', $product);

        $product->update($request->validated());
        $alerts->evaluateProduct($product);

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'تم تحديث بيانات المنتج بنجاح.');
    }

    public function destroy(Product $product, AlertService $alerts): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $name = $product->name;
        $alerts->resolveProduct($product);
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'تم حذف المنتج «'.$name.'».');
    }
}
