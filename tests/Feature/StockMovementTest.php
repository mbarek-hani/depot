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

    public function test_user_can_record_stock_in_and_out(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'low_stock_threshold' => 2]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'reason' => 'company',
                'quantity' => 5,
                'occurred_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(15.0, (float) $product->fresh()->quantity);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'quantity' => 4,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(11.0, (float) $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_all_reasons_are_valid_for_both_directions(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 50, 'low_stock_threshold' => 1]);

        foreach (['company', 'kitchen', 'administration', 'club', 'other'] as $reason) {
            $this->actingAs($user)
                ->post(route('movements.store'), [
                    'product_id' => $product->id,
                    'type' => 'in',
                    'reason' => $reason,
                    'quantity' => 1,
                ])
                ->assertSessionHasNoErrors();

            $this->actingAs($user)
                ->post(route('movements.store'), [
                    'product_id' => $product->id,
                    'type' => 'out',
                    'reason' => $reason,
                    'quantity' => 1,
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(50.0, (float) $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 10);
    }

    public function test_legacy_reason_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'reason' => 'purchase',
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertSame(10.0, (float) $product->fresh()->quantity);
    }

    public function test_stock_cannot_go_negative(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 3]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(3.0, (float) $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_alerts_are_deduplicated_and_resolved(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'quantity' => 5,
            'low_stock_threshold' => 10,
            'expiry_date' => null,
        ]);

        $alerts = app(AlertService::class);

        $this->assertSame(1, $alerts->evaluateProduct($product));
        $this->assertSame(0, $alerts->evaluateProduct($product));

        $unread = $user->notifications()
            ->where('data->alert_type', AlertType::LowStock->value)
            ->whereNull('read_at')
            ->count();

        $this->assertSame(1, $unread);

        $product->update(['quantity' => 50]);
        $alerts->evaluateProduct($product);

        $this->assertSame(0, $user->unreadNotifications()
            ->where('data->alert_type', AlertType::LowStock->value)
            ->count());
    }

    public function test_out_of_stock_alert_is_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()
            ->where('data->alert_type', AlertType::LowStock->value)
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('نفد المخزون', $notification->data['title']);
        $this->assertSame($product->id, $notification->data['product_id']);
    }

    public function test_expiry_alerts_are_sent_to_all_users(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $product = Product::factory()->expiringSoon()->create([
            'quantity' => 20,
            'low_stock_threshold' => 1,
        ]);

        app(AlertService::class)->evaluateProduct($product);

        foreach ([$first, $second] as $user) {
            $this->assertSame(1, $user->notifications()
                ->where('data->alert_type', AlertType::Expiry->value)
                ->count());
        }
    }

    public function test_archiving_product_resolves_open_alerts(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}
