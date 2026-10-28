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
        $product->loadMissing('category');

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'quantity' => (float) $product->quantity,
            'quantity_formatted' => Format::quantity($product->quantity),
            'low_stock_threshold' => $product->low_stock_threshold !== null ? (float) $product->low_stock_threshold : null,
            'threshold_formatted' => Format::quantity($product->lowStockThresholdValue()),
            'expiry_date' => $product->expiry_date?->format('Y-m-d'),
            'expiry_date_formatted' => Format::date($product->expiry_date),
            'expiry_long' => $product->expiry_date ? Format::longDate($product->expiry_date) : null,
            'expiry_label' => $product->expiryStatus()->label(),
            'expiry_tone' => $product->expiryStatus()->tone(),
            'expiry_status' => $product->expiryStatus()->value,
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
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'quantity' => (float) $product->quantity,
            'quantity_formatted' => Format::quantity($product->quantity),
        ];
    }

    public static function movement(StockMovement $movement, bool $withProduct = true): array
    {
        $product = $movement->product;
        $type = $movement->type;

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
            'reason' => $movement->reason->value,
            'reason_label' => $movement->reason->label(),
            'quantity' => (float) $movement->quantity,
            'quantity_formatted' => Format::quantity($movement->quantity),
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
