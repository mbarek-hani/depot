<?php

namespace Tests\Feature;

use App\Enums\AlertType;
use App\Models\Product;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_stock_in_and_out(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity' => 10, 'low_stock_threshold' => 2]);

        $this->actingAs($admin)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'reason' => 'purchase',
                'quantity' => 5,
                'occurred_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(15.0, (float) $product->fresh()->quantity);

        $this->actingAs($admin)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'distribution',
                'quantity' => 4,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(11.0, (float) $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_stock_cannot_go_negative(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity' => 3]);

        $this->actingAs($admin)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'distribution',
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(3.0, (float) $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_movement_reason_must_match_type(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($admin)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'reason' => 'distribution',
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertSame(10.0, (float) $product->fresh()->quantity);
    }

    public function test_alerts_are_deduplicated_and_resolved(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'quantity' => 5,
            'low_stock_threshold' => 10,
            'expiry_date' => null,
        ]);

        $alerts = app(AlertService::class);

        $this->assertSame(1, $alerts->evaluateProduct($product));
        $this->assertSame(0, $alerts->evaluateProduct($product));

        $unread = $admin->notifications()
            ->where('data->alert_type', AlertType::LowStock->value)
            ->whereNull('read_at')
            ->count();

        $this->assertSame(1, $unread);

        $product->update(['quantity' => 50]);
        $alerts->evaluateProduct($product);

        $this->assertSame(0, $admin->unreadNotifications()
            ->where('data->alert_type', AlertType::LowStock->value)
            ->count());
    }

    public function test_out_of_stock_alert_is_created(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $notification = $admin->notifications()
            ->where('data->alert_type', AlertType::LowStock->value)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('نفد المخزون', $notification->data['title']);
        $this->assertSame($product->id, $notification->data['product_id']);
    }

    public function test_expiry_alert_is_created_only_for_opted_in_users(): void
    {
        User::factory()->admin()->create();
        $optedIn = User::factory()->create(['receive_expiry_alerts' => true]);
        $optedOut = User::factory()->create(['receive_expiry_alerts' => false]);

        $product = Product::factory()->expiringSoon()->create([
            'quantity' => 20,
            'low_stock_threshold' => 1,
        ]);

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $optedIn->notifications()
            ->where('data->alert_type', AlertType::Expiry->value)
            ->count());

        $this->assertSame(0, $optedOut->notifications()
            ->where('data->alert_type', AlertType::Expiry->value)
            ->count());
    }

    public function test_archiving_product_resolves_open_alerts(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $admin->unreadNotifications()->count());

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSame(0, $admin->unreadNotifications()->count());
    }
}
