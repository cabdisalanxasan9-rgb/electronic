<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductAdminController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', 'all');
        $trashed = (string) $request->query('trashed', 'without');

        $query = Product::query();

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        if ($q !== '') {
            $query->where(function ($inner) use ($q): void {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return view('admin.products.index', [
            'products' => $query->latest()->paginate(20)->withQueryString(),
            'categories' => Product::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
            'q' => $q,
            'activeCategory' => $category,
            'trashed' => $trashed,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        return view('admin.products.create', [
            'categories' => Product::query()->select('category')->distinct()->orderBy('category')->pluck('category'), // legacy
            'dbCategories' => Category::orderBy('name')->get(),
            'dbBrands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $this->validateData($request, true);
        $validated['featured'] = $request->boolean('featured');
        unset($validated['variants_text'], $validated['stock_reason']);

        $validated['image'] = '';

        if ($request->hasFile('image_file')) {
            $validated['image_path'] = $request->file('image_file')->store('products', 'public');
        }

        Product::query()->create($validated);
        $product = Product::query()->findOrFail($validated['id']);
        $this->syncVariants($product, (string) $request->input('variants_text', ''));
        $this->logInventoryChange($product, 0, (int) $product->stock, 'initial stock', 'Product created');

        AuditLogger::log(Auth::user(), 'product.created', $product, [
            'name' => $validated['name'],
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Product::query()->select('category')->distinct()->orderBy('category')->pluck('category'), // legacy
            'dbCategories' => Category::orderBy('name')->get(),
            'dbBrands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $this->validateData($request, false, $product->id);
        $validated['featured'] = $request->boolean('featured');
        unset($validated['image'], $validated['variants_text'], $validated['stock_reason']);
        $removeImage = (bool) ($validated['remove_image'] ?? false);
        unset($validated['remove_image']);

        if ($removeImage && ! $request->hasFile('image_file') && ! empty($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image_file')) {
            if (! empty($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $request->file('image_file')->store('products', 'public');
        }

        $oldStock = (int) $product->stock;
        $product->update($validated);
        $this->syncVariants($product, (string) $request->input('variants_text', ''));

        if ($oldStock !== (int) $product->stock) {
            $this->logInventoryChange(
                $product,
                $oldStock,
                (int) $product->stock,
                (string) $request->input('stock_reason', 'manual adjustment'),
                'Product stock edited'
            );
        }

        AuditLogger::log(Auth::user(), 'product.updated', $product, [
            'name' => $product->name,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        if (! empty($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
            $product->image_path = null;
            $product->save();
        }

        $product->delete();

        AuditLogger::log(Auth::user(), 'product.deleted', $product, [
            'name' => $product->name,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    public function restore(string $id): RedirectResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $product);

        $product->restore();

        AuditLogger::log(Auth::user(), 'product.restored', $product, [
            'name' => $product->name,
        ]);

        return redirect()->route('admin.products.index', ['trashed' => 'only'])->with('status', 'Product restored.');
    }

    private function validateData(Request $request, bool $isCreate, ?string $currentId = null): array
    {
        $idRule = 'required|string|max:120|unique:products,id';
        $imageFileRules = $isCreate
            ? ['required', 'image', 'max:2048']
            : ['nullable', 'image', 'max:2048'];

        if (! $isCreate && $currentId !== null) {
            $idRule = 'required|string|max:120|unique:products,id,'.$currentId.',id';
        }

        $validated = Validator::make($request->all(), [
            'id' => $idRule,
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'rating' => ['required', 'numeric', 'between:0,5'],
            'featured' => ['nullable', 'boolean'],
            'badge' => ['nullable', 'string', 'max:60'],
            'description' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'image' => ['nullable', 'string', 'max:1000'],
            'image_file' => $imageFileRules,
            'remove_image' => ['nullable', 'boolean'],
            'variants_text' => ['nullable', 'string', 'max:3000'],
            'stock_reason' => ['nullable', 'string', 'max:180'],
        ], [
            'id.required' => 'Product ID is required.',
            'id.unique' => 'This ID is already in use.',
            'name.required' => 'Product name is required.',
            'category.required' => 'Category is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'rating.between' => 'Rating must be between 0 and 5.',
            'description.required' => 'Description is required.',
            'image_file.required' => 'Product image is required when creating a product.',
            'image_file.image' => 'The uploaded file must be a valid image.',
            'image_file.max' => 'The image must be smaller than 2MB.',
        ])->validate();

        return $validated;
    }

    private function syncVariants(Product $product, string $variantsText): void
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', $variantsText))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        $seenIds = [];

        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', $line));
            $name = (string) ($parts[0] ?? '');

            if ($name === '') {
                continue;
            }

            $sku = (string) ($parts[1] ?? '');
            $priceDelta = (float) ($parts[2] ?? 0);
            $stock = max(0, (int) ($parts[3] ?? 0));

            $variant = ProductVariant::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'name' => $name,
                    'sku' => $sku !== '' ? $sku : null,
                ],
                [
                    'price_delta' => $priceDelta,
                    'stock' => $stock,
                    'is_active' => true,
                ]
            );

            $seenIds[] = $variant->id;
        }

        $product->variants()
            ->when($seenIds !== [], fn ($query) => $query->whereNotIn('id', $seenIds))
            ->update(['is_active' => false]);
    }

    private function logInventoryChange(Product $product, int $oldStock, int $newStock, string $reason, ?string $note = null): void
    {
        InventoryAdjustment::query()->create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'delta' => $newStock - $oldStock,
            'reason' => $reason !== '' ? $reason : 'manual adjustment',
            'note' => $note,
        ]);
    }
}
