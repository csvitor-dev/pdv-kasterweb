<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Repositories\Contracts\ProductRepository;
use App\Services\Product\Contracts\ProductService as ProductServiceContract;
use Illuminate\Database\Eloquent\Collection;

class ProductService implements ProductServiceContract
{
    private const int SEARCH_LIMIT = 20;

    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {}

    public function searchBy(string $term): Collection
    {
        return $this->productRepository->searchProducts($term, self::SEARCH_LIMIT);
    }
}
