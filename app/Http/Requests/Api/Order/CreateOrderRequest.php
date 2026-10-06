<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Order;

use App\Enums\PaymentMethod;
use App\Models\User;
use App\Services\Order\Inputs\CreateOrderInput;
use App\Services\Order\Inputs\OrderItemInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates only the shape of the sale; business rules that depend on the
 * computed total (cash received, product availability) live in the service.
 */
class CreateOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_uuid' => ['required', 'uuid', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount_received_cents' => [
                Rule::requiredIf(fn (): bool => $this->isCashPayment()),
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function toInput(): CreateOrderInput
    {
        /** @var User $user */
        $user = $this->user();

        /** @var list<array{product_uuid: string, quantity: int|string}> $items */
        $items = $this->validated('items');

        return new CreateOrderInput(
            userId: $user->id,
            items: array_map(
                fn (array $item): OrderItemInput => new OrderItemInput($item['product_uuid'], (int) $item['quantity']),
                $items,
            ),
            paymentMethod: PaymentMethod::from($this->integer('payment_method')),
            amountReceivedCents: $this->isCashPayment() ? $this->integer('amount_received_cents') : null,
        );
    }

    private function isCashPayment(): bool
    {
        return $this->enum('payment_method', PaymentMethod::class) === PaymentMethod::CASH;
    }
}
