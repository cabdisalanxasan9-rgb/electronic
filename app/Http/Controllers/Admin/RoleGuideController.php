<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleGuideController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageUsers(), 403);

        return view('admin.roles.index');
    }
}
