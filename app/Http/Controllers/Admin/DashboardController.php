<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Http\Requests\Admin\DashboardPeriodRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function orders(): JsonResponse
    {
        return response()->json(
            Order::query()
                ->with('items')
                ->latest('id')
                ->paginate(10)
        );
    }

    public function summary(): JsonResponse
    {
        $paidOrders = Order::query()
            ->whereNotNull('paid_at');

        $currencies = (clone $paidOrders)
            ->distinct()
            ->pluck('currency');

        $revenue = $currencies->mapWithKeys(
            fn (string $currency) => [
                $currency => (clone $paidOrders)
                    ->where('currency', $currency)
                    ->sum('grand_total'),
            ]
        );

        return response()->json([
            'data' => [
                'products_total' => Product::count(),
                'orders_total' => Order::count(),
                'paid_orders' => (clone $paidOrders)->count(),
                'revenue_by_currency' => $revenue,
            ],
        ]);
    }

    public function sales(DashboardPeriodRequest $request): JsonResponse
    {
        $sales = [];

        $orders = Order::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [
                CarbonImmutable::parse($request->validated('from') ?? now()->subDays(29)->toDateString())->startOfDay(),
                CarbonImmutable::parse($request->validated('until') ?? now()->toDateString())->endOfDay(),
            ])
            ->select([
                'id',
                'paid_at',
                'currency',
                'grand_total',
            ])
            ->cursor();

        foreach ($orders as $order) {
            $date = $order->paid_at->toDateString();
            $currency = $order->currency;
            $key = "{$date}:{$currency}";

            if (!isset($sales[$key])) {
                $sales[$key] = [
                    'sale_date' => $date,
                    'currency' => $currency,
                    'total' => '0.00',
                    'orders' => 0,
                ];
            }

            $sales[$key]['total'] = bcadd(
                $sales[$key]['total'],
                (string) $order->grand_total,
                2
            );

            $sales[$key]['orders']++;
        }

        $rows = collect($sales)
            ->sortBy([
                ['sale_date', 'asc'],
                ['currency', 'asc'],
            ])
            ->values();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function topProducts(DashboardPeriodRequest $request): JsonResponse
    {
        $from = CarbonImmutable::parse($request->validated('from') ?? now()->subDays(29)->toDateString())->startOfDay();
        $until = CarbonImmutable::parse($request->validated('until') ?? now()->toDateString())->endOfDay();
        $paidOrder = fn ($query) => $query
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $until])
            ->when($request->validated('currency'), fn ($q, $currency) => $q->where('currency', $currency));

        $products = Product::query()
            ->whereHas(
                'orderItems',
                fn ($query) => $query
                    ->whereHas('order', $paidOrder)
            )
            ->withSum(
                [
                    'orderItems as units_sold' => fn ($query) =>
                        $query->whereHas('order', $paidOrder),
                ],
                'quantity'
            )
            ->orderByDesc('units_sold')
            ->orderBy('id')
            ->limit(3)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $products->map(fn ($product) => [
                'product_id' => $product->id,
                'name' => $product->name,
                'units_sold' => (int) $product->units_sold,
            ]),
        ]);
    }
}
