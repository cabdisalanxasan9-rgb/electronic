<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', 'all');
        $sort = (string) $request->query('sort', 'featured');
        $minPrice = $request->query('price_min');
        $maxPrice = $request->query('price_max');
        $ratingMin = $request->query('rating_min');
        $inStock = (bool) $request->boolean('in_stock');

        if (! $this->productsTableReady()) {
            return view('shop.index', [
                'products' => collect(),
                'categories' => collect(),
                'activeCategory' => $category,
                'search' => $search,
                'sort' => $sort,
                'filters' => [
                    'price_min' => $minPrice,
                    'price_max' => $maxPrice,
                    'rating_min' => $ratingMin,
                    'in_stock' => $inStock,
                ],
                'cartCount' => $this->cartCount($request),
            ]);
        }

        $productsQuery = Product::query();

        if ($category !== 'all') {
            $cat = Category::where('slug', $category)->first();
            if ($cat) {
                $productsQuery->where('category_id', $cat->id);
            } else {
                $productsQuery->where('category', $category);
            }
        }

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($minPrice !== null && $minPrice !== '') {
            $productsQuery->where('price', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '') {
            $productsQuery->where('price', '<=', (float) $maxPrice);
        }

        if ($ratingMin !== null && $ratingMin !== '') {
            $productsQuery->where('rating', '>=', (float) $ratingMin);
        }

        if ($inStock) {
            $productsQuery->where('stock', '>', 0);
        }

        match ($sort) {
            'price_low' => $productsQuery->orderBy('price'),
            'price_high' => $productsQuery->orderByDesc('price'),
            'rating' => $productsQuery->orderByDesc('rating'),
            default => $productsQuery->orderByDesc('featured')->orderByDesc('rating'),
        };

        $products = $productsQuery->get();

        $recentIds = (array) $request->session()->get('recently_viewed', []);
        $recentProducts = $recentIds === []
            ? collect()
            : Product::query()->whereIn('id', $recentIds)->get()->sortBy(function ($product) use ($recentIds) {
                return array_search($product->id, $recentIds, true);
            })->values();

        return view('shop.index', [
            'products' => $products,
            'categories' => Category::all(),
            'activeCategory' => $category,
            'search' => $search,
            'sort' => $sort,
            'filters' => [
                'price_min' => $minPrice,
                'price_max' => $maxPrice,
                'rating_min' => $ratingMin,
                'in_stock' => $inStock,
            ],
            'recentProducts' => $recentProducts,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function cart(Request $request): View
    {
        if (! $this->productsTableReady()) {
            return view('shop.cart', [
                'items' => collect(),
                'subTotal' => 0,
                'discount' => 0,
                'tax' => 0,
                'coupon' => null,
                'shipping' => 0,
                'grandTotal' => 0,
                'cartCount' => $this->cartCount($request),
            ]);
        }

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
                    'key' => $key,
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
        $shipping = $subTotal > 0 ? 7.99 : 0;

        [$coupon, $discount] = $this->resolveCoupon($request, (float) $subTotal);
        $taxable = max(0, (float) $subTotal - $discount);
        $tax = round($taxable * (float) config('shop.tax_rate', 0.05), 2);
        $grandTotal = $taxable + $tax + $shipping;

        return view('shop.cart', [
            'items' => $items,
            'subTotal' => $subTotal,
            'discount' => $discount,
            'tax' => $tax,
            'coupon' => $coupon,
            'shipping' => $shipping,
            'grandTotal' => $grandTotal,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function product(Request $request, Product $product): View
    {
        $product->load(['images', 'variants']);
        $this->rememberRecentlyViewed($request, $product->id);

        $reviews = $product->reviews()
            ->where('is_visible', true)
            ->with('user')
            ->latest()
            ->get();

        $userReview = null;
        if ($request->user()) {
            $userReview = $product->reviews()
                ->where('user_id', $request->user()->id)
                ->first();
        }

        $related = Product::query()
            ->where('category', $product->category)
            ->whereKeyNot($product->id)
            ->limit(4)
            ->get();

        return view('shop.product', [
            'product' => $product,
            'reviews' => $reviews,
            'userReview' => $userReview,
            'related' => $related,
            'cartCount' => $this->cartCount($request),
        ]);
    }

    private function rememberRecentlyViewed(Request $request, string $productId): void
    {
        $items = (array) $request->session()->get('recently_viewed', []);

        $items = array_values(array_filter($items, fn ($id) => $id !== $productId));
        array_unshift($items, $productId);

        $items = array_slice($items, 0, 6);

        $request->session()->put('recently_viewed', $items);
    }

    public function sitemap(): Response
    {
        $products = $this->productsTableReady()
            ? Product::query()->get(['id', 'updated_at'])
            : collect();

        $content = view('sitemap', [
            'products' => $products,
        ])->render();

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }

    public function addToCart(Request $request, string $id): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $quantity = max(1, min(5, $quantity));

        $product = Product::query()->with('variants')->whereKey($id)->first();

        if ($product === null) {
            return back()->with('status', 'That product was not found.');
        }

        $cart = $request->session()->get('cart', []);
        $variantId = $request->integer('variant_id') ?: null;
        $variant = $variantId !== null ? $product->variants->firstWhere('id', $variantId) : null;
        $cartKey = $variant !== null ? $id.':'.$variant->id : $id;
        $currentQuantity = (int) ($cart[$cartKey] ?? 0);
        $requestedQuantity = $currentQuantity + $quantity;

        if ($variant !== null && $requestedQuantity > (int) $variant->stock) {
            return back()->with('status', 'Only '.$variant->stock.' units of '.$product->name.' / '.$variant->name.' are available.');
        }

        if ($variant === null && $product->stock !== null && $requestedQuantity > (int) $product->stock) {
            return back()->with('status', 'Only '.$product->stock.' units of '.$product->name.' are available.');
        }

        $cart[$cartKey] = $requestedQuantity;
        $request->session()->put('cart', $cart);

        return redirect()->route('shop.cart')->with('status', 'Product added to your cart.');
    }

    public function removeFromCart(Request $request, string $id): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);

        $id = urldecode($id);

        if (array_key_exists($id, $cart)) {
            unset($cart[$id]);
            $request->session()->put('cart', $cart);
        }

        return back()->with('status', 'Product removed from your cart.');
    }

    public function clearCart(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with('status', 'Cart cleared.');
    }

    private function cartCount(Request $request): int
    {
        return (int) collect($request->session()->get('cart', []))->sum();
    }

    private function parseCartKey(string $key): array
    {
        if (! str_contains($key, ':')) {
            return [$key, null];
        }

        [$productId, $variantId] = explode(':', $key, 2);

        return [$productId, $variantId !== '' ? (int) $variantId : null];
    }

    private function productsTableReady(): bool
    {
        return Schema::hasTable('products');
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
}
