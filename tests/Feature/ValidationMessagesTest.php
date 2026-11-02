<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_date_range_validation_messages_are_entirely_arabic(): void
    {
        $user = User::factory()->admin()->create();
        $expected = 'يجب أن يكون تاريخ النهاية بعد أو يساوي تاريخ البداية.';

        $this->actingAs($user)
            ->get(route('reports.index', [
                'type' => 'movements',
                'from' => '2026-11-10',
                'to' => '2026-11-01',
            ]))
            ->assertSessionHasErrors('to');

        $this->assertSame($expected, session('errors')->first('to'));

        $this->get(route('movements.index', [
            'from' => '2026-11-10',
            'to' => '2026-11-01',
        ]))->assertSessionHasErrors('to');

        $this->assertSame($expected, session('errors')->first('to'));
    }

    public function test_form_validation_messages_are_entirely_arabic(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $requests = [
            ['post', route('products.store'), []],
            ['post', route('categories.store'), []],
            ['post', route('movements.store'), []],
            ['post', route('users.store'), []],
            ['patch', route('settings.general'), []],
        ];

        foreach ($requests as [$method, $url, $payload]) {
            $this->{$method}($url, $payload)->assertSessionHasErrors();

            foreach (session('errors')->all() as $message) {
                $this->assertMatchesRegularExpression('/\p{Arabic}/u', $message);
                $this->assertDoesNotMatchRegularExpression('/[A-Za-z]/u', $message);
            }
        }
    }
}
