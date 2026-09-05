<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
|
| Worker registration/heartbeat/task-polling endpoints (module 14-15) and
| the resource API (module 32) are added in Phase 4/5, once the Laravel
| core (auth, roles, dashboard) is stable, per the project's own phased
| plan. This file intentionally stays minimal in Phase 1.
|
*/

Route::middleware('auth:sanctum')->get('/v1/me', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->user()->only('id', 'name', 'email', 'status'),
        'message' => 'Success',
    ]);
});
