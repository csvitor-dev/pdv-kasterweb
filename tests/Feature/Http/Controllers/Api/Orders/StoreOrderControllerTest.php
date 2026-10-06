<?php

namespace Tests\Feature\Http\Controllers\Api\Orders;

use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class StoreOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $response = $this->postJson(route('api.orders.store'), []);

        $response->assertUnauthorized();
    }

    public function test_creates_order_with_totals_computed_by_the_server_and_returns_201(): void
    {
        $user = User::factory()->create();
        $rice = Product::factory()->create(['name' => 'Arroz', 'price_cents' => 2500, 'stock' => 10]);
        $beans = Product::factory()->create(['name' => 'Feijão', 'price_cents' => 800, 'stock' => 10]);

        $response = $this->actingAs($user)->postJson(route('api.orders.store'), [
            'items' => [
                ['product_uuid' => $rice->uuid, 'quantity' => 2, 'unit_price_cents' => 1],
                ['product_uuid' => $beans->uuid, 'quantity' => 3, 'subtotal_cents' => 1],
            ],
            'payment_method' => PaymentMethod::PIX->value,
            'total_cents' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_cents', 7400)
            ->assertJsonPath('data.payment_method.value', PaymentMethod::PIX->value)
            ->assertJsonPath('data.operator', $user->name)
            ->assertJsonPath('data.items.0.unit_price_cents', 2500)
            ->assertJsonPath('data.items.0.subtotal_cents', 5000)
            ->assertJsonPath('data.items.1.unit_price_cents', 800)
            ->assertJsonPath('data.items.1.subtotal_cents', 2400);

        $this->assertDatabaseHas('orders', [
            'uuid' => $response->json('data.uuid'),
            'user_id' => $user->id,
            'total_cents' => 7400,
            'amount_received_cents' => null,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $rice->id, 'product_name' => 'Arroz', 'unit_price_cents' => 2500, 'quantity' => 2]);
        $this->assertDatabaseHas('order_items', ['product_id' => $beans->id, 'product_name' => 'Feijão', 'unit_price_cents' => 800, 'quantity' => 3]);

        $this->assertSame(8, $rice->fresh()?->stock);
        $this->assertSame(7, $beans->fresh()?->stock);
    }

    #[TestWith([3000, 500], 'amount above total')]
    #[TestWith([2500, 0], 'exact amount')]
    public function test_cash_payment_returns_change(int $amountReceivedCents, int $expectedChangeCents): void
    {
        $product = Product::factory()->create(['price_cents' => 2500, 'stock' => 10]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [['product_uuid' => $product->uuid, 'quantity' => 1]],
            'payment_method' => PaymentMethod::CASH->value,
            'amount_received_cents' => $amountReceivedCents,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount_received_cents', $amountReceivedCents)
            ->assertJsonPath('data.change_cents', $expectedChangeCents);

        $this->assertDatabaseHas('orders', ['amount_received_cents' => $amountReceivedCents]);
    }

    public function test_returns_422_when_cash_received_is_below_total(): void
    {
        $product = Product::factory()->create(['price_cents' => 2500, 'stock' => 10]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [['product_uuid' => $product->uuid, 'quantity' => 1]],
            'payment_method' => PaymentMethod::CASH->value,
            'amount_received_cents' => 2499,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'amount_received_cents' => 'O valor recebido (R$ 24,99) é menor que o total da venda (R$ 25,00).',
            ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()?->stock);
    }

    public function test_returns_422_when_cash_payment_has_no_amount_received(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [['product_uuid' => $product->uuid, 'quantity' => 1]],
            'payment_method' => PaymentMethod::CASH->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount_received_cents' => 'O campo valor recebido é obrigatório.']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_ignores_amount_received_when_payment_is_not_cash(): void
    {
        $product = Product::factory()->create(['price_cents' => 2500, 'stock' => 10]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [['product_uuid' => $product->uuid, 'quantity' => 1]],
            'payment_method' => PaymentMethod::DEBIT_CARD->value,
            'amount_received_cents' => 10000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount_received_cents', null)
            ->assertJsonPath('data.change_cents', null);
    }

    public function test_returns_422_when_order_has_no_items(): void
    {
        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [],
            'payment_method' => PaymentMethod::PIX->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items' => 'O campo itens é obrigatório.']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_returns_422_when_product_is_inactive(): void
    {
        $available = Product::factory()->create(['stock' => 10]);
        $inactive = Product::factory()->inactive()->create(['name' => 'Iogurte', 'stock' => 10]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [
                ['product_uuid' => $available->uuid, 'quantity' => 1],
                ['product_uuid' => $inactive->uuid, 'quantity' => 1],
            ],
            'payment_method' => PaymentMethod::PIX->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.1.product_uuid' => 'O produto Iogurte não está mais disponível para venda.',
            ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(10, $available->fresh()?->stock);
    }

    public function test_returns_422_when_quantity_exceeds_stock(): void
    {
        $product = Product::factory()->create(['name' => 'Queijo', 'stock' => 2]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [['product_uuid' => $product->uuid, 'quantity' => 3]],
            'payment_method' => PaymentMethod::PIX->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.0.product_uuid' => 'Estoque insuficiente para Queijo: 2 disponível(is).',
            ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()?->stock);
    }

    public function test_returns_422_when_product_does_not_exist(): void
    {
        $response = $this->actingAs(User::factory()->create())->postJson(route('api.orders.store'), [
            'items' => [['product_uuid' => '01a11140-b4e8-72e9-822f-f787883331bc', 'quantity' => 1]],
            'payment_method' => PaymentMethod::PIX->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_uuid' => 'Produto não encontrado.']);

        $this->assertDatabaseCount('orders', 0);
    }
}
