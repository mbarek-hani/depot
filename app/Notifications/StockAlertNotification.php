<?php

namespace App\Notifications;

use App\Enums\AlertType;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public AlertType $alertType,
        public string $title,
        public string $message,
        public string $tone = 'warning',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'alert_type' => $this->alertType->value,
            'title' => $this->title,
            'message' => $this->message,
            'tone' => $this->tone,
            'quantity' => (float) $this->product->quantity,
            'expiry_date' => $this->product->nearestExpiryDate()?->toDateString(),
            'icon' => $this->alertType === AlertType::Expiry ? 'calendar' : 'archive',
        ];
    }
}
