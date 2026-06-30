<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = $request->user()
            ->companies()
            ->wherePivot('role', 'admin')
            ->wherePivot('is_active', true)
            ->exists();

        if (!$isAdmin) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
