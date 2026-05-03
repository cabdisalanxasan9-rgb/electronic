<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function apply(Request $request): RedirectResponse
    {
        $request->validate([
            'points' => ['required', 'integer', 'min:0'],
        ]);

        $points = (int) $request->input('points');
        $available = (int) $request->user()->loyalty_points;

        $points = min($points, $available);

        $request->session()->put('loyalty', [
            'points' => $points,
        ]);

        return back()->with('status', 'Loyalty points applied.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->session()->forget('loyalty');

        return back()->with('status', 'Loyalty points removed.');
    }
}
