<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module  The module to check permission for (e.g., 'POS', 'Inventory', 'HR')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        // If no user is authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user has access to the module
        if (!$user->hasModule($module)) {
            // For Inertia requests, return a proper response
            if ($request->inertia()) {
                return \Inertia\Inertia::render('Errors/Forbidden', [
                    'message' => 'You do not have permission to access this module.',
                    'module' => $module,
                ])->toResponse($request)->setStatusCode(403);
            }

            // For regular requests, abort with 403
            abort(403, 'Access denied: You do not have permission to access this module.');
        }

        return $next($request);
    }
}
