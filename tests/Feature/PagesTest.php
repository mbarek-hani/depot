<?php

namespace Tests\Feature;

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

        $this->user = User::factory()->admin()->create();

        $category = Category::factory()->create(['name' => 'معلبات']);
        $this->product = Product::factory()
            ->withBatch(20, now()->addDays(10))
            ->create([
                'category_id' => $category->id,
                'name' => 'تونة معلبة',
                'quantity' => 20,
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

        $target = User::factory()->create(['name' => 'مستخدم للاختبار']);

        $this->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Users/Index'));

        $this->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Users/Create'));

        $this->get(route('users.edit', $target))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Users/Edit'));
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

    public function test_admin_can_manage_inventory(): void
    {
        $this->actingAs($this->user);

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
        $this->get(route('products.edit', $this->product))->assertOk();
    }

    public function test_products_index_and_expiry_report_use_batch_rows(): void
    {
        StockMovement::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => null,
            'type' => StockMovementType::In,
            'reason' => null,
            'expiry_date' => now()->addDays(4),
            'quantity' => 3,
            'quantity_remaining' => 3,
            'quantity_before' => 0,
            'quantity_after' => 3,
        ]);

        $this->product->update(['quantity' => 23]);

        $this->actingAs($this->user);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('products.data', 2));

        $this->get(route('products.show', $this->product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->has('batches', 2));

        $this->get(route('movements.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Movements/Index')
                ->has('products.0.active_batches', 2));

        $this->get(route('reports.index', ['type' => 'expiry']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Index')
                ->has('report.rows', 2));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('expiringBatches', 2));
    }

    public function test_products_index_filters_and_sorts_batch_rows(): void
    {
        StockMovement::factory()->create([
            'product_id' => $this->product->id,
            'user_id' => null,
            'type' => StockMovementType::In,
            'reason' => null,
            'expiry_date' => now()->addDays(4),
            'quantity' => 3,
            'quantity_remaining' => 3,
            'quantity_before' => 0,
            'quantity_after' => 3,
        ]);

        $this->product->update(['quantity' => 23]);
        $this->actingAs($this->user);

        $this->get(route('products.index', ['status' => 'expiring']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('products.data', 2));

        $this->get(route('products.index', ['status' => 'expired']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('products.data', 0));

        $this->get(route('products.index', ['search' => 'تونة']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('products.data', 2));

        $this->get(route('products.index', ['sort' => 'expiry_date|asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('products.data.0.expiry_date', now()->addDays(4)->toDateString()));
    }

    public function test_movements_index_sorts_by_selected_column(): void
    {
        $small = StockMovement::factory()->create([
            'product_id' => $this->product->id,
            'type' => StockMovementType::In,
            'quantity' => 0.5,
            'quantity_remaining' => 0.5,
            'occurred_at' => now()->subDays(2),
        ]);

        $large = StockMovement::factory()->create([
            'product_id' => $this->product->id,
            'type' => StockMovementType::In,
            'quantity' => 99,
            'quantity_remaining' => 99,
            'occurred_at' => now()->subDay(),
        ]);

        $this->actingAs($this->user);

        $this->get(route('movements.index', ['sort' => 'quantity|desc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'quantity|desc')
                ->where('movements.data.0.id', $large->id));

        $this->get(route('movements.index', ['sort' => 'quantity|asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'quantity|asc')
                ->where('movements.data.0.id', $small->id));
    }

    public function test_arabic_pagination_labels_are_available(): void
    {
        $this->assertStringContainsString('السابق', __('pagination.previous'));
        $this->assertStringContainsString('التالي', __('pagination.next'));
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
