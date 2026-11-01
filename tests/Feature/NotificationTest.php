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
        $user = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('products.show', $product));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($other)
            ->get(route('notifications.open', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_opening_notification_for_archived_product_redirects_safely(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();

        app(AlertService::class)->evaluateProduct($product);
        $notification = $user->notifications()->firstOrFail();
        $product->delete();

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('status');
    }
}
