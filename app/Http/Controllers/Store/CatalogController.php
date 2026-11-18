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

        return $this->catalogResponse([
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
        Request $request,
        string $product
    ): JsonResponse {
        // Avoid implicit model binding: SELECT * would read descriptions before Redis.
        if ($request->route()->bindingFieldFor('product') === 'slug') {
            $id = Product::query()->where('slug', $product)->value('id');
            abort_if($id === null, 404);
        } else {
            abort_unless(ctype_digit($product), 404);
            $id = (int) $product;
        }

        $result = $this->catalog->findPublicProduct((int) $id);

        if ($result === null) {
            abort(404);
        }

        return $this->catalogResponse([
            'data' => $this->catalog->toDto($result),
        ]);
    }

    public function lookup(
        CatalogLookupRequest $request
    ): JsonResponse {
        $products = $this->catalog->findByIds(
            $request->validated('product_ids')
        );

        return $this->catalogResponse([
            'data' => $products
                ->map(
                    fn ($product) => $this->catalog->toDto($product)
                )
                ->values()
                ->all(),
        ]);
    }

    private function catalogResponse(array $data): JsonResponse
    {
        $response = response()->json($data);

        if (request()->attributes->get('catalog_descriptions_from_redis') === true) {
            $response->headers->set('X-Descriptions-Source', 'redis');
        }

        return $response;
    }
}
