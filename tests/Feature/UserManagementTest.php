<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_verified_regular_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'مستخدم جديد',
                'email' => 'new-user@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'receive_expiry_alerts' => '1',
                'receive_low_stock_alerts' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAdmin());
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->receive_expiry_alerts);
        $this->assertSame($admin->id, $user->created_by);
    }

    public function test_regular_user_cannot_create_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.store'), [
                'name' => 'مستخدم جديد',
                'email' => 'new-user@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_an_admin_account(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $otherAdmin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_admin_can_delete_a_regular_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
