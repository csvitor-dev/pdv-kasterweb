<?php

namespace Tests\Feature\Http\Controllers\Api\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchProductsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson(route('api.products.search', ['search' => 'arroz']));

        $response->assertUnauthorized();
    }

    public function test_returns_422_when_search_term_is_missing(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->getJson(route('api.products.search'));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['search' => 'O campo busca é obrigatório.']);
    }

    public function test_returns_product_whose_code_matches_exactly(): void
    {
        $this->actingAs(User::factory()->create());
        Product::factory()->create(['code' => '7891000100103', 'name' => 'Leite Integral', 'price_cents' => 549, 'stock' => 10]);

        $response = $this->getJson(route('api.products.search', ['search' => '7891000100103']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '7891000100103')
            ->assertJsonPath('data.0.name', 'Leite Integral')
            ->assertJsonPath('data.0.price_cents', 549);
    }

    public function test_does_not_match_partial_code(): void
    {
        $this->actingAs(User::factory()->create());
        Product::factory()->create(['code' => '7891000100103', 'name' => 'Leite Integral']);

        $response = $this->getJson(route('api.products.search', ['search' => '78910']));

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_returns_all_products_whose_name_contains_the_term(): void
    {
        $this->actingAs(User::factory()->create());
        Product::factory()->create(['name' => 'Arroz Branco 5kg']);
        Product::factory()->create(['name' => 'Arroz Integral 1kg']);
        Product::factory()->create(['name' => 'Feijão Carioca 1kg']);

        $response = $this->getJson(route('api.products.search', ['search' => 'arroz']));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Arroz Branco 5kg')
            ->assertJsonPath('data.1.name', 'Arroz Integral 1kg');
    }

    public function test_does_not_return_products_that_are_unavailable(): void
    {
        $this->actingAs(User::factory()->create());
        Product::factory()->inactive()->create(['name' => 'Arroz fora de linha']);
        Product::factory()->outOfStock()->create(['name' => 'Arroz esgotado']);
        Product::factory()->create(['name' => 'Arroz disponível']);

        $response = $this->getJson(route('api.products.search', ['search' => 'arroz']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Arroz disponível');
    }
}
