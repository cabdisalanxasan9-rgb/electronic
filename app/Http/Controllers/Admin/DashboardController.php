<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();

        $lowStockThreshold = (int) config('shop.low_stock_threshold', 5);

        $stats = [
            'todaySales' => (float) Order::query()->whereDate('created_at', $today)->sum('grand_total'),
            'pendingOrders' => (int) Order::query()->where('status', 'processing')->count(),
            'totalOrders' => (int) Order::query()->count(),
            'totalProducts' => (int) Product::query()->withTrashed()->count(),
            'totalUsers' => (int) User::query()->count(),
            'lowStock' => (int) Product::query()->where('stock', '<=', $lowStockThreshold)->count(),
            'pendingReturns' => (int) OrderReturn::query()->where('status', 'pending')->count(),
        ];

        $topProducts = DB::table('order_items')
            ->selectRaw('product_name, SUM(quantity) as total_qty')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'topProducts' => $topProducts,
            'latestAudits' => AuditLog::query()->with('user')->latest()->limit(10)->get(),
            'notifications' => $request->user()->notifications()->latest()->limit(8)->get(),
            'lowStockProducts' => Product::query()
                ->where('stock', '<=', $lowStockThreshold)
                ->orderBy('stock')
                ->limit(6)
                ->get(['id', 'name', 'stock']),
            'recentReturns' => OrderReturn::query()->with('order')->latest()->limit(6)->get(),
        ]);
    }
}
