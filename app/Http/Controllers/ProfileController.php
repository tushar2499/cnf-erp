<?php

namespace App\Http\Controllers;

use App\Models\Chevron\ChevronBranch;
use App\Models\Company;
use App\Models\EmployeeBranchAccess;
use App\Models\NasFreights\NasFreightsBranch;
use App\Models\NasTrading\NasTradingBranch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

        $accessData = $this->buildAccessData($user);

        $userRole = $user->is_super
            ? 'Super Admin'
            : $user->role?->name;

        return view('shared.profile.show', compact('user', 'accessData', 'userRole'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => filled($validated['email'] ?? null) ? $validated['email'] : null,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(6)],
        ]);

        Auth::user()->update(['password' => $request->password]);

        return back()->with('success', 'Password changed successfully.');
    }

    /** @return array<int, array{company: Company, role: ?Role, permissions_by_module: array<string, list<string>>, branches: list<string>}> */
    private function buildAccessData(User $user): array
    {
        if ($user->is_super) {
            return Company::where('is_active', true)->get()->map(fn ($co) => [
                'company'               => $co,
                'role'                  => null,
                'is_active'             => true,
                'permissions_by_module' => [],
                'branches'              => [],
            ])->values()->toArray();
        }

        $role = $user->role_id ? Role::with('permissions')->find($user->role_id) : null;

        $roleCompanyIds = DB::table('role_company')
            ->where('role_id', $user->role_id)
            ->pluck('company_id');

        $accessByCompany = EmployeeBranchAccess::where('employee_id', $user->employee_id)
            ->get()
            ->groupBy('company_id');

        $visibleCompanyIds = $roleCompanyIds->intersect($accessByCompany->keys());

        $companies = Company::whereIn('id', $visibleCompanyIds)->where('is_active', true)->get();

        $permsByModule = [];
        if ($role) {
            foreach ($role->permissions as $perm) {
                $module = $perm->module ?: 'General';
                $permsByModule[$module][] = $perm->name;
            }
            ksort($permsByModule);
        }

        $branchLoaders = [
            1 => fn (array $ids) => ChevronBranch::whereIn('id', $ids)->pluck('name'),
            2 => fn (array $ids) => NasFreightsBranch::whereIn('id', $ids)->pluck('name'),
            3 => fn (array $ids) => NasTradingBranch::whereIn('id', $ids)->pluck('name'),
        ];

        $result = [];

        foreach ($companies as $company) {
            $branchIds = ($accessByCompany[$company->id] ?? collect())->pluck('branch_id')->toArray();
            $branchNames = [];
            if ($branchIds && isset($branchLoaders[$company->id])) {
                $branchNames = ($branchLoaders[$company->id])($branchIds)->values()->toArray();
            }

            $result[] = [
                'company'               => $company,
                'role'                  => $role,
                'is_active'             => $user->is_active,
                'permissions_by_module' => $permsByModule,
                'branches'              => $branchNames,
            ];
        }

        return $result;
    }
}
