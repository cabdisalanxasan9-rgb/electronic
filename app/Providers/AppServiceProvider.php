<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(8));

        RateLimiter::for('checkout', function (Request $request): array {
            return [
                Limit::perMinute(8)->by((string) ($request->user()?->id ?? $request->ip())),
            ];
        });

        RateLimiter::for('stripe-webhook', function (Request $request): array {
            return [
                Limit::perMinute(120)->by((string) $request->ip()),
            ];
        });

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());
    }
}
