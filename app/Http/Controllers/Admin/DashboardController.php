<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function orders(): JsonResponse
    {
        return response()->json(Order::with('items')->orderByDesc('id')->paginate(10));
    }

    public function summary(): JsonResponse
    {
        $paidOrders = Order::query()
            ->whereNotNull('paid_at');

        return response()->json([
            'data' => [
                'products_total' => Product::count(),

                'orders_total' => Order::count(),

                'paid_orders' => (clone $paidOrders)->count(),

                'revenue_by_currency' => (clone $paidOrders)
                    ->select('currency')
                    ->selectRaw('SUM(grand_total) as total')
                    ->groupBy('currency')
                    ->pluck('total', 'currency'),
            ],
        ]);
    }

    public function sales(): JsonResponse
    {
        $rows = Order::query()
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(paid_at) as sale_date')
            ->select('currency')
            ->selectRaw('SUM(grand_total) as total')
            ->selectRaw('COUNT(*) as orders')
            ->groupByRaw('DATE(paid_at), currency')
            ->orderBy('sale_date')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function topProducts(): JsonResponse
    {
        $rows = OrderItem::query()
            ->join(
                'orders',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->whereNotNull('orders.paid_at')
            ->select([
                'order_items.product_id',
                'order_items.name_snapshot',
            ])
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupBy(
                'order_items.product_id',
                'order_items.name_snapshot'
            )
            ->orderByDesc('units_sold')
            ->limit(3)
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }
}
