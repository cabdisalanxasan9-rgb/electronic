<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Notifications\OrderEventNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Mail\OrderStatusMail;

class OrderAdminController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Order::class);

        return view('admin.orders.create', [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:stripe,cod'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
            'status' => ['required', 'in:pending_payment,processing,shipped,completed,cancelled,delivered'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'shipping_zone' => ['nullable', 'in:metro,regional,pickup'],
            'courier_name' => ['nullable', 'string', 'max:120'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'estimated_delivery_at' => ['nullable', 'date'],
        ], [
            'user_id.required' => 'Please select a user.',
            'product_id.required' => 'Please select a product.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Minimum quantity is 1.',
            'quantity.max' => 'Maximum quantity is 20.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_status.required' => 'Please select a payment status.',
            'status.required' => 'Please select an order status.',
            'customer_name.required' => 'Customer name is required.',
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Customer email is invalid.',
            'shipping_address.required' => 'Shipping address is required.',
        ]);

        $productId = (string) $validated['product_id'];
        $quantity = (int) $validated['quantity'];

        $order = DB::transaction(function () use ($validated, $productId, $quantity): Order {
            $product = Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
            if ($product->stock !== null && $product->stock < $quantity) {
                throw new \Exception('Product ' . $product->name . ' has insufficient stock.');
            }

            if ($product->stock !== null) {
                $oldStock = (int) $product->stock;
                $product->decrement('stock', $quantity);
                InventoryAdjustment::query()->create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'old_stock' => $oldStock,
                    'new_stock' => $oldStock - $quantity,
                    'delta' => -1 * $quantity,
                    'reason' => 'admin order',
                    'note' => 'Admin-created order stock decrement',
                ]);
            }

            $subTotal = (float) $product->price * $quantity;
            $shipping = (float) ($validated['shipping_amount'] ?? 0);
            $grandTotal = $subTotal + $shipping;

            $order = Order::query()->create([
                'user_id' => (int) $validated['user_id'],
                'order_number' => 'ORD-ADM-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'shipping_amount' => $shipping,
                'tax_amount' => 0,
                'grand_total' => $grandTotal,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'status' => $validated['status'],
                'transaction_ref' => null,
                'coupon_code' => null,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'shipping_address' => $validated['shipping_address'],
                'shipping_zone' => $validated['shipping_zone'] ?? null,
                'courier_name' => $validated['courier_name'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'estimated_delivery_at' => $validated['estimated_delivery_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => $subTotal,
            ]);

            OrderStatusLog::query()->create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'old_status' => null,
                'new_status' => $validated['status'],
                'old_payment_status' => null,
                'new_payment_status' => $validated['payment_status'],
                'note' => 'Order created by admin',
            ]);

            return $order;
        });

        AuditLogger::log(Auth::user(), 'order.created.admin', $order, [
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order created.');
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $status = (string) $request->query('status', 'all');
        $paymentStatus = (string) $request->query('payment_status', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = Order::query()->with('user');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        if ($q !== '') {
            $query->where(function ($inner) use ($q): void {
                $inner->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%");
            });
        }

        return view('admin.orders.index', [
            'orders' => $query->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'paymentStatus' => $paymentStatus,
            'q' => $q,
        ]);
    }

    public function show(Order $order): View
    {
        abort_unless(request()->user()->canManageOrders(), 403);
        $order->load('items', 'user', 'statusLogs.user', 'returns', 'paymentEvents');

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', 'in:pending_payment,processing,shipped,completed,cancelled,delivered'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
            'note' => ['nullable', 'string', 'max:500'],
            'courier_name' => ['nullable', 'string', 'max:120'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'estimated_delivery_at' => ['nullable', 'date'],
        ], [
            'status.required' => 'Order status is required.',
            'payment_status.required' => 'Payment status is required.',
        ]);

        $oldStatus = (string) $order->status;
        $oldPayment = (string) $order->payment_status;

        $order->update([
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'],
            'courier_name' => $validated['courier_name'] ?? $order->courier_name,
            'tracking_number' => $validated['tracking_number'] ?? $order->tracking_number,
            'estimated_delivery_at' => $validated['estimated_delivery_at'] ?? $order->estimated_delivery_at,
        ]);

        OrderStatusLog::query()->create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'old_payment_status' => $oldPayment,
            'new_payment_status' => $validated['payment_status'],
            'note' => $validated['note'] ?? null,
        ]);

        AuditLogger::log(Auth::user(), 'order.status.updated', $order, [
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'old_payment_status' => $oldPayment,
            'new_payment_status' => $validated['payment_status'],
        ]);

        Mail::to($order->customer_email)->send(new OrderStatusMail(
            $order,
            'Your order status was updated to '.str_replace('_', ' ', $validated['status']).'.'
        ));

        return back()->with('status', 'Order status updated.');
    }

    public function markDelivered(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $oldStatus = (string) $order->status;
        $order->update(['status' => 'delivered']);

        OrderStatusLog::query()->create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'delivered',
            'old_payment_status' => (string) $order->payment_status,
            'new_payment_status' => (string) $order->payment_status,
            'note' => 'Marked as delivered by admin',
        ]);

        AuditLogger::log(Auth::user(), 'order.marked_delivered', $order);

        Mail::to($order->customer_email)->send(new OrderStatusMail(
            $order,
            'Your order has been delivered.'
        ));

        return back()->with('status', 'Order marked as delivered.');
    }

    public function refund(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $oldStatus = (string) $order->status;
        $oldPayment = (string) $order->payment_status;
        $order->update([
            'payment_status' => 'refunded',
            'status' => 'cancelled',
        ]);

        OrderStatusLog::query()->create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => 'cancelled',
            'old_payment_status' => $oldPayment,
            'new_payment_status' => 'refunded',
            'note' => 'Refund triggered by admin',
        ]);

        if ($order->relationLoaded('user') || $order->user) {
            $order->user->notify(new OrderEventNotification($order, 'Order '.$order->order_number.' was refunded.'));
        }

        AuditLogger::log(Auth::user(), 'order.refunded', $order);

        Mail::to($order->customer_email)->send(new OrderStatusMail(
            $order,
            'Your order has been refunded.'
        ));

        return back()->with('status', 'Refund recorded.');
    }

    public function printInvoice(Order $order): View
    {
        abort_unless(request()->user()->canManageOrders(), 403);
        $order->load('items', 'user');

        return view('admin.orders.print-invoice', [
            'order' => $order,
        ]);
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $orderNumber = $order->order_number;
        $order->delete();

        AuditLogger::log(Auth::user(), 'order.deleted.admin', null, [
            'order_number' => $orderNumber,
        ]);

        return redirect()->route('admin.orders.index')->with('status', 'Order deleted.');
    }
}
