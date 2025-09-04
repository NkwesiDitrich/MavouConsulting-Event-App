<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userRole = $request->user()->role;

        // Check if user has any of the required roles
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'error' => 'Forbidden', 
                'message' => 'You do not have permission to access this resource'
            ], 403);
        }

        return $next($request);
    }
}

