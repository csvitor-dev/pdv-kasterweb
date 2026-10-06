<?php

namespace Tests\Feature\Http\Controllers\Api\Orders;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ShowOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson(route('api.orders.show', $order->uuid));

        $response->assertUnauthorized();
    }

    public function test_returns_receipt_with_the_price_recorded_at_the_time_of_sale(): void
    {
        $operator = User::factory()->create(['name' => 'Operador Caixa 1']);
        $product = Product::factory()->create(['name' => 'Café 500g', 'price_cents' => 1890]);
        $order = Order::factory()->for($operator)->paidInCash(5000)->create(['total_cents' => 3780]);
        OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);
        $product->update(['price_cents' => 2500, 'name' => 'Café Premium 500g']);

        $response = $this->actingAs(User::factory()->create())->getJson(route('api.orders.show', $order->uuid));

        $response->assertOk()
            ->assertJsonPath('data.uuid', $order->uuid)
            ->assertJsonPath('data.payment_method.value', PaymentMethod::CASH->value)
            ->assertJsonPath('data.payment_method.label', 'Dinheiro')
            ->assertJsonPath('data.total_cents', 3780)
            ->assertJsonPath('data.amount_received_cents', 5000)
            ->assertJsonPath('data.change_cents', 1220)
            ->assertJsonPath('data.operator', 'Operador Caixa 1')
            ->assertJsonPath('data.items.0.product_name', 'Café 500g')
            ->assertJsonPath('data.items.0.unit_price_cents', 1890)
            ->assertJsonPath('data.items.0.subtotal_cents', 3780);
    }

    public function test_returns_404_when_order_does_not_exist(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson(route('api.orders.show', '01a11140-b4e8-72e9-822f-f787883331bc'));

        $response->assertNotFound();
    }

    #[TestWith(['put'])]
    #[TestWith(['patch'])]
    #[TestWith(['delete'])]
    public function test_returns_405_when_trying_to_modify_a_finalized_order(string $method): void
    {
        $order = Order::factory()->create(['total_cents' => 1000]);

        $response = $this->actingAs(User::factory()->create())
            ->json($method, route('api.orders.show', $order->uuid), ['total_cents' => 1]);

        $response->assertMethodNotAllowed();

        $this->assertSame(1000, $order->fresh()?->total_cents);
    }
}
