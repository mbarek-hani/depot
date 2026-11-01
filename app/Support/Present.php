<?php

namespace App\Support;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Notifications\DatabaseNotification;

class Present
{
    public static function product(Product $product): array
    {
        $product->loadMissing(['category', 'activeBatches']);

        $expiryDate = $product->nearestExpiryDate();
        $expiryStatus = $product->expiryStatus();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'quantity' => (float) $product->quantity,
            'quantity_formatted' => Format::quantity($product->quantity),
            'low_stock_threshold' => $product->low_stock_threshold !== null ? (float) $product->low_stock_threshold : null,
            'threshold_formatted' => Format::quantity($product->lowStockThresholdValue()),
            'expiry_date' => $expiryDate?->format('Y-m-d'),
            'expiry_date_formatted' => Format::date($expiryDate),
            'expiry_long' => $expiryDate ? Format::longDate($expiryDate) : null,
            'expiry_label' => $expiryStatus->label(),
            'expiry_tone' => $expiryStatus->tone(),
            'expiry_status' => $expiryStatus->value,
            'active_batches_count' => $product->activeBatches->count(),
            'active_batches' => $product->activeBatches
                ->map(fn (StockMovement $batch) => self::batch($batch))
                ->values()
                ->all(),
            'stock_label' => $product->stockStatus()->label(),
            'stock_tone' => $product->stockStatus()->tone(),
            'stock_status' => $product->stockStatus()->value,
            'cost_price' => (float) $product->cost_price,
            'sale_price' => (float) $product->sale_price,
            'cost_formatted' => Format::money($product->cost_price),
            'sale_formatted' => Format::money($product->sale_price),
            'stock_cost' => $product->stockCostValue(),
            'stock_cost_formatted' => Format::money($product->stockCostValue()),
            'stock_sale' => $product->stockSaleValue(),
            'stock_sale_formatted' => Format::money($product->stockSaleValue()),
            'margin_formatted' => Format::money($product->stockSaleValue() - $product->stockCostValue()),
            'notes' => $product->notes,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'category_color' => $product->category?->colorClasses(),
            'created_at' => $product->created_at?->toISOString(),
            'updated_at' => $product->updated_at?->toISOString(),
        ];
    }

    public static function productOption(Product $product): array
    {
        $product->loadMissing('activeBatches');

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'quantity' => (float) $product->quantity,
            'quantity_formatted' => Format::quantity($product->quantity),
            'active_batches' => $product->activeBatches
                ->map(fn (StockMovement $batch) => self::batch($batch))
                ->values()
                ->all(),
        ];
    }

    public static function batch(StockMovement $batch): array
    {
        $status = $batch->expiryStatus();

        return [
            'id' => $batch->id,
            'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
            'expiry_date_formatted' => Format::date($batch->expiry_date),
            'expiry_long' => $batch->expiry_date ? Format::longDate($batch->expiry_date) : null,
            'expiry_label' => $status->label(),
            'expiry_tone' => $status->tone(),
            'expiry_status' => $status->value,
            'days_until_expiry' => $batch->daysUntilExpiry(),
            'quantity_remaining' => (float) $batch->quantity_remaining,
            'quantity_remaining_formatted' => Format::quantity($batch->quantity_remaining),
            'occurred_at' => $batch->occurred_at?->toISOString(),
            'occurred_formatted' => Format::date($batch->occurred_at),
        ];
    }

    public static function stockRow(object $row, ?string $categoryColor = null, ?float $defaultThreshold = null): array
    {
        $product = new Product([
            'name' => $row->name,
            'sku' => $row->sku,
            'unit' => $row->unit,
            'quantity' => $row->quantity,
            'low_stock_threshold' => $row->low_stock_threshold,
            'cost_price' => $row->cost_price,
            'sale_price' => $row->sale_price,
        ]);
        $product->id = $row->product_id;
        $product->category_id = $row->category_id;
        $product->created_at = $row->created_at;
        $product->updated_at = $row->updated_at;

        $batch = null;

        if ($row->batch_id !== null) {
            $batch = new StockMovement([
                'expiry_date' => $row->batch_expiry_date,
                'quantity_remaining' => $row->row_quantity,
            ]);
            $batch->id = $row->batch_id;
        }

        $rowQuantity = (float) $row->row_quantity;
        $expiryStatus = $batch?->expiryStatus();
        $stockStatus = $product->stockStatus();
        $threshold = $row->low_stock_threshold !== null
            ? (float) $row->low_stock_threshold
            : ($defaultThreshold ?? Settings::lowStockThreshold());

        return [
            'key' => $row->batch_id !== null ? 'b'.$row->batch_id : 'p'.$row->product_id,
            'batch_id' => $row->batch_id,
            'id' => $row->product_id,
            'product_id' => $row->product_id,
            'name' => $row->name,
            'sku' => $row->sku,
            'unit' => $row->unit,
            'quantity' => $rowQuantity,
            'quantity_formatted' => Format::quantity($rowQuantity),
            'product_quantity' => (float) $row->quantity,
            'product_quantity_formatted' => Format::quantity($row->quantity),
            'low_stock_threshold' => $row->low_stock_threshold !== null ? (float) $row->low_stock_threshold : null,
            'threshold_formatted' => Format::quantity($threshold),
            'expiry_date' => $batch?->expiry_date?->format('Y-m-d'),
            'expiry_date_formatted' => Format::date($batch?->expiry_date),
            'expiry_long' => $batch?->expiry_date ? Format::longDate($batch->expiry_date) : null,
            'expiry_label' => $expiryStatus?->label() ?? 'بدون تاريخ انتهاء',
            'expiry_tone' => $expiryStatus?->tone() ?? 'neutral',
            'expiry_status' => $expiryStatus?->value ?? 'none',
            'stock_label' => $stockStatus->label(),
            'stock_tone' => $stockStatus->tone(),
            'stock_status' => $stockStatus->value,
            'stock_cost' => round($rowQuantity * (float) $row->cost_price, 2),
            'stock_cost_formatted' => Format::money($rowQuantity * (float) $row->cost_price),
            'stock_sale_formatted' => Format::money($rowQuantity * (float) $row->sale_price),
            'category_id' => $row->category_id,
            'category_name' => $row->category_name,
            'category_color' => $categoryColor ?? (new Category(['color' => $row->category_color_key]))->colorClasses(),
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }

    public static function movement(StockMovement $movement, bool $withProduct = true): array
    {
        $product = $movement->product;
        $type = $movement->type;
        $reason = $movement->reason;

        return [
            'id' => $movement->id,
            'product_id' => $movement->product_id,
            'product_name' => $product?->name ?? 'منتج محذوف',
            'product_sku' => $product?->sku,
            'product_unit' => $product?->unit,
            'product_archived' => $product?->trashed() ?? false,
            'product_url' => $withProduct && $product && ! $product->trashed() ? route('products.show', $product) : null,
            'user_name' => $movement->user?->name,
            'type' => $type->value,
            'type_label' => $type->label(),
            'type_tone' => $type->tone(),
            'sign' => $type->sign(),
            'reason' => $reason?->value,
            'reason_label' => $reason?->label() ?? '—',
            'expiry_date' => $movement->expiry_date?->format('Y-m-d'),
            'expiry_date_formatted' => Format::date($movement->expiry_date),
            'quantity' => (float) $movement->quantity,
            'quantity_formatted' => Format::quantity($movement->quantity),
            'quantity_remaining_formatted' => Format::quantity($movement->quantity_remaining),
            'quantity_before_formatted' => Format::quantity($movement->quantity_before),
            'quantity_after_formatted' => Format::quantity($movement->quantity_after),
            'note' => $movement->note,
            'occurred_at' => $movement->occurred_at?->toISOString(),
            'occurred_formatted' => Format::dateTime($movement->occurred_at),
            'occurred_human' => $movement->occurred_at?->diffForHumans(),
            'is_in' => $type === StockMovementType::In,
        ];
    }

    public static function category(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
            'color_class' => $category->colorClasses(),
            'description' => $category->description,
            'products_count' => (int) ($category->products_count ?? 0),
            'all_products_count' => (int) ($category->all_products_count ?? 0),
            'quantity_total' => (float) ($category->quantity_total ?? 0),
            'quantity_total_formatted' => Format::quantity($category->quantity_total ?? 0),
        ];
    }

    public static function notification(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'تنبيه',
            'message' => $data['message'] ?? '',
            'tone' => $data['tone'] ?? 'warning',
            'icon' => $data['icon'] ?? 'bell',
            'sku' => $data['sku'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'alert_type' => $data['alert_type'] ?? null,
            'url' => route('notifications.open', $notification->id),
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'created_human' => $notification->created_at?->diffForHumans(),
            'created_formatted' => $notification->created_at?->translatedFormat('j M Y - H:i'),
        ];
    }
}
