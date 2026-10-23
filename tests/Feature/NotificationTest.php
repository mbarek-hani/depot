<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_their_own_notification(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $notification = $admin->notifications()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('products.show', $product));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $notification = $admin->notifications()->firstOrFail();

        $this->actingAs($other)
            ->get(route('notifications.open', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $admin->unreadNotifications()->count());

        $this->actingAs($admin)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $admin->unreadNotifications()->count());
    }

    public function test_opening_notification_for_archived_product_redirects_safely(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->outOfStock()->create(['expiry_date' => null]);

        app(AlertService::class)->evaluateProduct($product);
        $notification = $admin->notifications()->firstOrFail();
        $product->delete();

        $this->actingAs($admin)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('status');
    }
}
