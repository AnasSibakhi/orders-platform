<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessRole
{
    /**
     * Usage: ->middleware('business_role:owner,manager')
     * Must run AFTER IdentifyBusiness, which sets the "membership" attribute.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $membership = $request->attributes->get('membership');

        if (! $membership || ! in_array($membership->role, $roles, true)) {
            abort(403, 'You do not have permission to perform this action for this business.');
        }

        return $next($request);
    }
}
