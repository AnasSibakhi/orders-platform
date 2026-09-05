<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\TeamMember;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusinessController extends Controller
{
    /**
     * Lists the businesses the current user belongs to, so they can switch
     * between them (a user can have their own login across multiple
     * businesses, e.g. an agent working for two different shops).
     */
    public function index(Request $request): View
    {
        $businesses = $request->user()
            ->businesses()
            ->wherePivot('status', TeamMember::STATUS_ACTIVE)
            ->get();

        return view('business.switch', ['businesses' => $businesses]);
    }

    public function create(): View
    {
        return view('onboarding.create-business');
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $business = DB::transaction(function () use ($validated, $request) {
            $business = Business::create([
                'name' => $validated['name'],
                'slug' => Business::generateUniqueSlug($validated['name']),
                'status' => Business::STATUS_ACTIVE,
            ]);

            $business->teamMembers()->create([
                'user_id' => $request->user()->id,
                'role' => TeamMember::OWNER,
                'status' => TeamMember::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);

            return $business;
        });

        $auditLog->log('business.created', Business::class, $business->id, ['name' => $business->name]);

        return redirect()->route('business.dashboard', $business);
    }
}
