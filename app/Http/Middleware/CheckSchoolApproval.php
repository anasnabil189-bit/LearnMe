<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolApproval
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->type === 'school') {
            $school = $user->school;
            
            // Allow access only if school exists and is approved
            // Exception: Allow the pending approval page itself
            if (!$school || $school->status !== 'approved') {
                if (!$request->routeIs('school.pending')) {
                    return redirect()->route('school.pending');
                }
            }
        }

        return $next($request);
    }
}
