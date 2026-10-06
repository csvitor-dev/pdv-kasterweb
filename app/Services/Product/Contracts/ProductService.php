<?php

declare(strict_types=1);

namespace App\Services\Product\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductService
{
    /**
     * Sellable products matching the term; an empty collection when nothing matches.
     *
     * @return Collection<int, Product>
     */
    public function searchBy(string $term): Collection;
}
