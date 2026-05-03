<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftCardAdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $cards = GiftCard::query()->latest()->paginate(20);

        return view('admin.gift-cards.index', [
            'cards' => $cards,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:80', 'unique:gift_cards,code'],
            'initial_balance' => ['required', 'numeric', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim((string) $validated['code']));
        $validated['balance'] = $validated['initial_balance'];
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        GiftCard::query()->create($validated);

        return back()->with('status', 'Gift card created.');
    }

    public function update(Request $request, GiftCard $giftCard): RedirectResponse
    {
        abort_unless($request->user()->canManageOrders(), 403);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $giftCard->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return back()->with('status', 'Gift card updated.');
    }
}
