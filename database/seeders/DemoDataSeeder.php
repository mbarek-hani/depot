<?php

namespace Database\Seeders;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\AlertService;
use App\Support\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('settings')->count() === 0) {
            Settings::setMany(Settings::DEFAULTS);
        }

        $viewer = User::query()->firstOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'سارة العتيبي',
                'password' => 'password',
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
                'theme' => 'system',
                'receive_expiry_alerts' => true,
                'receive_low_stock_alerts' => true,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'viewer2@example.com'],
            [
                'name' => 'خالد الحربي',
                'password' => 'password',
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
                'theme' => 'light',
                'receive_expiry_alerts' => true,
                'receive_low_stock_alerts' => false,
            ],
        );

        $categories = [
            'معلبات' => ['brand', 'معلبات غذائية جاهزة للاستخدام.'],
            'أرز وحبوب' => ['emerald', 'الأرز والبقوليات والحبوب الأساسية.'],
            'زيوت وسمن' => ['orange', 'الزيوت النباتية والسمن.'],
            'مشروبات' => ['sky', 'المياه والعصائر والمشروبات الساخنة.'],
            'ألبان وأجبان' => ['violet', 'منتجات الحليب والأجبان.'],
            'حلويات ومكسرات' => ['rose', 'التمور والحلويات والمكسرات.'],
            'نظافة واستهلاكية' => ['maroon', 'مواد النظافة والاستهلاك اليومي.'],
        ];

        $categoryModels = [];

        foreach ($categories as $name => [$color, $description]) {
            $categoryModels[$name] = Category::query()->updateOrCreate(
                ['name' => $name],
                ['color' => $color, 'description' => $description],
            );
        }

        $products = [
            ['معلبات', 'تونة معلبة', 'كرتون', 48, 15, 180, 42, 60],
            ['معلبات', 'فول مدمس', 'كرتون', 6, 10, 240, 30, 42],
            ['معلبات', 'ذرة حلوة', 'علبة', 0, 12, 300, 6.5, 9],
            ['معلبات', 'صلصة طماطم', 'كرتون', 120, 20, 90, 25, 38],
            ['أرز وحبوب', 'أرز بسمتي', 'كيس', 75, 20, 365, 55, 78],
            ['أرز وحبوب', 'عدس أحمر', 'كيس', 9, 12, 200, 18, 28],
            ['أرز وحبوب', 'برغل', 'كيس', 35, null, 150, 8, 13],
            ['أرز وحبوب', 'دقيق', 'كيس', 0, 15, 120, 12, 18],
            ['زيوت وسمن', 'زيت زيتون', 'لتر', 22, 10, 150, 48, 70],
            ['زيوت وسمن', 'زيت ذرة', 'لتر', 8, 12, 210, 22, 32],
            ['زيوت وسمن', 'سمن نباتي', 'علبة', 55, null, 240, 15, 23],
            ['مشروبات', 'ماء معدني', 'كرتون', 140, 30, 330, 10, 16],
            ['مشروبات', 'عصير برتقال', 'علبة', 18, 15, 20, 4, 6],
            ['مشروبات', 'شاي', 'علبة', 64, 15, 420, 12, 19],
            ['مشروبات', 'قهوة', 'كيس', 5, 8, 180, 28, 40],
            ['ألبان وأجبان', 'حليب مجفف', 'علبة', 40, 10, 60, 32, 45],
            ['ألبان وأجبان', 'جبنة مثلثات', 'علبة', 12, 10, -10, 18, 27],
            ['حلويات ومكسرات', 'تمر', 'كيلو', 85, 15, 100, 20, 32],
            ['حلويات ومكسرات', 'بسكويت', 'كرتون', 7, 10, -25, 14, 22],
            ['حلويات ومكسرات', 'لوز', 'كيلو', 26, null, 240, 45, 68],
            ['نظافة واستهلاكية', 'صابون', 'كرتون', 90, 20, 500, 18, 27],
            ['نظافة واستهلاكية', 'مناديل', 'كرتون', 3, 10, 365, 22, 34],
            ['نظافة واستهلاكية', 'معقم', 'لتر', 0, 8, 400, 9, 15],
        ];

        foreach ($products as $index => [$categoryName, $name, $unit, $quantity, $threshold, $expiryInDays, $cost, $sale]) {
            $product = Product::query()->updateOrCreate(
                ['name' => $name, 'category_id' => $categoryModels[$categoryName]->id],
                [
                    'unit' => $unit,
                    'quantity' => 0,
                    'low_stock_threshold' => $threshold,
                    'expiry_date' => $expiryInDays === null ? null : today()->addDays($expiryInDays),
                    'cost_price' => $cost,
                    'sale_price' => $sale,
                    'notes' => null,
                ],
            );

            if ($product->movements()->exists()) {
                $product->update(['quantity' => $quantity]);

                continue;
            }

            $opening = $quantity + 20 + (($index % 4) * 5);
            $in = 10 + (($index % 5) * 3);
            $out = $opening + $in - $quantity;

            StockMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => $viewer->id,
                'type' => StockMovementType::In,
                'reason' => MovementReason::Opening,
                'quantity' => $opening,
                'quantity_before' => 0,
                'quantity_after' => $opening,
                'note' => 'الرصيد الافتتاحي عند إعداد النظام',
                'occurred_at' => Carbon::today()->subDays(60 - ($index % 10)),
            ]);

            StockMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => $viewer->id,
                'type' => StockMovementType::In,
                'reason' => $index % 2 === 0 ? MovementReason::Donation : MovementReason::Purchase,
                'quantity' => $in,
                'quantity_before' => $opening,
                'quantity_after' => $opening + $in,
                'note' => 'توريد دفعة جديدة للمستودع',
                'occurred_at' => Carbon::today()->subDays(14 - ($index % 5)),
            ]);

            StockMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => $viewer->id,
                'type' => StockMovementType::Out,
                'reason' => $index % 3 === 0 ? MovementReason::Damage : MovementReason::Distribution,
                'quantity' => $out,
                'quantity_before' => $opening + $in,
                'quantity_after' => $quantity,
                'note' => 'صرف للمستفيدين من الجمعية',
                'occurred_at' => Carbon::today()->subDays(3 - ($index % 3)),
            ]);

            $product->update(['quantity' => $quantity]);
        }

        app(AlertService::class)->checkAll();
    }
}
