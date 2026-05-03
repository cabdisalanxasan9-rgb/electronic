<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageUsers(), 403);

        return view('admin.security.index', [
            'checks' => [
                'APP_DEBUG' => config('app.debug') ? 'Disable in production' : 'OK',
                'SESSION_SECURE_COOKIE' => config('session.secure') ? 'OK' : 'Enable behind HTTPS',
                'QUEUE_CONNECTION' => config('queue.default'),
                'MAIL_MAILER' => config('mail.default'),
                'STRIPE_SECRET' => config('services.stripe.secret') ? 'Configured' : 'Missing',
            ],
        ]);
    }
}
