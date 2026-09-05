<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class TeamController extends Controller
{
    public function index(Business $business): View
    {
        $members = $business->teamMembers()->with('user')->get();

        return view('business.team', [
            'business' => $business,
            'members' => $members,
        ]);
    }

    /**
     * MVP scope: adds an *already registered* user by email directly as an
     * active member. A proper email-invitation flow (invite link for
     * someone without an account yet) is a natural follow-up but isn't
     * needed for the core multi-tenant workflow to work end-to-end.
     */
    public function store(Business $business, Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'string', 'in:manager,agent'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'لا يوجد مستخدم مسجّل بهذا البريد الإلكتروني بعد.',
            ]);
        }

        if ($business->teamMembers()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'هذا المستخدم عضو بالفعل في هذا النشاط.',
            ]);
        }

        $member = $business->teamMembers()->create([
            'user_id' => $user->id,
            'role' => $validated['role'],
            'status' => TeamMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $auditLog->log('team_member.added', TeamMember::class, $member->id, ['role' => $member->role]);

        return back()->with('status', 'تمت إضافة العضو للفريق.');
    }

    public function destroy(Business $business, int $member, Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $member = $business->teamMembers()->findOrFail($member);

        if ($member->isOwner()) {
            throw ValidationException::withMessages([
                'member' => 'لا يمكن إزالة مالك النشاط.',
            ]);
        }

        if ($member->user_id === $request->user()->id) {
            throw ValidationException::withMessages([
                'member' => 'لا يمكنك إزالة نفسك.',
            ]);
        }

        $member->delete();

        $auditLog->log('team_member.removed', TeamMember::class, $member->id);

        return back()->with('status', 'تمت إزالة العضو.');
    }
}
