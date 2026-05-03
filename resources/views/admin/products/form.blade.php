<form method="POST" action="{{ $action }}" class="summary product-form" enctype="multipart/form-data" data-draft-form="product-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="form-error-box" role="alert">
            <strong>Please fix the errors below.</strong>
        </div>
    @endif

    <div class="product-form-layout">
        <div class="product-form-main">
            <div class="product-form-grid">
                <div>
                    <label>ID</label>
                    <input type="text" name="id" value="{{ old('id', $product?->id) }}" required>
                    @error('id')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $product?->name) }}" required>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">-- Select Category --</option>
                        @foreach ($dbCategories ?? [] as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product?->category_id) == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
                    
                    <input type="hidden" name="category" value="{{ old('category', $product?->category ?? 'legacy') }}">
                </div>

                <div>
                    <label>Brand</label>
                    <select name="brand_id">
                        <option value="">-- Select Brand --</option>
                        @foreach ($dbBrands ?? [] as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $product?->brand_id) == $brand->id)>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label>Badge</label>
                    <input type="text" name="badge" value="{{ old('badge', $product?->badge) }}">
                    @error('badge')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label>Stock</label>
                    <input type="number" name="stock" min="0" value="{{ old('stock', $product?->stock) }}" required>
                    @error('stock')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product?->price) }}" required>
                    @error('price')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label>Rating (0-5)</label>
                    <input type="number" step="0.01" min="0" max="5" name="rating" value="{{ old('rating', $product?->rating) }}" required>
                    @error('rating')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <label>Description</label>
            <textarea rows="5" name="description" required>{{ old('description', $product?->description) }}</textarea>
            @error('description')<p class="field-error">{{ $message }}</p>@enderror

            <label>SEO Title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $product?->meta_title) }}" maxlength="180">
            @error('meta_title')<p class="field-error">{{ $message }}</p>@enderror

            <label>SEO Description</label>
            <textarea rows="3" name="meta_description">{{ old('meta_description', $product?->meta_description) }}</textarea>
            @error('meta_description')<p class="field-error">{{ $message }}</p>@enderror

            <label>Variants</label>
            <textarea rows="4" name="variants_text" placeholder="Color: Black | SKU-BLK | 0 | 10">{{ old('variants_text', isset($product) && $product ? $product->variants->map(fn ($variant) => $variant->name.' | '.$variant->sku.' | '.$variant->price_delta.' | '.$variant->stock)->implode("\n") : '') }}</textarea>
            <p class="empty">One per line: name | sku | price delta | stock.</p>
            @error('variants_text')<p class="field-error">{{ $message }}</p>@enderror

            @if ($product)
                <label>Stock Change Reason</label>
                <input type="text" name="stock_reason" value="{{ old('stock_reason') }}" placeholder="Restock, correction, damaged item...">
                @error('stock_reason')<p class="field-error">{{ $message }}</p>@enderror
            @endif

            <label class="product-check-row">
                <input type="checkbox" name="featured" value="1" @checked(old('featured', $product?->featured))>
                Featured Product
            </label>
        </div>

        <aside class="product-form-side">
            <div class="product-image-panel">
                <p class="product-image-title">Primary Image</p>
                <p class="product-image-note">{{ $product ? 'Choose a new image to replace the current one.' : 'Upload a product image (required).' }}</p>

                <label>Image Upload {{ $product ? '(optional)' : '(required)' }}</label>
                <input type="file" name="image_file" accept="image/*" data-image-input="#product-live-preview">
                @error('image_file')<p class="field-error">{{ $message }}</p>@enderror

                @if ($product)
                    <p class="product-image-current-label">Current Image</p>
                    <img src="{{ $product->display_image }}" alt="{{ $product->name }}" class="product-image-preview" id="product-live-preview" loading="lazy" decoding="async">

                    <label class="product-check-row" style="margin-top:10px;">
                        <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>
                        Remove current image
                    </label>
                    @error('remove_image')<p class="field-error">{{ $message }}</p>@enderror
                @else
                    <img src="" alt="Preview" class="product-image-preview" id="product-live-preview" style="display:none;" loading="lazy" decoding="async">
                @endif
            </div>

            @if ($product)
            <div class="product-image-panel section-space-top">
                <p class="product-image-title">Gallery Images</p>
                <p class="product-image-note">Upload additional images for this product.</p>
                
                <div style="margin-top: 1rem;">
                    @foreach($product->images as $galleryImage)
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; padding: 0.5rem; background: var(--panel-bg); border: 1px solid var(--border); border-radius: 4px;">
                            <img src="{{ Storage::url($galleryImage->image_path) }}" alt="Gallery image" style="width: 50px; height: 50px; object-fit: cover;">
                            <button type="button" class="btn-danger" style="padding: 0.2rem 0.5rem;" onclick="event.preventDefault(); document.getElementById('delete-gallery-{{ $galleryImage->id }}').submit();">Delete</button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>

    <button type="submit" class="btn-primary product-save-btn">{{ $saveLabel ?? 'Save Product' }}</button>
</form>

@if ($product)
    @foreach($product->images as $galleryImage)
        <form id="delete-gallery-{{ $galleryImage->id }}" action="{{ route('admin.product-images.destroy', $galleryImage) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <form method="POST" action="{{ route('admin.product-images.store') }}" enctype="multipart/form-data" class="summary section-space-top">
        @csrf
        <h3>Add Gallery Image</h3>
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        
        <label>Upload Image</label>
        <input type="file" name="image" accept="image/*" required>
        
        <label>Sort Order</label>
        <input type="number" name="sort_order" value="0">
        
        <button type="submit" class="btn-secondary" style="margin-top: 1rem;">Add Image to Gallery</button>
    </form>
@endif
