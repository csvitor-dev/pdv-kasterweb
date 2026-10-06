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
            'payment_method' => $this->payment_method->toOption(),
            'total_cents' => $this->total_cents,
            'amount_received_cents' => $this->amount_received_cents,
            'change_cents' => $this->change_cents,
            'operator' => $this->whenLoaded('user', fn (): string => $this->user->name),
            'items' => $this->whenLoaded(
                'items',
                fn (): array => OrderItemResource::collection($this->items)->resolve($request),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
