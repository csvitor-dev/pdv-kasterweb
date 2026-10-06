<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'payment_method' => [
                'value' => $this->payment_method->value,
                'label' => $this->payment_method->label(),
            ],
            'total_cents' => $this->total_cents,
            'amount_received_cents' => $this->amount_received_cents,
            'change_cents' => $this->change_cents,
            'operator' => $this->whenLoaded('user', fn (): string => $this->user->name),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
