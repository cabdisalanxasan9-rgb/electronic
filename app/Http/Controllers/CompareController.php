<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    private const MAX_ITEMS = 4;

    public function index(Request $request): View
    {
        $ids = (array) $request->session()->get('compare', []);
        $products = $ids === []
            ? collect()
            : Product::query()->whereIn('id', $ids)->get()->sortBy(function ($product) use ($ids) {
                return array_search($product->id, $ids, true);
            })->values();

        return view('shop.compare', [
            'products' => $products,
            'cartCount' => (int) collect($request->session()->get('cart', []))->sum(),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $items = (array) $request->session()->get('compare', []);

        if (! in_array($product->id, $items, true)) {
            $items[] = $product->id;
        }

        $items = array_slice($items, -self::MAX_ITEMS);
        $request->session()->put('compare', $items);

        return back()->with('status', 'Added to compare.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $items = array_values(array_filter(
            (array) $request->session()->get('compare', []),
            fn ($id) => $id !== $product->id
        ));

        $request->session()->put('compare', $items);

        return back()->with('status', 'Removed from compare.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('compare');

        return back()->with('status', 'Compare list cleared.');
    }
}
