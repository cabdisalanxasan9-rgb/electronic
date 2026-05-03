<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
            'cartCount' => (int) collect($request->session()->get('cart', []))->sum(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load('items', 'returns', 'statusLogs.user', 'paymentEvents');

        return view('orders.show', [
            'order' => $order,
            'cartCount' => (int) collect($request->session()->get('cart', []))->sum(),
            'returnWindowDays' => (int) config('shop.return_window_days', 10),
            'canReturn' => $this->canReturn($order),
            'trackingSteps' => $this->trackingSteps($order),
        ]);
    }

    private function trackingSteps(Order $order): array
    {
        $steps = [
            'pending_payment' => 'Payment',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
        ];

        $currentIndex = array_search((string) $order->status, array_keys($steps), true);
        if ($order->status === 'completed') {
            $currentIndex = array_search('delivered', array_keys($steps), true);
        }

        return collect($steps)
            ->map(function (string $label, string $status) use ($currentIndex, $steps): array {
                $stepIndex = array_search($status, array_keys($steps), true);

                return [
                    'status' => $status,
                    'label' => $label,
                    'is_complete' => $currentIndex !== false && $stepIndex <= $currentIndex,
                ];
            })
            ->values()
            ->all();
    }

    private function canReturn(Order $order): bool
    {
        if ($order->returns->isNotEmpty()) {
            return false;
        }

        if (! in_array($order->status, ['completed', 'delivered'], true)) {
            return false;
        }

        $cutoff = now()->subDays((int) config('shop.return_window_days', 10));

        return $order->created_at !== null && $order->created_at->gte($cutoff);
    }
}
