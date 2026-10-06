<?php

namespace Tests\Feature\Http\Controllers\Orders;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ListTodayOrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.business_timezone' => 'America/Sao_Paulo']);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('orders.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_lists_orders_of_the_business_day_newest_first_with_summary(): void
    {
        $this->travelTo('2026-10-06 15:00:00');
        $operator = User::factory()->create(['name' => 'Operador Caixa 1']);
        $morningOrder = Order::factory()->for($operator)->create(['total_cents' => 1000, 'created_at' => '2026-10-06 13:00:00']);
        $noonOrder = Order::factory()->for($operator)->create([
            'total_cents' => 2500,
            'payment_method' => PaymentMethod::PIX,
            'created_at' => '2026-10-06 14:00:00',
        ]);
        OrderItem::factory()->count(2)->for($noonOrder)->create();
        Order::factory()->create(['total_cents' => 9999, 'created_at' => '2026-10-06 02:00:00']);

        $response = $this->actingAs($operator)->get(route('orders.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('orders/index')
            ->where('date', '2026-10-06')
            ->where('summary', ['orders_count' => 2, 'total_cents' => 3500])
            ->has('orders', 2)
            ->where('orders.0.uuid', $noonOrder->uuid)
            ->where('orders.0.payment_method.label', 'PIX')
            ->where('orders.0.operator', 'Operador Caixa 1')
            ->where('orders.0.items_count', 2)
            ->where('orders.1.uuid', $morningOrder->uuid));
    }

    public function test_includes_orders_from_earlier_utc_date_while_the_business_day_is_still_open(): void
    {
        $this->travelTo('2026-10-07 01:30:00');
        $earlyOrder = Order::factory()->create(['created_at' => '2026-10-06 12:00:00']);

        $response = $this->actingAs(User::factory()->create())->get(route('orders.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('date', '2026-10-06')
            ->has('orders', 1)
            ->where('orders.0.uuid', $earlyOrder->uuid));
    }
}
