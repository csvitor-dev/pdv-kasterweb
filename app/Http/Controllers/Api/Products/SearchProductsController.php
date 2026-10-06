<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\SearchProductsRequest;
use App\Http\Resources\ProductResource;
use App\Services\Product\Contracts\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SearchProductsController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function __invoke(SearchProductsRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->productService->searchBy($request->term()),
        );
    }
}
