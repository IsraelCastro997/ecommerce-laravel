<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogQueryService
{
    private function query(): Builder
    {
        $stock = DB::table('inventory_balances')
            ->select('product_id')
            ->selectRaw(
                'SUM(GREATEST(qty_on_hand - qty_reserved, 0)) AS available_quantity'
            )
            ->groupBy('product_id');

        return Product::query()
            ->select([
                'products.*',
                'brands.name as brand_name',
                'categories.name as category_name',
                'snapshots.price_mxn as published_price_mxn',
                'snapshots.price_usd as published_price_usd',
            ])
            ->selectRaw(
                'COALESCE(stock.available_quantity, 0) as available_quantity'
            )
            ->leftJoin(
                'brands',
                'brands.id',
                '=',
                'products.brand_id'
            )
            ->leftJoin(
                'categories',
                'categories.id',
                '=',
                'products.primary_category_id'
            )
            ->join(
                'product_price_snapshots as snapshots',
                'snapshots.product_id',
                '=',
                'products.id'
            )
            ->join(
                'price_publications as publications',
                'publications.id',
                '=',
                'snapshots.price_publication_id'
            )
            ->join(
                'markets',
                'markets.id',
                '=',
                'publications.market_id'
            )
            ->leftJoinSub(
                $stock,
                'stock',
                fn ($join) => $join->on(
                    'stock.product_id',
                    '=',
                    'products.id'
                )
            )
            ->with('images')
            ->where('products.status', 'active')
            ->where('publications.is_active', true)
            ->where('publications.status', 'published')
            ->where('markets.code', 'MX');
    }

    public function search(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        $term = trim((string) ($filters['q'] ?? ''));

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('products.name', 'like', '%' . $term . '%')
                    ->orWhere('products.sku', 'like', '%' . $term . '%')
                    ->orWhere('products.description', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filters['category'])) {
            $slug = $filters['category'];

            $query->where(function (Builder $builder) use ($slug) {
                $builder
                    ->whereHas(
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
            $query->where(
                'stock.available_quantity',
                '>',
                0
            );
        }

        $currency = $filters['currency'] ?? 'MXN';

        $priceColumn = $currency === 'USD'
            ? 'snapshots.price_usd'
            : 'snapshots.price_mxn';

        if (
            isset($filters['max_price'])
            && $filters['max_price'] !== ''
        ) {
            $query->where(
                $priceColumn,
                '<=',
                $filters['max_price']
            );
        }

        switch ($filters['sort'] ?? 'newest') {
            case 'name':
                $query->orderBy('products.name');
                break;

            case 'price_asc':
                $query->orderBy($priceColumn);
                break;

            case 'price_desc':
                $query->orderByDesc($priceColumn);
                break;

            default:
                $query->orderByDesc('products.id');
                break;
        }

        $query->orderBy('products.id');

        return $query->paginate(
            min(100, (int) ($filters['per_page'] ?? 24))
        );
    }

    public function findPublicProduct(int $id): ?Product
    {
        return $this->query()
            ->where('products.id', $id)
            ->first();
    }

    public function findByIds(array $ids): Collection
    {
        return $this->query()
            ->whereIn('products.id', $ids)
            ->get();
    }

    public function filters(): array
    {
        $visibleProducts = function (Builder $query): void {
            $query
                ->where('status', 'active')
                ->whereHas('priceSnapshots', function (Builder $snapshot) {
                    $snapshot->whereHas(
                        'pricePublication',
                        function (Builder $publication) {
                            $publication
                                ->where('is_active', true)
                                ->where('status', 'published')
                                ->whereHas(
                                    'market',
                                    fn (Builder $market) => $market
                                        ->where('code', 'MX')
                                );
                        }
                    );
                });
        };

        $brands = Brand::query()
            ->whereHas('products', $visibleProducts)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $categories = Category::query()
            ->where('active', true)
            ->where(function (Builder $query) use ($visibleProducts) {
                $query
                    ->whereHas('primaryProducts', $visibleProducts)
                    ->orWhereHas('products', $visibleProducts);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return [
            'brands' => $brands,
            'categories' => $categories,
            'currencies' => ['MXN', 'USD'],
        ];
    }

    public function toDto(Product $product): array
    {
        $image = $product->images
            ->firstWhere('is_primary', true)
            ?? $product->images->first();

        $imageUrl = $image === null
            ? null
            : Storage::disk($image->disk)->url($image->path);

        $availableQuantity = (int) $product->available_quantity;

        return [
            'product_id' => (int) $product->id,
            'id' => (string) $product->id,
            'sku' => $product->sku,
            'slug' => $product->slug,
            'name' => $product->name,
            'detail' => $product->description ?? '',
            'category' => $product->category_name,
            'brand' => $product->brand_name,
            'origin' => $product->origin_country,
            'available' => $availableQuantity > 0,
            'available_quantity' => $availableQuantity,
            'prices' => [
                'MXN' => (float) $product->published_price_mxn,
                'USD' => (float) $product->published_price_usd,
            ],
            'image' => $imageUrl,
        ];
    }
}