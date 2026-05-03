<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponAdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $coupons = Coupon::query()->latest()->get();

        return view('admin.coupons.index', [
            'coupons' => $coupons,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', 'in:fixed'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim((string) $validated['code']));
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        Coupon::query()->create($validated);

        return back()->with('status', 'Coupon created.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        abort_unless(request()->user()->canManageOrders(), 403);

        $coupon->delete();

        return back()->with('status', 'Coupon deleted.');
    }
}
