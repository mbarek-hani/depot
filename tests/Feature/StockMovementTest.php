<?php

namespace Tests\Feature;

use App\Enums\AlertType;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_record_stock_in_and_out(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(10)->create(['quantity' => 10, 'low_stock_threshold' => 2]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
                'expiry_date' => now()->addDays(10)->toDateString(),
                'occurred_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(15.0, (float) $product->fresh()->quantity);

        $incoming = $product->movements()->latest('id')->firstOrFail();
        $this->assertNull($incoming->reason);
        $this->assertSame(now()->addDays(10)->toDateString(), $incoming->expiry_date->toDateString());
        $this->assertSame(5.0, (float) $incoming->quantity_remaining);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'quantity' => 4,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(11.0, (float) $product->fresh()->quantity);
        $this->assertSame(1.0, (float) $incoming->fresh()->quantity_remaining);
        $this->assertDatabaseCount('stock_movements', 3);
    }

    public function test_outgoing_movements_require_a_reason(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(10)->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertSame(10.0, (float) $product->fresh()->quantity);
    }

    public function test_incoming_movements_ignore_reason_and_outgoing_movements_reject_expiry(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(10)->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'reason' => 'company',
                'quantity' => 2,
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($product->movements()->latest('id')->firstOrFail()->reason);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'expiry_date' => now()->addDay()->toDateString(),
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('expiry_date');
    }

    public function test_all_reasons_are_valid_for_outgoing_movements(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(50)->create(['quantity' => 50, 'low_stock_threshold' => 1]);

        foreach (['company', 'kitchen', 'administration', 'club', 'other'] as $reason) {
            $this->actingAs($user)
                ->post(route('movements.store'), [
                    'product_id' => $product->id,
                    'type' => 'out',
                    'reason' => $reason,
                    'quantity' => 1,
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(45.0, (float) $product->fresh()->quantity);
        $this->assertSame(5, $product->movements()->where('type', StockMovementType::Out->value)->count());
    }

    public function test_outgoing_stock_consumes_batches_by_earliest_expiry(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity' => 13, 'low_stock_threshold' => 1]);

        $later = $this->batch($product, 6, now()->addDays(20), now()->subDays(2));
        $earlier = $this->batch($product, 4, now()->addDays(5), now()->subDay());
        $withoutExpiry = $this->batch($product, 3, null, now());

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'quantity' => 8,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(0.0, (float) $earlier->fresh()->quantity_remaining);
        $this->assertSame(2.0, (float) $later->fresh()->quantity_remaining);
        $this->assertSame(3.0, (float) $withoutExpiry->fresh()->quantity_remaining);
        $this->assertSame(5.0, (float) $product->fresh()->quantity);
    }

    public function test_outgoing_stock_rejects_untracked_batch_balance(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(10.0, (float) $product->fresh()->quantity);
    }

    public function test_legacy_reason_is_rejected(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(10)->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'purchase',
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertSame(10.0, (float) $product->fresh()->quantity);
    }

    public function test_product_creation_records_the_opening_batch_expiry(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'منتج بصلاحية',
                'category_id' => $category->id,
                'unit' => 'كيس',
                'quantity' => 5,
                'expiry_date' => now()->addDays(30)->toDateString(),
                'cost_price' => 10,
                'sale_price' => 15,
            ])
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('name', 'منتج بصلاحية')->firstOrFail();
        $batch = $product->movements()->firstOrFail();

        $this->assertSame(5.0, (float) $product->quantity);
        $this->assertSame(5.0, (float) $batch->quantity_remaining);
        $this->assertSame(now()->addDays(30)->toDateString(), $batch->expiry_date->toDateString());
        $this->assertNull($batch->reason);
    }

    public function test_new_product_without_initial_stock_does_not_trigger_out_of_stock_alert(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)
            ->post(route('products.store'), [
                'name' => 'منتج بدون رصيد',
                'category_id' => $category->id,
                'unit' => 'كيس',
                'cost_price' => 10,
                'sale_price' => 15,
            ])
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('name', 'منتج بدون رصيد')->firstOrFail();

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(0, $product->movements()->count());
        $this->assertSame(0, $user->notifications()->count());
    }

    public function test_stock_cannot_go_negative(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(3)->create(['quantity' => 3]);

        $this->actingAs($user)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'out',
                'reason' => 'kitchen',
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(3.0, (float) $product->fresh()->quantity);
        $this->assertSame(0, $product->movements()->where('type', StockMovementType::Out->value)->count());
    }

    public function test_alerts_are_deduplicated_and_resolved(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->withBatch(5)->create([
            'quantity' => 5,
            'low_stock_threshold' => 10,
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
        $product = Product::factory()->depleted()->create();

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
        $user = User::factory()->admin()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    protected function batch(Product $product, float $quantity, mixed $expiryDate, mixed $occurredAt): StockMovement
    {
        return StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => null,
            'type' => StockMovementType::In,
            'reason' => null,
            'expiry_date' => $expiryDate,
            'quantity' => $quantity,
            'quantity_remaining' => $quantity,
            'quantity_before' => 0,
            'quantity_after' => $quantity,
            'occurred_at' => $occurredAt,
        ]);
    }
}
