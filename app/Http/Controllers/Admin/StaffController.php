<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    private const ROLES = [
        'super_admin' => 'Super Admin',
        'gestionnaire_catalogue' => 'Gestionnaire Catalogue',
        'gestionnaire_stock' => 'Gestionnaire Stock',
        'service_client' => 'Service Client',
        'comptable' => 'Comptable',
    ];

    public function index(): Response
    {
        $staff = User::whereIn('role', array_keys(self::ROLES))->get();

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', array_keys(self::ROLES))],
            'password' => ['required', 'min:10'],
        ]);

        $user = User::create([
            ...$validated,
            'phone' => null,
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::record('staff.created', $user, [], ['role' => $validated['role']]);

        return back()->with('success', 'Compte administrateur créé. 2FA à activer à la première connexion.');
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        abort_unless(in_array($staff->role, array_keys(self::ROLES), true), 404);

        $validated = $request->validate([
            'role' => ['required', 'in:'.implode(',', array_keys(self::ROLES))],
        ]);

        $old = ['role' => $staff->role];
        $staff->update($validated);

        AuditLog::record('staff.role_updated', $staff, $old, $validated);

        return back()->with('success', 'Rôle mis à jour.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        abort_unless(in_array($staff->role, array_keys(self::ROLES), true), 404);

        AuditLog::record('staff.deleted', $staff, $staff->only(['email', 'role']), []);
        $staff->delete();

        return back()->with('success', 'Compte supprimé.');
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'super_admin', 403, 'Réservé au Super Admin.');
    }
}
