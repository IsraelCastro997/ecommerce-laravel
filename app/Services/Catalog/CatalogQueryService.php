<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceSnapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use LogicException;

class CatalogQueryService
{
    /**
     * Restringe los precios a publicaciones activas
     * del mercado mexicano.
     */
    private function publishedPrices(Builder $query): Builder
    {
        return $query->whereHas(
            'pricePublication',
            fn (Builder $publication) => $publication
                ->where('is_active', true)
                ->where('status', 'published')
                ->whereHas(
                    'market',
                    fn (Builder $market) => $market
                        ->where('code', 'MX')
                )
        );
    }

    /**
     * Productos visibles para el catálogo.
     */
    private function visibleProducts(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(fn (Builder $q) => $q->whereNull('available_from')->orWhere('available_from', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('available_until')->orWhere('available_until', '>=', now()))
            ->whereHas(
                'priceSnapshots',
                fn (Builder $snapshot) =>
                    $this->publishedPrices($snapshot)
            );
    }

    /**
     * Consulta base con relaciones precargadas.
     */
    private function query(): Builder
    {
        return $this->visibleProducts(
            Product::query()
                ->select([
                    'products.id', 'sku', 'slug', 'name', 'image_url',
                    'brand_id', 'primary_category_id', 'origin_country',
                    'status', 'available_from', 'available_until',
                ])
                ->with([
                    'brand:id,name,slug',
                    'primaryCategory:id,name,slug',

                    'priceSnapshots' => function (
                        HasMany $relation
                    ): void {
                        $this->publishedPrices($relation->getQuery())
                            ->with('pricePublication');
                    },
                ])
                ->withSum(
                    'inventoryBalances as stock_on_hand',
                    'qty_on_hand'
                )
                ->withSum(
                    'inventoryBalances as stock_reserved',
                    'qty_reserved'
                )
        );
    }

    /**
     * Subconsulta para ordenar por precio publicado.
     */
    private function priceOrderQuery(string $currency): Builder
    {
        $column = $currency === 'USD'
            ? 'price_usd'
            : 'price_mxn';

        return $this->publishedPrices(
            ProductPriceSnapshot::query()
                ->select($column)
                ->whereColumn(
                    'product_id',
                    'products.id'
                )
        )
            ->orderByDesc('price_publication_id')
            ->limit(1);
    }

    /**
     * Busca productos con filtros y paginación.
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        $term = trim((string) ($filters['q'] ?? ''));

        if ($term !== '') {
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['category'])) {
            $slug = $filters['category'];

            $query->where(function (Builder $q) use ($slug) {
                $q->whereHas(
                    'primaryCategory',
                    fn (Builder $category) => $category
                        ->where('slug', $slug)
                        ->where('active', true)
                )
                ->orWhereHas(
                    'categories',
                    fn (Builder $category) => $category
                        ->where('slug', $slug)
                        ->where('active', true)
                );
            });
        }

        if (!empty($filters['brand'])) {
            $query->whereHas(
                'brand',
                fn (Builder $brand) => $brand
                    ->where('slug', $filters['brand'])
            );
        }

        if (!empty($filters['stock'])) {
            $query->whereHas(
                'inventoryBalances',
                fn (Builder $balance) => $balance
                    ->whereColumn(
                        'qty_on_hand',
                        '>',
                        'qty_reserved'
                    )
            );
        }

        $currency = ($filters['currency'] ?? 'MXN') === 'USD'
            ? 'USD'
            : 'MXN';

        $priceColumn = $currency === 'USD'
            ? 'price_usd'
            : 'price_mxn';

        if (
            isset($filters['max_price'])
            && $filters['max_price'] !== ''
        ) {
            $query->whereHas(
                'priceSnapshots',
                fn (Builder $snapshot) =>
                    $this->publishedPrices($snapshot)
                        ->where(
                            $priceColumn,
                            '<=',
                            $filters['max_price']
                        )
            );
        }

        switch ($filters['sort'] ?? 'newest') {
            case 'name':
                $query->orderBy('name');
                break;

            case 'price_asc':
                $query->orderBy(
                    $this->priceOrderQuery($currency),
                    'asc'
                );
                break;

            case 'price_desc':
                $query->orderBy(
                    $this->priceOrderQuery($currency),
                    'desc'
                );
                break;

            default:
                $query->orderByDesc('id');
                break;
        }

        if (($filters['sort'] ?? 'newest') !== 'newest') {
            $query->orderBy('id');
        }

        $page = $query->paginate(
            min(100, max(1, (int) ($filters['per_page'] ?? 24)))
        );
        app(ProductDescriptionCache::class)->hydrate($page->getCollection());

        return $page;
    }

    /**
     * Recupera un producto público por ID.
     */
    public function findPublicProduct(int $id): ?Product
    {
        $product = $this->query()
            ->whereKey($id)
            ->first();

        if ($product) {
            app(ProductDescriptionCache::class)->hydrate(new \Illuminate\Database\Eloquent\Collection([$product]));
        }

        return $product;
    }

    /**
     * Recupera productos públicos por sus IDs.
     */
    public function findByIds(array $ids): Collection
    {
        $products = $this->query()
            ->whereKey($ids)
            ->get();
        app(ProductDescriptionCache::class)->hydrate($products);

        return $products;
    }

    /**
     * Categorías y marcas con productos visibles.
     */
    public function filters(): array
    {
        $visible = fn (Builder $query) =>
            $this->visibleProducts($query);

        $brands = Brand::query()
            ->whereHas('products', $visible)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $categories = Category::query()
            ->where('active', true)
            ->where(function (Builder $query) use ($visible) {
                $query
                    ->whereHas('primaryProducts', $visible)
                    ->orWhereHas('products', $visible);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return [
            'brands' => $brands,
            'categories' => $categories,
            'currencies' => ['MXN', 'USD'],
        ];
    }

    /**
     * Transforma el producto al formato de Alpine.
     */
    public function toDto(Product $product): array
    {
        $snapshots = $product->priceSnapshots;

        if ($snapshots->count() !== 1) {
            throw new LogicException(
                'El producto ' . $product->id .
                ' no tiene exactamente un precio publicado vigente.'
            );
        }

        $snapshot = $snapshots->first();

        $onHand = (int) ($product->stock_on_hand ?? 0);
        $reserved = (int) ($product->stock_reserved ?? 0);

        $availableQuantity = max(
            0,
            $onHand - $reserved
        );

        return [
            'product_id' => (int) $product->id,
            'id' => (string) $product->id,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'detail' => $product->long_description ?? $product->description ?? '',
            'short_description' => $product->short_description ?? '',
            'long_description' => $product->long_description ?? $product->description ?? '',
            'available_from' => $product->available_from?->toIso8601String(),
            'available_until' => $product->available_until?->toIso8601String(),

            'category' => $product->primaryCategory?->name,
            'brand' => $product->brand?->name,
            'origin' => $product->origin_country,

            'available' => $availableQuantity > 0,
            'available_quantity' => $availableQuantity,

            'prices' => [
                'MXN' => (float) $snapshot->price_mxn,
                'USD' => (float) $snapshot->price_usd,
            ],

            'image' => ($product->image_url ?: sprintf('https://picsum.photos/seed/product-%d/640/640', $product->id)),
            'image_url' => ($product->image_url ?: sprintf('https://picsum.photos/seed/product-%d/640/640', $product->id)),
        ];
    }
}
