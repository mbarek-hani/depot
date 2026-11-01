<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_user_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('users.create'))->assertForbidden();
        $this->actingAs($user)->post(route('users.store'))->assertForbidden();
        $this->actingAs($user)->get(route('users.edit', $user))->assertForbidden();
    }

    public function test_admin_can_view_and_filter_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'سارة العتيبي']);
        User::factory()->blocked()->create(['name' => 'خالد الحربي']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('users.data', 3)
                ->where('summary.total', 3)
                ->where('summary.admins', 1)
                ->where('summary.blocked', 1));

        $this->actingAs($admin)
            ->get(route('users.index', ['role' => 'user', 'status' => 'blocked']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'خالد الحربي'));
    }

    public function test_admin_can_create_an_active_regular_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'مستخدم جديد',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ])
            ->assertRedirect(route('users.index'));

        $user = User::query()->where('name', 'مستخدم جديد')->firstOrFail();

        $this->assertSame(UserRole::User, $user->role);
        $this->assertTrue($user->isActive());
        $this->assertSame($admin->id, $user->created_by);
        $this->assertTrue(Hash::check('Password123', $user->password));
    }

    public function test_user_creation_validates_unique_name_and_password(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'مستخدم موجود']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'مستخدم موجود',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors(['name', 'password']);
    }

    public function test_admin_can_update_a_regular_user_password_and_block_access(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'سارة']);

        DB::table('sessions')->insert([
            'id' => 'session-user',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->patch(route('users.update', $user), [
                'name' => 'سارة العتيبي',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
                'is_active' => false,
            ])
            ->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('سارة العتيبي', $user->name);
        $this->assertFalse($user->isActive());
        $this->assertTrue(Hash::check('NewPassword456', $user->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'session-user']);
    }

    public function test_admin_can_reactivate_a_user_without_changing_the_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->blocked()->create(['password' => 'Password123']);

        $this->actingAs($admin)
            ->patch(route('users.update', $user), [
                'name' => $user->name,
                'is_active' => true,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue($user->fresh()->isActive());
        $this->assertTrue(Hash::check('Password123', $user->fresh()->password));
    }

    public function test_admin_accounts_cannot_be_managed_from_the_ui(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('users.edit', $otherAdmin))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('users.update', $otherAdmin), [
                'name' => $otherAdmin->name,
                'is_active' => false,
            ])
            ->assertForbidden();
    }
}
