<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_command_creates_an_active_admin(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'مدير النظام',
            '--password' => 'Password123',
        ])->assertSuccessful();

        $admin = User::query()->where('name', 'مدير النظام')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertTrue(Hash::check('Password123', $admin->password));
    }

    public function test_admin_create_command_rejects_duplicate_name(): void
    {
        User::factory()->admin()->create(['name' => 'مدير النظام']);

        $this->artisan('admin:create', [
            '--name' => 'مدير النظام',
            '--password' => 'Password123',
        ])->assertFailed();
    }

    public function test_admin_create_command_rejects_weak_password(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'مدير جديد',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['name' => 'مدير جديد']);
    }

    public function test_admin_password_command_updates_an_admin_password(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'مدير النظام']);

        $this->artisan('admin:password', [
            'name' => 'مدير النظام',
            '--password' => 'NewPassword456',
        ])->assertSuccessful();

        $this->assertTrue(Hash::check('NewPassword456', $admin->fresh()->password));
    }

    public function test_admin_password_command_rejects_a_regular_user(): void
    {
        $user = User::factory()->create(['name' => 'سارة']);

        $this->artisan('admin:password', [
            'name' => 'سارة',
            '--password' => 'NewPassword456',
        ])->assertFailed();

        $this->assertFalse(Hash::check('NewPassword456', $user->fresh()->password));
    }

    public function test_admin_password_command_fails_for_unknown_name(): void
    {
        $this->artisan('admin:password', [
            'name' => 'غير موجود',
            '--password' => 'NewPassword456',
        ])->assertFailed();
    }
}
