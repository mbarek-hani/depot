<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_is_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_verified_admin_can_login(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'Password123']);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'Password123']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unverified_admin_is_redirected_to_verification_notice(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_email_verification_link_verifies_the_matching_user(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $admin->id,
            'hash' => sha1($admin->getEmailForVerification()),
        ], absolute: false);

        $this->actingAs($admin)
            ->get($url)
            ->assertRedirect(route('dashboard').'?verified=1');

        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_email_verification_link_for_another_user_is_rejected(): void
    {
        $admin = User::factory()->admin()->unverified()->create();
        $other = User::factory()->admin()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $other->id,
            'hash' => sha1($other->getEmailForVerification()),
        ], absolute: false);

        $this->actingAs($admin)
            ->get($url)
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors('email');

        $this->assertNull($admin->fresh()->email_verified_at);
        $this->assertNull($other->fresh()->email_verified_at);
    }

    public function test_email_verification_link_is_host_independent(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $path = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $admin->id,
            'hash' => sha1($admin->getEmailForVerification()),
        ], absolute: false);

        $this->actingAs($admin)
            ->get('http://127.0.0.1'.$path)
            ->assertRedirect(route('dashboard').'?verified=1');

        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_login_email_is_case_insensitive(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'Password123',
        ]);

        $this->post(route('login.store'), [
            'email' => 'ADMIN@EXAMPLE.COM',
            'password' => 'Password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
    }
}
