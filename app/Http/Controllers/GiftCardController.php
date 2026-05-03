<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function apply(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:80'],
        ]);

        $code = strtoupper(trim((string) $request->input('code')));
        $card = GiftCard::query()->where('code', $code)->first();

        if ($card === null || ! $card->is_active) {
            return back()->with('status', 'Gift card not found.');
        }

        if ($card->expires_at && $card->expires_at->isPast()) {
            return back()->with('status', 'Gift card has expired.');
        }

        $request->session()->put('gift_card', [
            'code' => $card->code,
        ]);

        return back()->with('status', 'Gift card applied.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->session()->forget('gift_card');

        return back()->with('status', 'Gift card removed.');
    }
}
