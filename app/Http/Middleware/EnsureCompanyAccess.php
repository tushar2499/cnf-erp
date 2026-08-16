<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $sessionSlug = session('active_company_slug');

        if ($sessionSlug !== $slug) {
            // Try to auto-switch if user has access
            $user = $request->user();
            $company = $user->companies()
                ->where('companies.slug', $slug)
                ->where('companies.is_active', true)
                ->where('company_user.is_active', true)
                ->when(! $user->is_super, fn ($q) => $q->whereNotNull('company_user.role_id'))
                ->first();

            if (! $company) {
                return redirect()->route('company.select')
                    ->with('error', 'You do not have access to that company.');
            }

            session([
                'active_company_id'   => $company->id,
                'active_company_slug' => $company->slug,
                'active_company_name' => $company->name,
                'active_company_type' => $company->type,
            ]);
        }

        return $next($request);
    }
}
