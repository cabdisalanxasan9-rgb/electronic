<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderReturnController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        if (! $this->isReturnable($order)) {
            return back()->with('status', 'This order is not eligible for return.');
        }

        OrderReturn::query()->firstOrCreate([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
        ], [
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return back()->with('status', 'Return request submitted.');
    }

    private function isReturnable(Order $order): bool
    {
        if (! in_array($order->status, ['completed', 'delivered'], true)) {
            return false;
        }

        $cutoff = now()->subDays((int) config('shop.return_window_days', 10));

        return $order->created_at !== null && $order->created_at->gte($cutoff);
    }
}
