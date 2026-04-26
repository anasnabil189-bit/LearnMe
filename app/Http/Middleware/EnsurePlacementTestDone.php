<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlacementTestDone
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if self-learning user (type 'user' and no school_id)
        if ($user && $user->type === 'user' && $user->school_id === null) {
            // Allow access only to the placement test routes
            if (!$user->has_taken_placement_test && !$request->routeIs('placement-test.*')) {
                return redirect()->route('placement-test.index');
            }
        }

        return $next($request);
    }
}
