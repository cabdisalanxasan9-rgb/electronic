<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $hasOrder = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id);
            })
            ->exists();

        if (! $hasOrder) {
            return back()->with('status', 'You can only review products you purchased.');
        }

        ProductReview::query()->updateOrCreate([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
        ], [
            'rating' => (int) $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
            'is_visible' => true,
        ]);

        $average = (float) ProductReview::query()
            ->where('product_id', $product->id)
            ->where('is_visible', true)
            ->avg('rating');

        $product->update(['rating' => round($average, 2)]);

        return back()->with('status', 'Review saved.');
    }
}
