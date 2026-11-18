<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductDescriptionCache
{
    private const FIELDS = ['description', 'short_description', 'long_description'];

    public static function key(int $id): string
    {
        return "catalog:product:{$id}:descriptions:v1";
    }

    public function hydrate(Collection $products): void
    {
        request()->attributes->set('catalog_descriptions_from_redis', false);

        if ($products->isEmpty()) {
            return;
        }

        // A single database read for all cache misses on this page.
        $keys = $products->map(fn (Product $product) => self::key($product->id))->all();
        $cache = Cache::store('redis');
        $values = $cache->many($keys);
        $missing = $products->filter(fn (Product $product) => ! is_array($values[self::key($product->id)] ?? null));

        request()->attributes->set('catalog_descriptions_from_redis', $missing->isEmpty());

        if ($missing->isNotEmpty()) {
            $rows = Product::query()->whereKey($missing->modelKeys())
                ->get(['id', ...self::FIELDS]);
            $fresh = [];
            foreach ($rows as $row) {
                $fresh[self::key($row->id)] = $row->only(self::FIELDS);
            }
            if ($fresh !== []) {
                $cache->putMany($fresh, 3600);
                $values = array_replace($values, $fresh);
            }
        }

        foreach ($products as $product) {
            foreach ($values[self::key($product->id)] ?? [] as $field => $value) {
                $product->setAttribute($field, $value);
            }
            $product->syncOriginalAttributes(self::FIELDS);
        }
    }

    public static function forget(int $id): void
    {
        Cache::store('redis')->forget(self::key($id));
    }
}
