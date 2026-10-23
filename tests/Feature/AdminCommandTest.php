<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_command_creates_an_unverified_admin(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'مدير النظام',
            '--email' => 'admin@example.com',
            '--password' => 'Password123',
        ])->assertSuccessful();

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertNull($admin->email_verified_at);
    }

    public function test_admin_create_command_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('admin:create', [
            '--name' => 'مدير النظام',
            '--email' => 'admin@example.com',
            '--password' => 'Password123',
        ])->assertFailed();
    }
}
