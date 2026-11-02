<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_dashboard_action_on_404_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/missing-page')
            ->assertNotFound()
            ->assertSee('العودة للرئيسية')
            ->assertDontSee('تسجيل الدخول');
    }

    public function test_authenticated_user_sees_dashboard_action_on_403_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden()
            ->assertSee('العودة للوحة التحكم')
            ->assertDontSee('تسجيل الدخول');
    }

    public function test_unread_count_redirects_browser_navigation_to_notifications_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('notifications.unread-count'))
            ->assertRedirect(route('notifications.index'));

        $this->actingAs($user)
            ->get(route('notifications.unread-count'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson(['unread' => 0]);
    }
}
