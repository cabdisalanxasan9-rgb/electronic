<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $monthStart = now()->startOfMonth();
        $lowStockThreshold = (int) config('shop.low_stock_threshold', 5);

        $dailySales = Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('admin.reports.index', [
            'stats' => [
                'todaySales' => (float) Order::query()->whereDate('created_at', today())->sum('grand_total'),
                'monthlyRevenue' => (float) Order::query()->where('created_at', '>=', $monthStart)->sum('grand_total'),
                'paidOrders' => (int) Order::query()->where('payment_status', 'paid')->count(),
                'lowStock' => (int) Product::query()->where('stock', '<=', $lowStockThreshold)->count(),
            ],
            'dailySales' => $dailySales,
            'topProducts' => DB::table('order_items')
                ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_sales')
                ->groupBy('product_name')
                ->orderByDesc('total_qty')
                ->limit(10)
                ->get(),
            'lowStockProducts' => Product::query()
                ->where('stock', '<=', $lowStockThreshold)
                ->orderBy('stock')
                ->limit(10)
                ->get(['id', 'name', 'stock']),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $fileName = 'orders-report-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Number', 'Customer', 'Email', 'Payment', 'Status', 'Discount', 'Tax', 'Total', 'Date']);

            Order::query()->latest()->chunk(200, function ($orders) use ($handle): void {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->customer_name,
                        $order->customer_email,
                        strtoupper($order->payment_status),
                        ucfirst($order->status),
                        number_format((float) $order->discount_amount, 2),
                        number_format((float) $order->tax_amount, 2),
                        number_format((float) $order->grand_total, 2),
                        $order->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $orders = Order::query()->latest()->limit(200)->get();

        $pdf = Pdf::loadView('admin.reports.orders-pdf', [
            'orders' => $orders,
            'generatedAt' => now(),
        ]);

        return $pdf->download('orders-report-'.now()->format('Ymd-His').'.pdf');
    }
}
