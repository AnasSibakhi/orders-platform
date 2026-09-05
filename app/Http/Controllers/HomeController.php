<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Central "where does the user land after auth" decision, since that
     * depends on how many businesses they belong to (module: Multi-tenancy).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $memberships = $request->user()
            ->teamMembers()
            ->with('business')
            ->where('status', TeamMember::STATUS_ACTIVE)
            ->get();

        if ($memberships->isEmpty()) {
            return redirect()->route('onboarding.create');
        }

        if ($memberships->count() === 1) {
            return redirect()->route('business.dashboard', $memberships->first()->business);
        }

        return redirect()->route('business.switch');
    }
}
