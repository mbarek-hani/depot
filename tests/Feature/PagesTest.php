<?php

namespace Tests\Feature;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $category = Category::factory()->create(['name' => 'معلبات']);
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'تونة معلبة',
            'quantity' => 20,
        ]);

        StockMovement::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->admin->id,
            'type' => StockMovementType::In,
            'reason' => MovementReason::Opening,
            'quantity' => 20,
            'quantity_before' => 0,
            'quantity_after' => 20,
        ]);
    }

    public function test_admin_pages_are_rendered(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
        $this->get(route('products.show', $this->product))->assertOk();
        $this->get(route('products.edit', $this->product))->assertOk();
        $this->get(route('movements.index'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('users.index'))->assertOk();
        $this->get(route('users.create'))->assertOk();
        $this->get(route('users.edit', $this->admin))->assertOk();
        $this->get(route('categories.index'))->assertOk();
        $this->get(route('settings.index'))->assertOk();
        $this->get(route('settings.index', ['tab' => 'appearance']))->assertOk();
        $this->get(route('settings.index', ['tab' => 'account']))->assertOk();
    }

    public function test_all_report_types_render(): void
    {
        $this->actingAs($this->admin);

        foreach (array_keys(ReportService::types()) as $type) {
            $this->get(route('reports.index', ['type' => $type]))->assertOk();
        }
    }

    public function test_regular_user_can_view_read_only_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.show', $this->product))->assertOk();
        $this->get(route('movements.index'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('settings.index'))->assertOk();
    }

    public function test_regular_user_cannot_access_admin_pages_or_actions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('products.edit', $this->product))->assertForbidden();
        $this->get(route('settings.index', ['tab' => 'general']))->assertForbidden();
        $this->post(route('movements.store'), [])->assertForbidden();
    }

    public function test_pdf_report_is_downloaded(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.download', ['type' => 'stock']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
