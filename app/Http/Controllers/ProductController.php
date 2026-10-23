<?php

namespace App\Http\Controllers;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\AlertService;
use App\Services\StockService;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
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

        $products = Product::query()
            ->with('category')
            ->search($filters['search'])
            ->when($filters['category_id'], fn ($query, $id) => $query->where('category_id', $id))
            ->filterStatus($filters['status'])
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        $summary = [
            'total' => Product::count(),
            'low' => Product::lowStock()->count(),
            'out' => Product::outOfStock()->count(),
            'expiring' => Product::expiringSoon()->count(),
        ];

        return view('products.index', compact('products', 'categories', 'filters', 'summary'));
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::query()->orderBy('name')->get(),
            'defaultThreshold' => Settings::lowStockThreshold(),
        ]);
    }

    public function store(StoreProductRequest $request, StockService $stock, AlertService $alerts): RedirectResponse
    {
        $data = $request->validated();
        $initialQuantity = (float) ($data['quantity'] ?? 0);
        unset($data['quantity']);

        $product = DB::transaction(function () use ($data, $initialQuantity, $request, $stock) {
            $product = Product::create($data);

            if ($initialQuantity > 0) {
                $stock->record(
                    $product,
                    StockMovementType::In,
                    $initialQuantity,
                    MovementReason::Opening,
                    $request->user(),
                    'الرصيد الافتتاحي عند إنشاء المنتج',
                );
            }

            return $product;
        });

        $alerts->evaluateProduct($product);

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'تم إضافة المنتج «'.$product->name.'» إلى المخزون بنجاح.');
    }

    public function show(Product $product): View
    {
        $product->load('category');

        $movements = $product->movements()
            ->with('user')
            ->latest('occurred_at')
            ->paginate(10);

        $totals = [
            'in' => (float) $product->movements()->where('type', StockMovementType::In->value)->sum('quantity'),
            'out' => (float) $product->movements()->where('type', StockMovementType::Out->value)->sum('quantity'),
        ];

        return view('products.show', compact('product', 'movements', 'totals'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
            'defaultThreshold' => Settings::lowStockThreshold(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, AlertService $alerts): RedirectResponse
    {
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
            ->with('status', 'تم أرشفة المنتج «'.$name.'».');
    }
}
