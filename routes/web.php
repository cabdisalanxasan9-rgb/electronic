<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\Admin\GiftCardAdminController;
use App\Http\Controllers\Admin\InventoryAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnAdminController;
use App\Http\Controllers\Admin\RoleGuideController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\BrandAdminController;
use App\Http\Controllers\Admin\PageAdminController;
use App\Http\Controllers\Admin\ContactAdminController;
use App\Http\Controllers\Admin\ProductImageAdminController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'index'])->name('shop.home');
Route::get('/sitemap.xml', [ShopController::class, 'sitemap'])->name('shop.sitemap');
Route::get('/products/{product}', [ShopController::class, 'product'])->name('shop.product');
Route::get('/cart', [ShopController::class, 'cart'])->name('shop.cart');
Route::post('/cart/{id}', [ShopController::class, 'addToCart'])->name('shop.cart.add');
Route::delete('/cart/{id}', [ShopController::class, 'removeFromCart'])->where('id', '.*')->name('shop.cart.remove');
Route::delete('/cart', [ShopController::class, 'clearCart'])->name('shop.cart.clear');
Route::post('/payments/stripe/webhook', [CheckoutController::class, 'stripeWebhook'])
	->withoutMiddleware(ValidateCsrfToken::class)
	->middleware('throttle:stripe-webhook')
	->name('checkout.stripe.webhook');

Route::middleware('auth')->group(function (): void {
	Route::post('/cart/coupon', [\App\Http\Controllers\CouponController::class, 'apply'])->name('cart.coupon.apply');
	Route::delete('/cart/coupon', [\App\Http\Controllers\CouponController::class, 'remove'])->name('cart.coupon.remove');
	Route::post('/cart/gift-card', [GiftCardController::class, 'apply'])->name('cart.gift-card.apply');
	Route::delete('/cart/gift-card', [GiftCardController::class, 'remove'])->name('cart.gift-card.remove');
	Route::post('/cart/loyalty', [LoyaltyController::class, 'apply'])->name('cart.loyalty.apply');
	Route::delete('/cart/loyalty', [LoyaltyController::class, 'remove'])->name('cart.loyalty.remove');

	Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
	Route::post('/checkout', [CheckoutController::class, 'store'])
		->middleware('throttle:checkout')
		->name('checkout.store');
	Route::get('/checkout/stripe/success/{order}', [CheckoutController::class, 'stripeSuccess'])->name('checkout.stripe.success');
	Route::get('/checkout/stripe/cancel/{order}', [CheckoutController::class, 'stripeCancel'])->name('checkout.stripe.cancel');

	Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
	Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
	Route::post('/orders/{order}/returns', [\App\Http\Controllers\OrderReturnController::class, 'store'])->name('orders.returns.store');

	Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
	Route::post('/wishlist/{product}', [\App\Http\Controllers\WishlistController::class, 'store'])->name('wishlist.store');
	Route::delete('/wishlist/{product}', [\App\Http\Controllers\WishlistController::class, 'destroy'])->name('wishlist.destroy');

	Route::post('/products/{product}/reviews', [\App\Http\Controllers\ProductReviewController::class, 'store'])->name('products.reviews.store');

	Route::get('/dashboard', function () {
		if (request()->user()?->is_admin) {
			return redirect()->route('admin.dashboard');
		}

		return view('dashboard');
	})->name('dashboard');

	Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
		Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

		Route::get('coupons', [CouponAdminController::class, 'index'])->name('coupons.index');
		Route::post('coupons', [CouponAdminController::class, 'store'])->name('coupons.store');
		Route::delete('coupons/{coupon}', [CouponAdminController::class, 'destroy'])->name('coupons.destroy');

		Route::get('gift-cards', [GiftCardAdminController::class, 'index'])->name('gift-cards.index');
		Route::post('gift-cards', [GiftCardAdminController::class, 'store'])->name('gift-cards.store');
		Route::patch('gift-cards/{giftCard}', [GiftCardAdminController::class, 'update'])->name('gift-cards.update');

		Route::resource('products', ProductAdminController::class)->except(['show']);
		Route::resource('categories', CategoryAdminController::class)->except(['show']);
		Route::resource('brands', BrandAdminController::class)->except(['show']);
		Route::resource('pages', PageAdminController::class)->except(['show']);
		Route::resource('contacts', ContactAdminController::class)->only(['index', 'show', 'update', 'destroy']);
		Route::resource('product-images', ProductImageAdminController::class)->only(['store', 'destroy']);
		Route::patch('products/{id}/restore', [ProductAdminController::class, 'restore'])->name('products.restore');
		Route::get('inventory/history', [InventoryAdminController::class, 'index'])->name('inventory.index');

		Route::get('orders', [OrderAdminController::class, 'index'])->name('orders.index');
		Route::get('orders/create', [OrderAdminController::class, 'create'])->name('orders.create');
		Route::post('orders', [OrderAdminController::class, 'store'])->name('orders.store');
		Route::get('orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
		Route::patch('orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');
		Route::patch('orders/{order}/delivered', [OrderAdminController::class, 'markDelivered'])->name('orders.delivered');
		Route::patch('orders/{order}/refund', [OrderAdminController::class, 'refund'])->name('orders.refund');
		Route::delete('orders/{order}', [OrderAdminController::class, 'destroy'])->name('orders.destroy');
		Route::get('orders/{order}/print', [OrderAdminController::class, 'printInvoice'])->name('orders.print');

		Route::get('returns', [ReturnAdminController::class, 'index'])->name('returns.index');
		Route::patch('returns/{return}', [ReturnAdminController::class, 'update'])->name('returns.update');

		Route::get('users', [UserAdminController::class, 'index'])->name('users.index');
		Route::patch('users/{user}/role', [UserAdminController::class, 'updateRole'])->name('users.role');
		Route::get('roles', [RoleGuideController::class, 'index'])->name('roles.index');
		Route::get('security', [SecurityController::class, 'index'])->name('security.index');

		Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
		Route::get('reports/orders.csv', [ReportController::class, 'exportCsv'])->name('reports.orders.csv');
		Route::get('reports/orders.pdf', [ReportController::class, 'exportPdf'])->name('reports.orders.pdf');
	});

	Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
	Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

	Route::get('/profile/addresses', [\App\Http\Controllers\UserAddressController::class, 'index'])->name('profile.addresses');
	Route::post('/profile/addresses', [\App\Http\Controllers\UserAddressController::class, 'store'])->name('profile.addresses.store');
	Route::patch('/profile/addresses/{address}', [\App\Http\Controllers\UserAddressController::class, 'update'])->name('profile.addresses.update');
	Route::delete('/profile/addresses/{address}', [\App\Http\Controllers\UserAddressController::class, 'destroy'])->name('profile.addresses.destroy');
});

require __DIR__.'/auth.php';
