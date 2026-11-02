<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_cannot_be_deleted_until_all_products_are_deleted(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $this->actingAs($user)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
