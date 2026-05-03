<?php

namespace App\Http\Controllers;

use App\Mail\OrderInvoiceMail;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\GiftCard;
use App\Models\InventoryAdjustment;
use App\Models\PaymentEvent;
use App\Models\UserAddress;
use App\Models\User;
use App\Models\StockReservation;
use App\Notifications\OrderEventNotification;
use App\Notifications\AdminAlertNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class CheckoutController extends Controller
{
    private const LOYALTY_POINT_VALUE = 0.01;

    public function show(Request $request): View|RedirectResponse
    {
        $cartData = $this->cartData($request);

        if ($cartData['items']->isEmpty()) {
            return redirect()->route('shop.cart')->with('status', 'Your cart is empty.');
        }

        return view('orders.checkout', [
            'items' => $cartData['items'],
            'subTotal' => $cartData['subTotal'],
            'discount' => $cartData['discount'],
            'tax' => $cartData['tax'],
            'coupon' => $cartData['coupon'],
            'giftCard' => $cartData['giftCard'],
            'giftCardAmount' => $cartData['giftCardAmount'],
            'loyaltyPoints' => $cartData['loyaltyPoints'],
            'loyaltyAmount' => $cartData['loyaltyAmount'],
            'shipping' => $cartData['shipping'],
            'grandTotal' => $cartData['grandTotal'],
            'cartCount' => $cartData['cartCount'],
            'addresses' => UserAddress::query()
                ->where('user_id', $request->user()->id)
                ->orderByDesc('is_default')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Prevent admin from placing multiple active orders
        $user = $request->user();
        if ($user && $user->is_admin) {
            $hasActiveOrder = Order::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['processing', 'pending_payment'])
                ->exists();
            if ($hasActiveOrder) {
                return redirect()->route('shop.cart')->with('status', 'Admin cannot place another order until the previous one is completed.');
            }
        }
        $cartData = $this->cartData($request);

        if ($cartData['items']->isEmpty()) {
            return redirect()->route('shop.cart')->with('status', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'address_id' => ['nullable', 'integer'],
            'payment_method' => ['required', 'in:stripe,cod'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'shipping_zone' => ['nullable', 'in:metro,regional,pickup'],
        ]);
        $validated['shipping_zone'] = $validated['shipping_zone'] ?? 'metro';

        if (! empty($validated['address_id'])) {
            $address = UserAddress::query()
                ->where('user_id', $request->user()->id)
                ->whereKey($validated['address_id'])
                ->first();

            if ($address !== null) {
                $validated['customer_name'] = $address->name;
                $validated['customer_phone'] = $address->phone;
                $validated['shipping_address'] = $this->formatAddress($address);
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $cartData, $validated): Order {
                $isCod = $validated['payment_method'] === 'cod';
                $paymentStatus = $isCod ? 'pending' : 'pending';
                $status = $isCod ? 'processing' : 'pending_payment';
                $shouldDecrementStock = $isCod;
                $shippingZone = $this->shippingZone($validated['shipping_zone']);

                $lines = $this->buildCartLines($cartData['items']);
                $this->ensureStock($lines, $shouldDecrementStock, null);

                $order = Order::query()->create([
                    'user_id' => $request->user()->id,
                    'order_number' => $this->generateOrderNumber(),
                    'sub_total' => $cartData['subTotal'],
                    'discount_amount' => $cartData['discount'],
                    'shipping_amount' => $shippingZone['fee'],
                    'tax_amount' => $cartData['tax'],
                    'grand_total' => $cartData['grandTotal'],
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $paymentStatus,
                    'status' => $status,
                    'transaction_ref' => null,
                    'coupon_code' => $cartData['coupon']?->code,
                    'gift_card_code' => $cartData['giftCard']?->code,
                    'gift_card_amount' => $cartData['giftCardAmount'],
                    'loyalty_points_used' => $cartData['loyaltyPoints'],
                    'loyalty_discount_amount' => $cartData['loyaltyAmount'],
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_zone' => $validated['shipping_zone'],
                    'courier_name' => $validated['shipping_zone'] === 'pickup' ? 'Store pickup' : 'ElectroHub Delivery',
                    'estimated_delivery_at' => now()->addDays((int) $shippingZone['eta_days']),
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($cartData['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'product_variant_id' => $item['variant']?->id,
                        'product_name' => $item['product']->name,
                        'variant_name' => $item['variant']?->name,
                        'unit_price' => $item['unitPrice'],
                        'quantity' => $item['quantity'],
                        'line_total' => $item['lineTotal'],
                    ]);
                }

                if (! $shouldDecrementStock) {
                    $this->reserveStock($lines, (int) $order->id);
                }

                OrderStatusLog::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $request->user()->id,
                    'old_status' => null,
                    'new_status' => $status,
                    'old_payment_status' => null,
                    'new_payment_status' => $paymentStatus,
                    'note' => 'Order created via checkout',
                ]);

                AuditLogger::log($request->user(), 'order.created', $order, [
                    'payment_method' => $validated['payment_method'],
                    'grand_total' => (float) $order->grand_total,
                ]);

                User::query()->where('is_admin', true)->get()->each(
                    fn (User $admin) => $admin->notify(new OrderEventNotification($order, 'New order placed: '.$order->order_number))
                );

                return $order;
            });
        } catch (\Throwable $exception) {
            Log::warning('Checkout stock validation failed', ['message' => $exception->getMessage()]);
            return redirect()->route('shop.cart')->with('status', $exception->getMessage());
        }

        if ($validated['payment_method'] === 'cod') {
            PaymentEvent::query()->create([
                'order_id' => $order->id,
                'provider' => 'cod',
                'event_type' => 'cod_selected',
                'status' => 'pending',
            ]);
            $this->finalizeCredits($order, $request->user());
            $this->sendInvoiceIfNeeded($order);
            $this->incrementCouponUsage($order->coupon_code);
            $request->session()->forget('cart');
            $request->session()->forget(['gift_card', 'loyalty']);
            return redirect()->route('orders.show', $order)->with('status', 'Your order has been placed.');
        }

        if ($validated['payment_method'] === 'stripe') {
            return $this->startStripePayment($request, $order);
        }

        return $this->startStripePayment($request, $order);
    }

    public function stripeSuccess(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        $sessionId = (string) $request->query('session_id', '');

        if ($sessionId === '' || config('services.stripe.secret') === null) {
            $order->update(['payment_failure_reason' => 'Stripe verification failed.']);
            PaymentEvent::query()->create([
                'order_id' => $order->id,
                'provider' => 'stripe',
                'event_type' => 'verification_failed',
                'status' => 'failed',
            ]);
            return redirect()->route('orders.show', $order)->with('status', 'Stripe verification failed.');
        }

        Stripe::setApiKey((string) config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        $metadataOrderId = (int) ($session->metadata->order_id ?? 0);
        $metadataOrderNumber = (string) ($session->metadata->order_number ?? '');
        if ($metadataOrderId !== (int) $order->id || ($metadataOrderNumber !== '' && $metadataOrderNumber !== $order->order_number)) {
            return redirect()->route('orders.show', $order)->with('status', 'Stripe session does not match this order.');
        }

        if ($session->payment_status === 'paid') {
            $alreadyPaid = false;

            $paymentRef = (string) ($session->payment_intent ?? $sessionId);

            try {
                $order = DB::transaction(function () use ($order, $request, $paymentRef, &$alreadyPaid): Order {
                    $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                    if ($lockedOrder->payment_status === 'paid') {
                        $alreadyPaid = true;
                        return $lockedOrder;
                    }

                    $lockedOrder->load('items');
                    $lines = $this->buildOrderLines($lockedOrder);
                    $this->ensureStock($lines, true, (int) $lockedOrder->id);

                    $lockedOrder->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'transaction_ref' => $paymentRef,
                    ]);

                    $this->finalizeCredits($lockedOrder, $request->user());
                    $this->clearReservations((int) $lockedOrder->id);

                    OrderStatusLog::query()->create([
                        'order_id' => $lockedOrder->id,
                        'user_id' => $request->user()->id,
                        'old_status' => 'pending_payment',
                        'new_status' => 'processing',
                        'old_payment_status' => 'pending',
                        'new_payment_status' => 'paid',
                        'note' => 'Stripe success callback confirmed payment',
                    ]);

                    return $lockedOrder;
                });
            } catch (\Throwable $exception) {
                Log::warning('Stripe stock validation failed', ['message' => $exception->getMessage()]);
                return redirect()->route('orders.show', $order)->with('status', 'Payment received, but stock could not be confirmed. Support will contact you.');
            }

            if (! $alreadyPaid) {
                AuditLogger::log($request->user(), 'order.payment.paid.stripe', $order, [
                    'session_id' => $sessionId,
                ]);

                $this->incrementCouponUsage($order->coupon_code);
                $this->sendInvoiceIfNeeded($order->fresh());
            }

            $request->session()->forget('cart');
            $request->session()->forget(['gift_card', 'loyalty']);
            return redirect()->route('orders.show', $order)->with('status', 'Stripe payment succeeded.');
        }

        $order->update(['payment_failure_reason' => 'Stripe payment was not confirmed.']);
        PaymentEvent::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'event_type' => 'payment_not_confirmed',
            'status' => 'failed',
            'reference' => $sessionId,
        ]);

        return redirect()->route('orders.show', $order)->with('status', 'Stripe payment was not confirmed.');
    }

    public function stripeCancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        AuditLogger::log($request->user(), 'order.payment.cancelled.stripe', $order);
        $order->update(['payment_failure_reason' => 'Customer cancelled Stripe checkout.']);
        PaymentEvent::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'event_type' => 'checkout_cancelled',
            'status' => 'cancelled',
        ]);
        User::query()->where('is_admin', true)->get()->each(
            fn (User $admin) => $admin->notify(new OrderEventNotification($order, 'Stripe payment cancelled: '.$order->order_number))
        );

        $this->clearReservations((int) $order->id);

        return redirect()->route('orders.show', $order)->with('status', 'Payment was cancelled; the order is still pending.');
    }

    public function stripeWebhook(Request $request)
    {
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '') {
            return response()->json(['message' => 'Webhook secret missing'], 400);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret
            );
        } catch (SignatureVerificationException $exception) {
            Log::warning('Stripe webhook signature invalid', ['message' => $exception->getMessage()]);
            return response()->json(['message' => 'Invalid signature'], 400);
        } catch (\Throwable $exception) {
            Log::warning('Stripe webhook parse failed', ['message' => $exception->getMessage()]);
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $expectedLiveMode = config('services.stripe.webhook_live_mode');
        if ($expectedLiveMode !== null && (bool) $event->livemode !== filter_var($expectedLiveMode, FILTER_VALIDATE_BOOL)) {
            Log::warning('Stripe webhook livemode mismatch', [
                'event_type' => $event->type,
                'event_livemode' => (bool) $event->livemode,
            ]);

            return response()->json(['message' => 'Livemode mismatch'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = (int) ($session->metadata->order_id ?? 0);
            $orderNumber = (string) ($session->metadata->order_number ?? '');

            if ($orderId > 0) {
                try {
                    DB::transaction(function () use ($orderId, $orderNumber, $session): void {
                        $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();
                        if ($order === null || $order->payment_status === 'paid') {
                            return;
                        }

                        if ($orderNumber !== '' && $order->order_number !== $orderNumber) {
                            throw new \Exception('Stripe metadata order number mismatch.');
                        }

                        $order->load('items');
                        $lines = $this->buildOrderLines($order);
                        $this->ensureStock($lines, true, (int) $order->id);

                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'transaction_ref' => (string) ($session->payment_intent ?? $session->id),
                            'payment_failure_reason' => null,
                        ]);

                        PaymentEvent::query()->create([
                            'order_id' => $order->id,
                            'provider' => 'stripe',
                            'event_type' => 'checkout.session.completed',
                            'status' => 'paid',
                            'reference' => (string) ($session->payment_intent ?? $session->id),
                            'payload' => [
                                'livemode' => (bool) $session->livemode,
                                'payment_status' => (string) $session->payment_status,
                            ],
                        ]);

                        $this->finalizeCredits($order, $order->user);
                        $this->clearReservations((int) $order->id);

                        $this->incrementCouponUsage($order->coupon_code);

                        OrderStatusLog::query()->create([
                            'order_id' => $order->id,
                            'user_id' => null,
                            'old_status' => 'pending_payment',
                            'new_status' => 'processing',
                            'old_payment_status' => 'pending',
                            'new_payment_status' => 'paid',
                            'note' => 'Stripe webhook payment confirmation',
                        ]);

                        $this->sendInvoiceIfNeeded($order);
                    });
                } catch (\Throwable $exception) {
                    Log::warning('Stripe webhook stock validation failed', ['message' => $exception->getMessage()]);
                    return response()->json(['message' => 'Stock validation failed'], 409);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    private function startStripePayment(Request $request, Order $order): RedirectResponse
    {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            return redirect()->route('orders.show', $order)->with('status', 'Stripe key is not set in .env.');
        }

        Stripe::setApiKey($secret);

        $subTotal = (float) $order->sub_total;
        $discount = (float) $order->discount_amount;
        $taxAmount = (float) $order->tax_amount;
        $discountRatio = $subTotal > 0 ? max(0, ($subTotal - $discount) / $subTotal) : 1;

        $lineItems = $order->items->map(function ($item) use ($discountRatio): array {
            $unitAmount = (float) $item->unit_price * 100;
            $unitAmount = (int) round($unitAmount * $discountRatio);
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product_name,
                    ],
                    'unit_amount' => max(1, $unitAmount),
                ],
                'quantity' => (int) $item->quantity,
            ];
        })->values()->all();

        if ((float) $order->shipping_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount' => (int) round((float) $order->shipping_amount * 100),
                ],
                'quantity' => 1,
            ];
        }

        if ($taxAmount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Tax'],
                    'unit_amount' => (int) round($taxAmount * 100),
                ],
                'quantity' => 1,
            ];
        }

        $currentTotal = collect($lineItems)->sum(function (array $item): int {
            return (int) $item['price_data']['unit_amount'] * (int) $item['quantity'];
        });

        $desiredTotal = (int) round((float) $order->grand_total * 100);
        $ratio = $currentTotal > 0 ? min(1, $desiredTotal / $currentTotal) : 1;

        if ($ratio < 1) {
            $lineItems = collect($lineItems)->map(function (array $item) use ($ratio): array {
                $unitAmount = (int) $item['price_data']['unit_amount'];
                $unitAmount = (int) round($unitAmount * $ratio);
                $item['price_data']['unit_amount'] = max(1, $unitAmount);
                return $item;
            })->values()->all();
        }

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => route('checkout.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.stripe.cancel', $order),
            'customer_email' => $order->customer_email,
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
            'line_items' => $lineItems,
        ]);

        PaymentEvent::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'event_type' => 'checkout_session_created',
            'status' => 'pending',
        ]);

        return redirect()->away((string) $session->url);
    }


    private function cartData(Request $request): array
    {
        $products = Product::query()->with('variants')->get()->keyBy('id');
        $cart = $request->session()->get('cart', []);

        $items = collect($cart)
            ->map(function (int $qty, string $key) use ($products): ?array {
                [$id, $variantId] = $this->parseCartKey($key);
                $product = $products->get($id);

                if ($product === null) {
                    return null;
                }

                $variant = $variantId !== null ? $product->variants->firstWhere('id', $variantId) : null;
                $quantity = max(1, $qty);
                $unitPrice = (float) $product->price + (float) ($variant?->price_delta ?? 0);

                return [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                    'lineTotal' => $unitPrice * $quantity,
                ];
            })
            ->filter()
            ->values();

        $subTotal = $items->sum('lineTotal');
        $shippingKey = (string) $request->input('shipping_zone', $request->session()->get('shipping_zone', 'metro'));
        $shipping = $subTotal > 0 ? $this->shippingZone($shippingKey)['fee'] : 0;

        [$coupon, $discount] = $this->resolveCoupon($request, (float) $subTotal);
        $taxable = max(0, (float) $subTotal - $discount);
        $tax = round($taxable * (float) config('shop.tax_rate', 0.05), 2);
        $totalBeforeCredits = $taxable + $tax + $shipping;

        [$giftCard, $giftCardAmount] = $this->resolveGiftCard($request, $totalBeforeCredits);
        $remaining = max(0, $totalBeforeCredits - $giftCardAmount);
        [$loyaltyPoints, $loyaltyAmount] = $this->resolveLoyalty($request, $remaining);
        $grandTotal = max(0, round($remaining - $loyaltyAmount, 2));

        return [
            'items' => $items,
            'subTotal' => $subTotal,
            'discount' => $discount,
            'tax' => $tax,
            'coupon' => $coupon,
            'giftCard' => $giftCard,
            'giftCardAmount' => $giftCardAmount,
            'loyaltyPoints' => $loyaltyPoints,
            'loyaltyAmount' => $loyaltyAmount,
            'shipping' => $shipping,
            'grandTotal' => $grandTotal,
            'cartCount' => (int) collect($cart)->sum(),
        ];
    }

    private function parseCartKey(string $key): array
    {
        if (! str_contains($key, ':')) {
            return [$key, null];
        }

        [$productId, $variantId] = explode(':', $key, 2);

        return [$productId, $variantId !== '' ? (int) $variantId : null];
    }

    private function shippingZone(string $key): array
    {
        $zones = (array) config('shop.shipping_zones', []);
        if ($zones === []) {
            $zones = [
                'metro' => ['label' => 'Metro delivery', 'fee' => 7.99, 'eta_days' => 2],
            ];
        }

        return $zones[$key] ?? $zones['metro'];
    }

    private function resolveGiftCard(Request $request, float $total): array
    {
        $code = (string) $request->session()->get('gift_card.code', '');
        if ($code === '') {
            return [null, 0.0];
        }

        $card = GiftCard::query()->where('code', $code)->first();
        if ($card === null || ! $card->is_active || (float) $card->balance <= 0) {
            $request->session()->forget('gift_card');
            return [null, 0.0];
        }

        if ($card->expires_at && $card->expires_at->isPast()) {
            $request->session()->forget('gift_card');
            return [null, 0.0];
        }

        $amount = min((float) $card->balance, max(0, $total));

        return [$card, round($amount, 2)];
    }

    private function resolveLoyalty(Request $request, float $total): array
    {
        if (! $request->user()) {
            $request->session()->forget('loyalty');
            return [0, 0.0];
        }

        $points = (int) $request->session()->get('loyalty.points', 0);
        if ($points <= 0) {
            return [0, 0.0];
        }

        $available = (int) $request->user()->loyalty_points;
        $points = min($points, $available);

        $maxPointsForTotal = (int) floor($total / self::LOYALTY_POINT_VALUE);
        $points = min($points, $maxPointsForTotal);

        if ($points <= 0) {
            $request->session()->forget('loyalty');
            return [0, 0.0];
        }

        $amount = round($points * self::LOYALTY_POINT_VALUE, 2);

        return [$points, min($amount, $total)];
    }

    private function resolveCoupon(Request $request, float $subTotal): array
    {
        $code = (string) $request->session()->get('coupon.code', '');
        if ($code === '') {
            return [null, 0.0];
        }

        $coupon = Coupon::query()->where('code', $code)->first();
        if ($coupon === null || ! $coupon->isValidFor($subTotal)) {
            $request->session()->forget('coupon');
            return [null, 0.0];
        }

        $discount = (float) $coupon->amount;
        if ($coupon->type === 'percent') {
            $discount = round($subTotal * ($discount / 100), 2);
        }

        if ($subTotal <= 0) {
            $discount = 0;
        } else {
            $discount = min($discount, max(0, $subTotal - 0.01));
        }

        return [$coupon, $discount];
    }

    private function formatAddress(UserAddress $address): string
    {
        $parts = [
            $address->line1,
            $address->line2,
            $address->city,
            $address->state,
            $address->postal_code,
            $address->country,
        ];

        return implode(', ', array_filter($parts, fn ($value) => $value !== null && $value !== ''));
    }

    private function buildCartLines($items): array
    {
        return collect($items)
            ->map(fn ($item) => [
                'product_id' => $item['product']->id,
                'product_variant_id' => $item['variant']?->id,
                'product_name' => $item['product']->name,
                'variant_name' => $item['variant']?->name,
                'quantity' => $item['quantity'],
            ])
            ->values()
            ->all();
    }

    private function buildOrderLines(Order $order): array
    {
        return $order->items
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
            ])
            ->values()
            ->all();
    }

    private function ensureStock(array $lines, bool $decrement, ?int $ignoreOrderId): void
    {
        $now = now();
        StockReservation::query()->where('expires_at', '<=', $now)->delete();

        foreach ($lines as $line) {
            $variant = ! empty($line['product_variant_id'])
                ? ProductVariant::query()->whereKey($line['product_variant_id'])->lockForUpdate()->first()
                : null;
            $product = Product::query()->whereKey($line['product_id'])->lockForUpdate()->first();
            if ($product === null) {
                throw new \Exception('Product ' . $line['product_name'] . ' no longer exists.');
            }

            if ($variant !== null) {
                if ((int) $variant->stock < $line['quantity']) {
                    throw new \Exception('Variant ' . $variant->name . ' has insufficient stock.');
                }

                if ($decrement) {
                    $oldStock = (int) $variant->stock;
                    $variant->decrement('stock', $line['quantity']);
                    InventoryAdjustment::query()->create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'user_id' => auth()->id(),
                        'old_stock' => $oldStock,
                        'new_stock' => $oldStock - (int) $line['quantity'],
                        'delta' => -1 * (int) $line['quantity'],
                        'reason' => 'checkout',
                        'note' => 'Order stock decrement',
                    ]);
                    $this->notifyLowStock($product, $variant, $oldStock - (int) $line['quantity']);
                }

                continue;
            }

            if ($product->stock !== null) {
                $reserved = StockReservation::query()
                    ->where('product_id', $product->id)
                    ->where('expires_at', '>', $now)
                    ->when($ignoreOrderId !== null, function ($query) use ($ignoreOrderId): void {
                        $query->where('order_id', '!=', $ignoreOrderId);
                    })
                    ->sum('quantity');

                $available = (int) $product->stock - (int) $reserved;

                if ($available < $line['quantity']) {
                    throw new \Exception('Product ' . $product->name . ' has insufficient stock.');
                }

                if ($decrement) {
                    $oldStock = (int) $product->stock;
                    $product->decrement('stock', $line['quantity']);
                    InventoryAdjustment::query()->create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'old_stock' => $oldStock,
                        'new_stock' => $oldStock - (int) $line['quantity'],
                        'delta' => -1 * (int) $line['quantity'],
                        'reason' => 'checkout',
                        'note' => 'Order stock decrement',
                    ]);
                    $this->notifyLowStock($product, null, $oldStock - (int) $line['quantity']);
                }
            }
        }
    }

    private function notifyLowStock(Product $product, ?ProductVariant $variant, int $remaining): void
    {
        if ($remaining > (int) config('shop.low_stock_threshold', 5)) {
            return;
        }

        $label = $product->name.($variant ? ' / '.$variant->name : '');

        User::query()
            ->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_INVENTORY_ADMIN])
            ->get()
            ->each(fn (User $admin) => $admin->notify(new AdminAlertNotification(
                'Low stock alert: '.$label.' has '.$remaining.' left.',
                route('admin.inventory.index')
            )));
    }

    private function reserveStock(array $lines, int $orderId): void
    {
        $expiresAt = now()->addMinutes(30);

        StockReservation::query()->where('order_id', $orderId)->delete();

        foreach ($lines as $line) {
            StockReservation::query()->create([
                'order_id' => $orderId,
                'product_id' => $line['product_id'],
                'quantity' => (int) $line['quantity'],
                'expires_at' => $expiresAt,
            ]);
        }
    }

    private function clearReservations(int $orderId): void
    {
        StockReservation::query()->where('order_id', $orderId)->delete();
    }

    private function incrementCouponUsage(?string $code): void
    {
        if ($code === null || $code === '') {
            return;
        }

        Coupon::query()->where('code', $code)->increment('used_count');
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }

    private function sendInvoiceIfNeeded(Order $order): void
    {
        if ($order->invoice_sent_at !== null) {
            return;
        }

        $order->loadMissing('items');

        try {
            Mail::to($order->customer_email)->send(new OrderInvoiceMail($order));
            $order->forceFill([
                'invoice_sent_at' => now(),
            ])->save();
        } catch (\Throwable $exception) {
            Log::warning('Invoice email sending failed', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function finalizeCredits(Order $order, ?User $user): void
    {
        if ($order->gift_card_code && (float) $order->gift_card_amount > 0) {
            GiftCard::query()
                ->where('code', $order->gift_card_code)
                ->lockForUpdate()
                ->first()
                ?->decrement('balance', (float) $order->gift_card_amount);
        }

        if ($user !== null && (int) $order->loyalty_points_used > 0) {
            User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->first()
                ?->decrement('loyalty_points', (int) $order->loyalty_points_used);
        }
    }
}
