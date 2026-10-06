<?php

namespace Tests\Feature\Http\Controllers\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowOrderReceiptControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('orders.show', $order->uuid));

        $response->assertRedirect(route('login'));
    }

    public function test_renders_receipt_with_the_order_resource(): void
    {
        $operator = User::factory()->create(['name' => 'Operador Caixa 1']);
        $product = Product::factory()->create(['name' => 'Café 500g', 'price_cents' => 1890]);
        $order = Order::factory()->for($operator)->paidInCash(5000)->create(['total_cents' => 3780]);
        OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);

        $response = $this->actingAs(User::factory()->create())->get(route('orders.show', $order->uuid));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.uuid', $order->uuid)
            ->where('order.payment_method', ['value' => 0, 'label' => 'Dinheiro', 'requires_amount_received' => true])
            ->where('order.total_cents', 3780)
            ->where('order.change_cents', 1220)
            ->where('order.operator', 'Operador Caixa 1')
            ->where('order.items.0.product_name', 'Café 500g')
            ->where('order.items.0.subtotal_cents', 3780));
    }

    public function test_returns_404_when_order_does_not_exist(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('orders.show', '01a11140-b4e8-72e9-822f-f787883331bc'));

        $response->assertNotFound();
    }
}
