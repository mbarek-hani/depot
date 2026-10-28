<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Format;
use App\Support\Present;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $defaultThreshold = Settings::lowStockThreshold();
        $warningDays = Settings::expiryWarningDays();

        $productQuery = Product::query();

        $stats = [
            'products' => (clone $productQuery)->count(),
            'quantity' => (float) (clone $productQuery)->sum('quantity'),
            'low_stock' => (clone $productQuery)->lowStock()->count(),
            'out_of_stock' => (clone $productQuery)->outOfStock()->count(),
            'expiring' => (clone $productQuery)->expiringSoon()->count(),
            'expired' => (clone $productQuery)->expired()->count(),
            'stock_cost' => (float) (clone $productQuery)
                ->selectRaw('COALESCE(SUM(quantity * cost_price), 0) as total')
                ->value('total'),
            'stock_sale' => (float) (clone $productQuery)
                ->selectRaw('COALESCE(SUM(quantity * sale_price), 0) as total')
                ->value('total'),
            'in_today' => (float) StockMovement::query()
                ->where('type', StockMovementType::In->value)
                ->whereDate('occurred_at', today())
                ->sum('quantity'),
            'out_today' => (float) StockMovement::query()
                ->where('type', StockMovementType::Out->value)
                ->whereDate('occurred_at', today())
                ->sum('quantity'),
            'movements_today' => StockMovement::query()->whereDate('occurred_at', today())->count(),
        ];

        $start = today()->subDays(6);

        $movementTotals = StockMovement::query()
            ->selectRaw('DATE(occurred_at) as day, type, SUM(quantity) as total')
            ->where('occurred_at', '>=', $start->copy()->startOfDay())
            ->groupBy('day', 'type')
            ->get();

        $days = collect(range(0, 6))->map(fn (int $offset) => $start->copy()->addDays($offset));
        $chart = [
            'labels' => $days->map(fn ($day) => $day->translatedFormat('D j/n'))->all(),
            'datasets' => [
                [
                    'label' => 'إدخال',
                    'values' => $days->map(fn ($day) => (float) $movementTotals
                        ->where('day', $day->toDateString())
                        ->where('type', StockMovementType::In->value)
                        ->sum('total'))->all(),
                ],
                [
                    'label' => 'إخراج',
                    'values' => $days->map(fn ($day) => (float) $movementTotals
                        ->where('day', $day->toDateString())
                        ->where('type', StockMovementType::Out->value)
                        ->sum('total'))->all(),
                ],
            ],
        ];

        $categoryTotals = Category::query()
            ->withSum('products as quantity_total', 'quantity')
            ->orderByDesc('quantity_total')
            ->get()
            ->filter(fn (Category $category) => (float) $category->quantity_total > 0)
            ->values();

        $categoryChart = [
            'labels' => $categoryTotals->pluck('name')->all(),
            'values' => $categoryTotals->map(fn (Category $category) => (float) $category->quantity_total)->all(),
        ];

        $recentMovements = StockMovement::query()
            ->with(['product', 'user'])
            ->latest('occurred_at')
            ->take(6)
            ->get();

        $lowStockProducts = Product::query()
            ->with('category')
            ->lowStock()
            ->orderBy('quantity')
            ->take(5)
            ->get();

        $expiringProducts = Product::query()
            ->with('category')
            ->expiringSoon()
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'products' => $stats['products'],
                'quantity' => $stats['quantity'],
                'quantity_formatted' => Format::quantity($stats['quantity']),
                'low_stock' => $stats['low_stock'],
                'out_of_stock' => $stats['out_of_stock'],
                'expiring' => $stats['expiring'],
                'expired' => $stats['expired'],
                'stock_cost_formatted' => Format::money($stats['stock_cost']),
                'stock_sale_formatted' => Format::money($stats['stock_sale']),
                'movements_today' => $stats['movements_today'],
                'in_today_formatted' => Format::quantity($stats['in_today']),
                'out_today_formatted' => Format::quantity($stats['out_today']),
            ],
            'chart' => $chart,
            'categoryChart' => $categoryChart,
            'recentMovements' => $recentMovements->map(fn ($movement) => Present::movement($movement))->all(),
            'lowStockProducts' => $lowStockProducts->map(fn ($product) => Present::product($product))->all(),
            'expiringProducts' => $expiringProducts->map(fn ($product) => Present::product($product))->all(),
            'defaultThreshold' => $defaultThreshold,
            'warningDays' => $warningDays,
        ]);
    }
}
