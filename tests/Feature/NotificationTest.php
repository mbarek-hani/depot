<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_their_own_notification(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('products.show', $product));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_opening_notification_uses_current_product_route_instead_of_stored_url(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()->firstOrFail();
        $notification->update([
            'data' => array_merge($notification->data, [
                'url' => 'http://stale-host.test/products/'.$product->id,
            ]),
        ]);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('products.show', $product));
    }

    public function test_unread_count_endpoint_returns_latest_notifications(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('unread', 1)
            ->assertJsonPath('latest.0.id', $notification->id);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($other)
            ->get(route('notifications.open', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_blocked_users_do_not_receive_alerts(): void
    {
        $active = User::factory()->create();
        $blocked = User::factory()->blocked()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $active->notifications()->count());
        $this->assertSame(0, $blocked->notifications()->count());
    }

    public function test_notification_dropdown_only_shares_unread_notifications(): void
    {
        $user = User::factory()->create();
        $readProduct = Product::factory()->depleted()->create();
        $unreadProduct = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($readProduct);
        $readNotification = $user->notifications()->firstOrFail();
        $readNotification->markAsRead();

        app(AlertService::class)->evaluateProduct($unreadProduct);
        $unreadNotification = $user->unreadNotifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('notifications.unread', 1)
                ->has('notifications.latest', 1)
                ->where('notifications.latest.0.id', $unreadNotification->id));
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);

        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_opening_notification_for_deleted_product_redirects_safely(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);
        $notification = $user->notifications()->firstOrFail();
        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('status');
    }

    public function test_user_can_delete_their_own_read_notification(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);
        $notification = $user->notifications()->firstOrFail();
        $notification->markAsRead();

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $notification))
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_unread_notification_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $notification))
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_user_cannot_delete_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $product = Product::factory()->depleted()->create();

        app(AlertService::class)->evaluateProduct($product);
        $notification = $user->notifications()->firstOrFail();
        $notification->markAsRead();

        $this->actingAs($other)
            ->delete(route('notifications.destroy', $notification))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }
}
