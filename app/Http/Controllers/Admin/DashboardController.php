<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->get('period', '14days');
        [$start, $end, $prevStart, $prevEnd] = $this->periodRange($period);

        // KPIs current vs previous
        $revenue     = (float) DB::table('orders')->whereBetween('created_at', [$start, $end])->where('payment_status', 'paid')->sum('total');
        $prevRevenue = (float) DB::table('orders')->whereBetween('created_at', [$prevStart, $prevEnd])->where('payment_status', 'paid')->sum('total');

        $orders     = (int) DB::table('orders')->whereBetween('created_at', [$start, $end])->count();
        $prevOrders = (int) DB::table('orders')->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $customers     = (int) User::where('role', 'client')->whereBetween('created_at', [$start, $end])->count();
        $prevCustomers = (int) User::where('role', 'client')->whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $kpis = [
            'revenue'          => $revenue,
            'revenue_change'   => $this->pctChange($prevRevenue, $revenue),
            'revenue_today'    => (float) DB::table('orders')->whereDate('created_at', now()->toDateString())->where('payment_status', 'paid')->sum('total'),
            'revenue_month'    => (float) DB::table('orders')->where('created_at', '>=', now()->startOfMonth())->where('payment_status', 'paid')->sum('total'),
            'orders'           => $orders,
            'orders_change'    => $this->pctChange($prevOrders, $orders),
            'orders_today'     => (int) DB::table('orders')->whereDate('created_at', now()->toDateString())->count(),
            'orders_pending'   => (int) Order::where('status', 'pending')->count(),
            'customers'        => $customers,
            'customers_change' => $this->pctChange($prevCustomers, $customers),
            'customers_total'  => (int) User::where('role', 'client')->count(),
            'average_basket'   => (float) DB::table('orders')->where('payment_status', 'paid')->avg('total') ?? 0,
            'conversion_rate'  => $this->computeConversionRate(),
            'low_stock_count'  => Product::lowStock()->count(),
        ];

        // Sales chart grouped by day
        $salesChart = DB::table('orders')
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders_count')
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Sales by category
        $salesByCategory = DB::table('order_items')
            ->join('products',   'products.id',   '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('orders',     'orders.id',     '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('categories.name, SUM(order_items.line_total) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        // Top clients by spend
        $topClients = DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("
                COALESCE(CONCAT(users.first_name, ' ', users.last_name), orders.customer_name, 'Invité') AS name,
                COUNT(orders.id) AS orders_count,
                SUM(orders.total) AS total_spent
            ")
            ->groupBy('orders.user_id', 'users.first_name', 'users.last_name', 'orders.customer_name')
            ->orderByDesc('total_spent')
            ->take(8)
            ->get()
            ->map(fn($r) => [
                'name'        => trim($r->name),
                'orders'      => (int) $r->orders_count,
                'total_spent' => (float) $r->total_spent,
            ]);

        // Recent orders
        $recentOrders = Order::with('items')->latest()->take(8)->get();

        // Low stock
        $lowStockProducts = Product::lowStock()->with('category')->take(10)->get();

        // Pending reviews count
        $pendingReviews = DB::table('reviews')->where('status', 'pending')->count();

        return Inertia::render('Admin/Dashboard', [
            'kpis'             => $kpis,
            'salesChart'       => $salesChart,
            'salesByCategory'  => $salesByCategory,
            'topClients'       => $topClients,
            'recentOrders'     => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'pendingReviews'   => (int) $pendingReviews,
            'periodFilter'     => $period,
        ]);
    }

    private function periodRange(string $period): array
    {
        $end   = now()->endOfDay();
        $start = match ($period) {
            '7days'  => now()->subDays(6)->startOfDay(),
            '30days' => now()->subDays(29)->startOfDay(),
            'month'  => now()->startOfMonth(),
            'year'   => now()->startOfYear(),
            default  => now()->subDays(13)->startOfDay(), // 14days
        };
        $diffDays = (int) $start->diffInDays($end);
        $prevEnd   = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($diffDays)->startOfDay();
        return [$start, $end, $prevStart, $prevEnd];
    }

    private function pctChange(float $prev, float $current): float
    {
        if ($prev == 0) return 0.0;
        return round((($current - $prev) / abs($prev)) * 100, 1);
    }

    private function computeConversionRate(): float
    {
        $views  = (int) Product::sum('views_count');
        $orders = Order::where('payment_status', 'paid')->count();
        return $views > 0 ? round(($orders / $views) * 100, 2) : 0.0;
    }
}
