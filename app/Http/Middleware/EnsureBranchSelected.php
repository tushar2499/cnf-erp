<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchSelected
{
    public function handle(Request $request, Closure $next, string $company = 'chevron'): Response
    {
        [$selectRoute, $storeRoute, $sessionKey] = match($company) {
            'nas-freights' => ['nas-freights.select-branch', 'nas-freights.select-branch.store', 'nas_freights_branch_id'],
            'nas-trading'  => ['nas-trading.select-branch',  'nas-trading.select-branch.store',  'nas_trading_branch_id'],
            default        => ['chevron.select-branch',       'chevron.select-branch.store',       'active_branch_id'],
        };

        if ($request->routeIs($selectRoute) || $request->routeIs($storeRoute)) {
            return $next($request);
        }

        if (!session($sessionKey)) {
            return redirect()->route($selectRoute);
        }

        return $next($request);
    }
}
