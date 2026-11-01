<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Format;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ReportService
{
    public function build(string $type, array $filters = []): array
    {
        $categoryId = isset($filters['category_id']) && $filters['category_id'] !== '' ? (int) $filters['category_id'] : null;
        $status = $filters['status'] ?? 'all';

        return match ($type) {
            'movements' => $this->movementsReport($categoryId, $status, $filters['from'] ?? null, $filters['to'] ?? null),
            'low-stock' => $this->lowStockReport($categoryId),
            'expiry' => $this->expiryReport($categoryId, $status),
            'categories' => $this->categoriesReport($categoryId),
            default => $this->stockReport($categoryId, $status),
        };
    }

    public static function types(): array
    {
        return [
            'stock' => 'جرد المخزون',
            'movements' => 'حركات المخزون',
            'low-stock' => 'المخزون المنخفض',
            'expiry' => 'تواريخ الصلاحية',
            'categories' => 'ملخص الأصناف',
        ];
    }

    protected function stockReport(?int $categoryId, string $status): array
    {
        $products = Product::query()
            ->with(['category', 'activeBatches'])
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->filterStatus($status === 'all' ? null : $status)
            ->orderBy('name')
            ->get();

        return [
            'type' => 'stock',
            'title' => 'تقرير جرد المخزون',
            'description' => 'صورة شاملة للمنتجات المتوفرة في المستودع مع الكميات والقيم المالية.',
            'period' => 'حتى تاريخ '.Format::longDate(now()),
            'summary' => [
                ['label' => 'إجمالي المنتجات', 'value' => number_format($products->count())],
                ['label' => 'إجمالي الكميات', 'value' => Format::quantity($products->sum(fn ($product) => (float) $product->quantity))],
                ['label' => 'قيمة المخزون بالتكلفة', 'value' => Format::money($products->sum(fn ($product) => $product->stockCostValue()))],
                ['label' => 'القيمة المتوقعة للبيع', 'value' => Format::money($products->sum(fn ($product) => $product->stockSaleValue()))],
            ],
            'columns' => ['المنتج', 'الرمز', 'الصنف', 'الكمية', 'الوحدة', 'حالة المخزون', 'الصلاحية', 'قيمة التكلفة'],
            'rows' => $products->map(function (Product $product) {
                $expiry = $product->nearestExpiryDate();

                return [
                    $product->name,
                    $product->sku,
                    $product->category?->name ?? '—',
                    Format::quantity($product->quantity),
                    $product->unit,
                    $product->stockStatus()->label(),
                    $expiry ? Format::date($expiry).' · '.$product->expiryStatus()->label() : 'بدون تاريخ',
                    Format::money($product->stockCostValue()),
                ];
            })->all(),
            'totals' => [
                'الكميات' => Format::quantity($products->sum(fn ($product) => (float) $product->quantity)),
                'قيمة التكلفة' => Format::money($products->sum(fn ($product) => $product->stockCostValue())),
                'قيمة البيع' => Format::money($products->sum(fn ($product) => $product->stockSaleValue())),
            ],
        ];
    }

    protected function movementsReport(?int $categoryId, string $status, ?Carbon $from, ?Carbon $to): array
    {
        $from ??= now()->startOfMonth();
        $to ??= now();

        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->whereBetween('occurred_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($categoryId, fn (Builder $query) => $query->whereHas('product', fn (Builder $query) => $query->where('category_id', $categoryId)))
            ->when(in_array($status, ['in', 'out'], true), fn (Builder $query) => $query->where('type', $status))
            ->latest('occurred_at')
            ->get();

        $in = $movements->where('type', StockMovementType::In)->sum(fn ($movement) => (float) $movement->quantity);
        $out = $movements->where('type', StockMovementType::Out)->sum(fn ($movement) => (float) $movement->quantity);

        return [
            'type' => 'movements',
            'title' => 'تقرير حركات المخزون',
            'description' => 'جميع عمليات الإدخال والإخراج المسجلة خلال الفترة المحددة.',
            'period' => Format::longDate($from).' — '.Format::longDate($to),
            'summary' => [
                ['label' => 'عدد الحركات', 'value' => number_format($movements->count())],
                ['label' => 'إجمالي الإدخال', 'value' => Format::quantity($in)],
                ['label' => 'إجمالي الإخراج', 'value' => Format::quantity($out)],
                ['label' => 'صافي الحركة', 'value' => Format::quantity($in - $out)],
            ],
            'columns' => ['التاريخ', 'المنتج', 'النوع', 'الكمية', 'السبب', 'المستخدم', 'ملاحظات'],
            'rows' => $movements->map(fn (StockMovement $movement) => [
                Format::dateTime($movement->occurred_at),
                $movement->product?->name ?? 'منتج محذوف',
                $movement->type->label(),
                $movement->type->sign().Format::quantity($movement->quantity),
                $movement->reason?->label() ?? '—',
                $movement->user?->name ?? '—',
                $movement->note ?: '—',
            ])->all(),
            'totals' => [
                'عدد الحركات' => number_format($movements->count()),
                'إجمالي الإدخال' => Format::quantity($in),
                'إجمالي الإخراج' => Format::quantity($out),
            ],
        ];
    }

    protected function lowStockReport(?int $categoryId): array
    {
        $products = Product::query()
            ->with('category')
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->lowOrOutOfStock()
            ->orderBy('quantity')
            ->get();

        $outCount = $products->filter(fn (Product $product) => (float) $product->quantity <= 0)->count();
        $shortage = $products->sum(fn (Product $product) => max(0, $product->lowStockThresholdValue() - (float) $product->quantity));

        return [
            'type' => 'low-stock',
            'title' => 'تقرير المخزون المنخفض',
            'description' => 'المنتجات التي وصلت إلى حد المخزون المنخفض أو نفدت بالكامل.',
            'period' => 'حتى تاريخ '.Format::longDate(now()),
            'summary' => [
                ['label' => 'منتجات تحتاج متابعة', 'value' => number_format($products->count())],
                ['label' => 'منتجات نفدت', 'value' => number_format($outCount)],
                ['label' => 'النقص الإجمالي عن الحد', 'value' => Format::quantity($shortage)],
                ['label' => 'حد المخزون الافتراضي', 'value' => Format::quantity(Settings::lowStockThreshold())],
            ],
            'columns' => ['المنتج', 'الرمز', 'الصنف', 'الكمية الحالية', 'الحد الأدنى', 'الوحدة', 'الحالة'],
            'rows' => $products->map(fn (Product $product) => [
                $product->name,
                $product->sku,
                $product->category?->name ?? '—',
                Format::quantity($product->quantity),
                Format::quantity($product->lowStockThresholdValue()),
                $product->unit,
                $product->stockStatus()->label(),
            ])->all(),
            'totals' => [
                'عدد المنتجات' => number_format($products->count()),
                'إجمالي الكمية المتبقية' => Format::quantity($products->sum(fn ($product) => (float) $product->quantity)),
            ],
        ];
    }

    protected function expiryReport(?int $categoryId, string $status): array
    {
        $warningDays = Settings::expiryWarningDays();

        $batches = StockMovement::query()
            ->activeBatches()
            ->with(['product.category'])
            ->whereHas('product', fn (Builder $query) => $query->whereNull('deleted_at'))
            ->whereNotNull('expiry_date')
            ->when($categoryId, fn (Builder $query) => $query->whereHas('product', fn (Builder $query) => $query->where('category_id', $categoryId)))
            ->when($status === 'expired', fn (Builder $query) => $query->whereDate('expiry_date', '<', today()))
            ->when($status === 'expiring', fn (Builder $query) => $query
                ->whereDate('expiry_date', '>=', today())
                ->whereDate('expiry_date', '<=', today()->addDays($warningDays)))
            ->when(! in_array($status, ['expired', 'expiring'], true), fn (Builder $query) => $query
                ->whereDate('expiry_date', '<=', today()->addDays($warningDays)))
            ->fefo()
            ->get();

        $expired = $batches->filter(fn (StockMovement $batch) => $batch->expiryStatus()->value === 'expired');
        $soon = $batches->filter(fn (StockMovement $batch) => $batch->expiryStatus()->value === 'expiring');

        return [
            'type' => 'expiry',
            'title' => 'تقرير تواريخ الصلاحية',
            'description' => "دفعات المخزون المنتهية أو التي تنتهي صلاحيتها خلال {$warningDays} يومًا.",
            'period' => 'حتى تاريخ '.Format::longDate(now()),
            'summary' => [
                ['label' => 'دفعات منتهية', 'value' => number_format($expired->count())],
                ['label' => 'دفعات قريبة الانتهاء', 'value' => number_format($soon->count())],
                ['label' => 'الكميات المعرضة للخطر', 'value' => Format::quantity($batches->sum(fn (StockMovement $batch) => (float) $batch->quantity_remaining))],
                ['label' => 'مدة التنبيه', 'value' => $warningDays.' يوم'],
            ],
            'columns' => ['المنتج', 'الرمز', 'الصنف', 'تاريخ الانتهاء', 'المتبقي', 'الكمية المتبقية', 'الحالة'],
            'rows' => $batches->map(fn (StockMovement $batch) => [
                $batch->product?->name ?? 'منتج محذوف',
                $batch->product?->sku ?? '—',
                $batch->product?->category?->name ?? '—',
                Format::date($batch->expiry_date),
                $this->remainingLabel($batch->daysUntilExpiry()),
                Format::quantity($batch->quantity_remaining).' '.($batch->product?->unit ?? ''),
                $batch->expiryStatus()->label(),
            ])->all(),
            'totals' => [
                'عدد الدفعات' => number_format($batches->count()),
                'إجمالي الكميات' => Format::quantity($batches->sum(fn (StockMovement $batch) => (float) $batch->quantity_remaining)),
            ],
        ];
    }

    protected function categoriesReport(?int $categoryId): array
    {
        $categories = Category::query()
            ->with(['products' => fn ($query) => $query->select('id', 'category_id', 'name', 'quantity', 'cost_price', 'sale_price')])
            ->when($categoryId, fn (Builder $query) => $query->whereKey($categoryId))
            ->orderBy('name')
            ->get();

        $rows = $categories->map(function (Category $category) {
            $quantity = $category->products->sum(fn (Product $product) => (float) $product->quantity);
            $cost = $category->products->sum(fn (Product $product) => $product->stockCostValue());
            $sale = $category->products->sum(fn (Product $product) => $product->stockSaleValue());

            return [
                'name' => $category->name,
                'cells' => [
                    $category->name,
                    number_format($category->products->count()),
                    Format::quantity($quantity),
                    Format::money($cost),
                    Format::money($sale),
                ],
                'quantity' => $quantity,
                'cost' => $cost,
                'sale' => $sale,
                'count' => $category->products->count(),
            ];
        });

        return [
            'type' => 'categories',
            'title' => 'تقرير ملخص الأصناف',
            'description' => 'إجمالي الكميات والقيم المالية لكل صنف في المستودع.',
            'period' => 'حتى تاريخ '.Format::longDate(now()),
            'summary' => [
                ['label' => 'عدد الأصناف', 'value' => number_format($categories->count())],
                ['label' => 'عدد المنتجات', 'value' => number_format($rows->sum('count'))],
                ['label' => 'إجمالي الكميات', 'value' => Format::quantity($rows->sum('quantity'))],
                ['label' => 'قيمة المخزون', 'value' => Format::money($rows->sum('cost'))],
            ],
            'columns' => ['الصنف', 'عدد المنتجات', 'إجمالي الكمية', 'قيمة التكلفة', 'قيمة البيع'],
            'rows' => $rows->map(fn (array $row) => $row['cells'])->all(),
            'totals' => [
                'الأصناف' => number_format($categories->count()),
                'الكميات' => Format::quantity($rows->sum('quantity')),
                'قيمة التكلفة' => Format::money($rows->sum('cost')),
                'قيمة البيع' => Format::money($rows->sum('sale')),
            ],
        ];
    }

    protected function remainingLabel(?int $days): string
    {
        if ($days === null) {
            return '—';
        }

        if ($days < 0) {
            return 'متأخر '.abs($days).' يوم';
        }

        if ($days === 0) {
            return 'ينتهي اليوم';
        }

        return $days.' يوم';
    }
}
