<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageAdminController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'image' => 'required|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('product-gallery', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        ProductImage::create($validated);

        return back()->with('status', 'Gallery image added.');
    }

    public function destroy(ProductImage $productImage)
    {
        if ($productImage->image_path) {
            Storage::disk('public')->delete($productImage->image_path);
        }

        $productImage->delete();

        return back()->with('status', 'Gallery image removed.');
    }
}
