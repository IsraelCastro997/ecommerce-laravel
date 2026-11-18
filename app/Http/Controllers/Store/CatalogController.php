<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CatalogIndexRequest;
use App\Http\Requests\Store\CatalogLookupRequest;
use App\Models\Product;
use App\Services\Catalog\CatalogQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        private readonly CatalogQueryService $catalog
    ) {}

    public function index(
        CatalogIndexRequest $request
    ): JsonResponse {
        $filters = $request->validated();

        $products = $this->catalog->search($filters);

        return response()->json([
            'data' => $products->getCollection()
                ->map(
                    fn ($product) => $this->catalog->toDto($product)
                )
                ->values()
                ->all(),

            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]);
    }

    public function filters(): JsonResponse
    {
        return response()->json(
            $this->catalog->filters()
        );
    }

    public function show(
        Product $product
    ): JsonResponse {
        $result = $this->catalog->findPublicProduct(
            $product->id
        );

        if ($result === null) {
            abort(404);
        }

        return response()->json([
            'data' => $this->catalog->toDto($result),
        ]);
    }

    public function lookup(
        CatalogLookupRequest $request
    ): JsonResponse {
        $products = $this->catalog->findByIds(
            $request->validated('product_ids')
        );

        return response()->json([
            'data' => $products
                ->map(
                    fn ($product) => $this->catalog->toDto($product)
                )
                ->values()
                ->all(),
        ]);
    }
}
