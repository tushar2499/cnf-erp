<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('active_company_id')) {
            view()->share('activeCompany', (object) [
                'id'   => session('active_company_id'),
                'slug' => session('active_company_slug'),
                'name' => session('active_company_name'),
                'type' => session('active_company_type'),
            ]);
        }

        $branchIdKey = match(session('active_company_slug')) {
            'nas-freights' => 'nas_freights_branch_id',
            'nas-trading'  => 'nas_trading_branch_id',
            default        => 'active_branch_id',
        };
        $branchNameKey = str_replace('_id', '_name', $branchIdKey);
        $branchCodeKey = str_replace('_id', '_code', $branchIdKey);

        if (session($branchIdKey)) {
            view()->share('activeBranch', (object) [
                'id'   => session($branchIdKey),
                'name' => session($branchNameKey),
                'code' => session($branchCodeKey),
            ]);
        }

        return $next($request);
    }
}
