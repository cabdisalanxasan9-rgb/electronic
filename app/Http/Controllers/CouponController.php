<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim((string) $request->input('code')));
        $coupon = Coupon::query()->where('code', $code)->first();

        if ($coupon === null) {
            return back()->with('status', 'Coupon not found.');
        }

        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return back()->with('status', 'Your cart is empty.');
        }

        $products = Product::query()->get()->keyBy('id');
        $subTotal = collect($cart)
            ->map(function (int $qty, string $id) use ($products): ?float {
                $product = $products->get($id);
                if ($product === null) {
                    return null;
                }

                $quantity = max(1, $qty);

                return (float) $product->price * $quantity;
            })
            ->filter()
            ->sum();

        if (! $coupon->isValidFor((float) $subTotal)) {
            return back()->with('status', 'Coupon is not valid for this cart.');
        }

        $request->session()->put('coupon', [
            'code' => $coupon->code,
        ]);

        return back()->with('status', 'Coupon applied.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->session()->forget('coupon');

        return back()->with('status', 'Coupon removed.');
    }
}
