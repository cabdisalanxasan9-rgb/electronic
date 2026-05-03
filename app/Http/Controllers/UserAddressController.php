<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = UserAddress::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('profile.addresses', [
            'addresses' => $addresses,
            'cartCount' => (int) collect($request->session()->get('cart', []))->sum(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $validated['user_id'] = $request->user()->id;

        if (! empty($validated['is_default'])) {
            UserAddress::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        UserAddress::query()->create($validated);

        return back()->with('status', 'Address saved.');
    }

    public function update(Request $request, UserAddress $address): RedirectResponse
    {
        $this->authorizeAddress($request, $address);

        $validated = $this->validateAddress($request);

        if (! empty($validated['is_default'])) {
            UserAddress::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('status', 'Address updated.');
    }

    public function destroy(Request $request, UserAddress $address): RedirectResponse
    {
        $this->authorizeAddress($request, $address);
        $address->delete();

        return back()->with('status', 'Address deleted.');
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'line1' => ['required', 'string', 'max:180'],
            'line2' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:120'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function authorizeAddress(Request $request, UserAddress $address): void
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 403);
    }
}
