<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_is_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_user_can_login_with_name_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'سارة العتيبي',
            'password' => 'Password123',
        ]);

        $this->post(route('login.store'), [
            'name' => 'سارة العتيبي',
            'password' => 'Password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'name' => $user->name,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('name');

        $this->assertGuest();
    }

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
