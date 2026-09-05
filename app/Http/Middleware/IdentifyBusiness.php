<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Support\Tenancy\CurrentBusiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = $request->route('business');

        if (! $business instanceof Business) {
            abort(404);
        }

        $user = $request->user();

        $membership = $user->teamMembers()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            // Deliberately 404, not 403: don't reveal that a business with
            // this slug exists to a user who isn't a member of it.
            abort(404);
        }

        app(CurrentBusiness::class)->set($business);

        // Available in every view without threading it through each controller.
        view()->share('currentBusiness', $business);
        view()->share('currentMembership', $membership);

        $request->attributes->set('membership', $membership);

        return $next($request);
    }
}
