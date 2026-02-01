<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
             return redirect('login');
        }

        $userRole = $request->user()->role->slug ?? 'user';

        // Check if user role matches any of the allowed roles
        // We handle pipe separated roles locally first if Laravel doesn't parse it
        // But the middleware parameters come in as array from ...$roles
        
        // Handle "master|admin" style string if passed as single arg logic, 
        // but Laravel splits commas. If we use | in route definition, it might be passed as one string.
        
        $allowedRoles = [];
        foreach($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode('|', $role));
        }

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
