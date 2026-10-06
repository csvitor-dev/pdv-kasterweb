<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepository as ProductRepositoryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryContract
{
    public function searchProducts(string $term, int $limit): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where(function (Builder $query) use ($term): void {
                $query->whereLike('code', "%{$term}%")
                    ->orWhereLike('name', "%{$term}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function findManyByUuidsForUpdate(array $uuids): Collection
    {
        return Product::query()
            ->whereIn('uuid', $uuids)
            ->lockForUpdate()
            ->get()
            ->keyBy('uuid');
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }
}
