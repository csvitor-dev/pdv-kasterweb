<?php

namespace Tests\Feature\Http\Controllers\PointOfSale;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowPointOfSaleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }

    public function test_renders_point_of_sale_with_payment_methods_from_the_server(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('pos/index')
            ->where('paymentMethods', [
                ['value' => 0, 'label' => 'Dinheiro', 'requires_amount_received' => true],
                ['value' => 1, 'label' => 'Cartão de crédito', 'requires_amount_received' => false],
                ['value' => 2, 'label' => 'Cartão de débito', 'requires_amount_received' => false],
                ['value' => 3, 'label' => 'PIX', 'requires_amount_received' => false],
            ]));
    }
}
