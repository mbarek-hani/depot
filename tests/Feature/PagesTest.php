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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::factory()->create(['name' => 'معلبات']);
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'تونة معلبة',
            'quantity' => 20,
        ]);

        StockMovement::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'type' => StockMovementType::In,
            'reason' => MovementReason::Company,
            'quantity' => 20,
            'quantity_before' => 0,
            'quantity_after' => 20,
        ]);
    }

    public function test_app_pages_are_rendered(): void
    {
        $this->actingAs($this->user);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('app.logo', asset('images/logo.jpeg')));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Products/Index'));

        $this->get(route('products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Products/Create'));

        $this->get(route('products.show', $this->product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Products/Show'));

        $this->get(route('products.edit', $this->product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Products/Edit'));

        $this->get(route('movements.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Movements/Index'));

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Reports/Index'));

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Notifications/Index'));

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Categories/Index'));

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Settings/Index'));
    }

    public function test_all_report_types_render(): void
    {
        $this->actingAs($this->user);

        foreach (array_keys(ReportService::types()) as $type) {
            $this->get(route('reports.index', ['type' => $type]))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component('Reports/Index'));
        }
    }

    public function test_dashboard_chart_supports_period_filters(): void
    {
        $this->actingAs($this->user);

        $this->get(route('dashboard', ['period' => '7d']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('period', '7d')
                ->has('chart.labels', 7)
                ->has('periods', 4));

        $this->get(route('dashboard', ['period' => '30d']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('period', '30d')
                ->has('chart.labels', 30));

        $this->get(route('dashboard', ['period' => 'week']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('period', 'week'));

        $this->get(route('dashboard', ['period' => 'month']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('period', 'month'));

        $this->get(route('dashboard', ['period' => 'invalid']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('period', '7d')
                ->has('chart.labels', 7));
    }

    public function test_every_authenticated_user_can_manage_inventory(): void
    {
        $this->actingAs($this->user);

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
        $this->get(route('products.edit', $this->product))->assertOk();
    }

    public function test_pdf_report_is_downloaded(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.download', ['type' => 'stock']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'attachment; filename="stock-report-',
            (string) $response->headers->get('Content-Disposition'),
        );
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
