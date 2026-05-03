<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'role' => ['required', 'in:super_admin,sales_admin,inventory_admin,customer'],
        ]);

        $newRole = (string) $validated['role'];
        $isAdmin = in_array($newRole, User::ADMIN_ROLES, true);

        $superAdminCount = User::query()->where('role', User::ROLE_SUPER_ADMIN)->count();
        if ((string) $user->role === User::ROLE_SUPER_ADMIN && $newRole !== User::ROLE_SUPER_ADMIN && $superAdminCount <= 1) {
            return back()->with('status', 'You cannot remove the last super admin role.');
        }

        $oldRole = (string) $user->role;
        $user->update([
            'role' => $newRole,
            'is_admin' => $isAdmin,
        ]);

        AuditLogger::log(Auth::user(), 'user.role.updated', $user, [
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);

        return back()->with('status', 'User role updated.');
    }
}
