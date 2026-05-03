<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryAdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageProducts(), 403);

        return view('admin.inventory.index', [
            'adjustments' => InventoryAdjustment::query()
                ->with(['product', 'variant', 'user'])
                ->latest()
                ->paginate(30),
        ]);
    }
}
