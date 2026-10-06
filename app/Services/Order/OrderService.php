<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Enums\PaymentMethod;
use App\Exceptions\Domain\Order\InsufficientPaymentException;
use App\Exceptions\Domain\Product\ProductUnavailableException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\Contracts\OrderRepository;
use App\Repositories\Contracts\ProductRepository;
use App\Services\Order\Contracts\OrderService as OrderServiceContract;
use App\Services\Order\Inputs\CreateOrderInput;
use App\Services\Order\Inputs\OrderItemInput;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceContract
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductRepository $productRepository,
    ) {}

    public function create(CreateOrderInput $input): Order
    {
        return DB::transaction(function () use ($input): Order {
            $products = $this->productRepository->findManyByUuidsForUpdate(
                array_map(fn (OrderItemInput $item): string => $item->productUuid, $input->items),
            );

            $items = $this->buildItems($input->items, $products);
            $totalCents = array_sum(array_map(fn (OrderItem $item): int => $item->subtotal_cents, $items));

            $this->ensurePaymentCoversTotal($input, $totalCents);

            $order = new Order([
                'payment_method' => $input->paymentMethod,
                'amount_received_cents' => $input->amountReceivedCents,
            ]);
            $order->user_id = $input->userId;
            $order->total_cents = $totalCents;

            $createdOrder = $this->orderRepository->create($order, $items);

            foreach ($input->items as $item) {
                $this->productRepository->decrementStock($products[$item->productUuid], $item->quantity);
            }

            return $createdOrder;
        });
    }

    public function findByUuidOrFail(string $uuid): Order
    {
        return $this->orderRepository->findByUuid($uuid)
            ?? throw (new ModelNotFoundException)->setModel(Order::class, [$uuid]);
    }

    /**
     * Snapshot name and price of each product so later catalog changes do not affect the sale.
     *
     * @param  list<OrderItemInput>  $itemInputs
     * @param  Collection<string, Product>  $products
     * @return list<OrderItem>
     *
     * @throws ProductUnavailableException
     */
    private function buildItems(array $itemInputs, Collection $products): array
    {
        $items = [];

        foreach ($itemInputs as $index => $itemInput) {
            $product = $products->get($itemInput->productUuid)
                ?? throw ProductUnavailableException::notFound($index);

            if ($product->is_active === false) {
                throw ProductUnavailableException::inactive($index, $product->name);
            }

            if ($product->stock < $itemInput->quantity) {
                throw ProductUnavailableException::insufficientStock($index, $product->name, $product->stock);
            }

            $items[] = (new OrderItem)->forceFill([
                'quantity' => $itemInput->quantity,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_cents' => $product->price_cents,
            ]);
        }

        return $items;
    }

    /**
     * @throws InsufficientPaymentException
     */
    private function ensurePaymentCoversTotal(CreateOrderInput $input, int $totalCents): void
    {
        if ($input->paymentMethod !== PaymentMethod::CASH) {
            return;
        }

        $amountReceivedCents = $input->amountReceivedCents ?? 0;

        if ($amountReceivedCents < $totalCents) {
            throw InsufficientPaymentException::forTotal($totalCents, $amountReceivedCents);
        }
    }
}
