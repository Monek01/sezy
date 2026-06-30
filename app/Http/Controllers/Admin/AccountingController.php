<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccountingController extends Controller
{
    public function exportPdf(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodDates($period);

        $transactions = \Illuminate\Support\Facades\DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("
                orders.order_number,
                COALESCE(CONCAT(users.first_name,' ',users.last_name), orders.customer_name,'Invité') AS customer_name,
                orders.payment_method,
                orders.total,
                orders.payment_status,
                orders.created_at
            ")
            ->orderByDesc('orders.created_at')
            ->get();

        $revenue = $transactions->where('payment_status', 'paid')->sum('total');
        $refunds = $transactions->where('payment_status', 'refunded')->sum('total');

        $pdf = app('dompdf.wrapper')->loadView('pdf.accounting-report', [
            'period'       => $period,
            'start'        => $start,
            'end'          => $end,
            'transactions' => $transactions,
            'revenue'      => $revenue,
            'refunds'      => $refunds,
        ]);

        return $pdf->download("rapport-comptable-{$period}-" . now()->format('Y-m-d') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodDates($period);

        $rows = \Illuminate\Support\Facades\DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("
                orders.order_number,
                COALESCE(CONCAT(users.first_name,' ',users.last_name), orders.customer_name,'Invité') AS customer,
                orders.payment_method,
                orders.total,
                orders.payment_status,
                orders.created_at
            ")
            ->orderByDesc('orders.created_at')
            ->get();

        $headers = ['Numéro', 'Client', 'Paiement', 'Total (FCFA)', 'Statut', 'Date'];
        $lines = [$headers];
        foreach ($rows as $r) {
            $lines[] = [
                $r->order_number,
                trim($r->customer),
                $r->payment_method,
                number_format($r->total, 0, ',', ' '),
                $r->payment_status,
                $r->created_at,
            ];
        }

        $callback = function () use ($lines) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            foreach ($lines as $line) {
                fputcsv($handle, $line, ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rapport-' . $period . '-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodDates($period);

        $diffDays = (int) $start->diffInDays($end);
        $prevEnd   = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($diffDays)->startOfDay();

        // ---- Revenue current vs previous ----
        $revenue     = $this->sumRevenue($start, $end);
        $prevRevenue = $this->sumRevenue($prevStart, $prevEnd);

        // ---- Cost (cost_price × qty) current vs previous ----
        $cost     = $this->sumCost($start, $end);
        $prevCost = $this->sumCost($prevStart, $prevEnd);

        // ---- Derived metrics ----
        $grossProfit     = $revenue - $cost;
        $prevGrossProfit = $prevRevenue - $prevCost;

        $marginRate     = $revenue > 0     ? round(($grossProfit     / $revenue)     * 100, 2) : 0.0;
        $prevMarginRate = $prevRevenue > 0 ? round(($prevGrossProfit / $prevRevenue) * 100, 2) : 0.0;

        $paidOrders     = $this->countPaidOrders($start, $end);
        $prevPaidOrders = $this->countPaidOrders($prevStart, $prevEnd);

        $avgBasket     = $paidOrders     > 0 ? round($revenue     / $paidOrders)     : 0;
        $prevAvgBasket = $prevPaidOrders > 0 ? round($prevRevenue / $prevPaidOrders) : 0;

        $refunds = Order::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'refunded')
            ->sum('total');

        $summary = [
            'revenue'              => (float) $revenue,
            'revenue_change'       => $this->pctChange($prevRevenue, $revenue),
            'cost'                 => (float) $cost,
            'cost_change'          => $this->pctChange($prevCost, $cost),
            'gross_profit'         => (float) $grossProfit,
            'gross_profit_change'  => $this->pctChange($prevGrossProfit, $grossProfit),
            'margin_rate'          => $marginRate,
            'margin_rate_change'   => round($marginRate - $prevMarginRate, 1),
            'paid_orders'          => $paidOrders,
            'orders_change'        => $this->pctChange($prevPaidOrders, $paidOrders),
            'avg_basket'           => $avgBasket,
            'avg_basket_change'    => $this->pctChange($prevAvgBasket, $avgBasket),
            'refunds'              => (float) $refunds,
        ];

        // ---- Monthly revenue last 12 months (use DB::table to avoid Eloquent issues) ----
        $monthlyRevenue = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '%b %Y') as month")
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as sort_key")
            ->selectRaw('SUM(total) as revenue')
            ->selectRaw('COUNT(*) as order_count')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->where('payment_status', 'paid')
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')")
            ->orderByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->get()
            ->map(fn ($r) => [
                'month'   => $r->month,
                'revenue' => (float) $r->revenue,
                'cost'    => 0,
                'orders'  => (int) $r->order_count,
            ]);

        // ---- Top products by revenue ----
        $topProducts = DB::table('order_items')
            ->join('orders',   'orders.id',   '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('products.title, SUM(order_items.line_total) as revenue, SUM(order_items.quantity) as qty_sold')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        // ---- Recent transactions ----
        $recentTransactions = DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("
                orders.id,
                orders.order_number,
                COALESCE(CONCAT(users.first_name, ' ', users.last_name), orders.customer_name, 'Invité') AS customer_name,
                orders.payment_method,
                orders.total,
                orders.payment_status,
                orders.created_at
            ")
            ->orderByDesc('orders.created_at')
            ->take(20)
            ->get()
            ->map(fn ($r) => [
                'id'             => $r->id,
                'order_number'   => $r->order_number,
                'customer_name'  => trim($r->customer_name),
                'payment_method' => $r->payment_method,
                'total'          => (float) $r->total,
                'cost'           => 0,
                'payment_status' => $r->payment_status,
                'created_at'     => $r->created_at,
            ]);

        return Inertia::render('Admin/Accounting/Index', [
            'summary'             => $summary,
            'monthlyRevenue'      => $monthlyRevenue,
            'topProducts'         => $topProducts,
            'recentTransactions'  => $recentTransactions,
            'periodFilter'        => $period,
        ]);
    }

    // ---- Helpers ----

    private function sumRevenue(Carbon $from, Carbon $to): float
    {
        return (float) DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->sum('total');
    }

    private function sumCost(Carbon $from, Carbon $to): float
    {
        return (float) DB::table('order_items')
            ->join('orders',   'orders.id',   '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->sum(DB::raw('COALESCE(products.cost_price, 0) * order_items.quantity'));
    }

    private function countPaidOrders(Carbon $from, Carbon $to): int
    {
        return (int) DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->count();
    }

    private function pctChange(float|int $prev, float|int $current): float
    {
        if ($prev == 0) return 0.0;
        return round((($current - $prev) / abs($prev)) * 100, 1);
    }

    private function periodDates(string $period): array
    {
        return match ($period) {
            'today'   => [now()->startOfDay(),     now()->endOfDay()],
            'week'    => [now()->startOfWeek(),    now()->endOfWeek()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year'    => [now()->startOfYear(),    now()->endOfYear()],
            default   => [now()->startOfMonth(),   now()->endOfMonth()],
        };
    }
}
