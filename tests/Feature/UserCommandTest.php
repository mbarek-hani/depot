<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_create_command_creates_an_account(): void
    {
        $this->artisan('user:create', [
            '--name' => 'مدير النظام',
            '--password' => 'Password123',
        ])->assertSuccessful();

        $user = User::query()->where('name', 'مدير النظام')->firstOrFail();

        $this->assertTrue(Hash::check('Password123', $user->password));
        $this->assertSame('system', $user->theme);
    }

    public function test_user_create_command_rejects_duplicate_name(): void
    {
        User::factory()->create(['name' => 'مدير النظام']);

        $this->artisan('user:create', [
            '--name' => 'مدير النظام',
            '--password' => 'Password123',
        ])->assertFailed();
    }

    public function test_user_create_command_rejects_weak_password(): void
    {
        $this->artisan('user:create', [
            '--name' => 'مستخدم جديد',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['name' => 'مستخدم جديد']);
    }

    public function test_user_password_command_updates_the_password(): void
    {
        $user = User::factory()->create(['name' => 'سارة']);

        $this->artisan('user:password', [
            'name' => 'سارة',
            '--password' => 'NewPassword456',
        ])->assertSuccessful();

        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
    }

    public function test_user_password_command_fails_for_unknown_name(): void
    {
        $this->artisan('user:password', [
            'name' => 'غير موجود',
            '--password' => 'NewPassword456',
        ])->assertFailed();
    }
}
