<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_can_read_inventory_and_reports(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->withBatch(5)->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('products.index'))->assertOk();
        $this->actingAs($user)->get(route('products.show', $product))->assertOk();
        $this->actingAs($user)->get(route('movements.index'))->assertOk();
        $this->actingAs($user)->get(route('categories.index'))->assertOk();
        $this->actingAs($user)->get(route('reports.index'))->assertOk();
        $this->actingAs($user)->get(route('reports.download', ['type' => 'stock']))->assertOk();
        $this->actingAs($user)->get(route('notifications.index'))->assertOk();
    }

    public function test_regular_user_cannot_change_inventory(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->withBatch(5)->create();
        $category = Category::factory()->create();

        $this->actingAs($user)->get(route('products.create'))->assertForbidden();
        $this->actingAs($user)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($user)->post(route('products.store'), [])->assertForbidden();
        $this->actingAs($user)->patch(route('products.update', $product), [])->assertForbidden();
        $this->actingAs($user)->delete(route('products.destroy', $product))->assertForbidden();
        $this->actingAs($user)->post(route('movements.store'), [])->assertForbidden();
        $this->actingAs($user)->post(route('categories.store'), [])->assertForbidden();
        $this->actingAs($user)->patch(route('categories.update', $category), [])->assertForbidden();
        $this->actingAs($user)->delete(route('categories.destroy', $category))->assertForbidden();
    }

    public function test_regular_user_can_only_access_account_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.index', ['tab' => 'general']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('tab', 'account'));

        $this->actingAs($user)
            ->patch(route('settings.general'), [])
            ->assertForbidden();
    }

    public function test_regular_user_can_update_their_own_account_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'سارة',
            'password' => 'Password123',
        ]);

        $this->actingAs($user)
            ->patch(route('settings.account'), ['name' => 'سارة العتيبي'])
            ->assertRedirect(route('settings.index', ['tab' => 'account']));

        $this->actingAs($user)
            ->patch(route('settings.password'), [
                'current_password' => 'Password123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'account']));

        $user->refresh();

        $this->assertSame('سارة العتيبي', $user->name);
        $this->assertTrue(Hash::check('NewPassword456', $user->password));
    }

    public function test_admin_can_change_inventory_and_general_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->withBatch(5)->create();

        $this->actingAs($admin)->get(route('products.create'))->assertOk();
        $this->actingAs($admin)->get(route('products.edit', $product))->assertOk();
        $this->actingAs($admin)->get(route('users.index'))->assertOk();

        $this->actingAs($admin)
            ->get(route('settings.index', ['tab' => 'general']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('tab', 'general'));
    }
}
