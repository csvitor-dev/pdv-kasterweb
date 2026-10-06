<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepository
{
    /**
     * Sellable products whose code matches the term exactly or whose name contains it.
     *
     * @return Collection<int, Product>
     */
    public function searchProducts(string $term, int $limit): Collection;

    /**
     * Products locked for update until the surrounding transaction ends.
     *
     * @param  list<string>  $uuids
     * @return Collection<string, Product> keyed by uuid
     */
    public function findManyByUuidsForUpdate(array $uuids): Collection;

    public function decrementStock(Product $product, int $quantity): void;
}
