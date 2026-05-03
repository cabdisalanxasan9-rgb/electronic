<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Notifications\OrderEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnAdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $returns = OrderReturn::query()
            ->with(['order', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.returns.index', [
            'returns' => $returns,
        ]);
    }

    public function update(Request $request, OrderReturn $return): RedirectResponse
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,refunded'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $return->update($validated);
        $return->loadMissing('order', 'user');

        if ($return->user && $return->order) {
            $return->user->notify(new OrderEventNotification(
                $return->order,
                'Return request '.$return->order->order_number.' is now '.ucfirst($return->status).'.'
            ));
        }

        return back()->with('status', 'Return updated.');
    }
}
