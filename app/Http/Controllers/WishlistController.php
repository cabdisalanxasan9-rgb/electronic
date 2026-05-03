<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $items = Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->with('product')
            ->latest()
            ->get();

        return view('shop.wishlist', [
            'items' => $items,
            'cartCount' => (int) collect($request->session()->get('cart', []))->sum(),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return back()->with('status', 'Added to wishlist.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('status', 'Removed from wishlist.');
    }
}
