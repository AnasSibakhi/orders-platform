<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Note: this platform is multi-tenant (module: Multi-tenancy). A freshly
 * registered user doesn't belong to any Business yet, so — unlike the
 * single-tenant Phase 1 base this was built on — we send them to onboarding
 * to create their first business rather than straight to a dashboard.
 *
 * They also don't get a global platform Role here: Role/Permission (super
 * admin, platform support) is for staff who operate the SaaS itself.
 * A regular customer's access is entirely defined by their TeamMember rows
 * (owner/manager/agent) per business, created in BusinessController.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => User::STATUS_ACTIVE,
        ]);

        $auditLog->log('user.registered', User::class, $user->id);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('onboarding.create');
    }
}
