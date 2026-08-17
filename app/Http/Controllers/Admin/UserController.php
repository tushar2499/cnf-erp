<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateUserRequest;
use App\Http\Requests\Admin\User\DestroyUserRequest;
use App\Http\Requests\Admin\User\EditUserRequest;
use App\Http\Requests\Admin\User\IndexUserRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\Chevron\ChevronBranch;
use App\Models\Chevron\ChevronEmployee;
use App\Models\Company;
use App\Models\NasFreights\NasFreightsBranch;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasTrading\NasTradingBranch;
use App\Models\NasTrading\NasTradingEmployee;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBranchAccess;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(IndexUserRequest $request)
    {
        if ($request->ajax()) {
            $users = User::with([
                'companies' => fn ($q) => $q->withPivot('role_id', 'is_active'),
            ])->get();

            $roleIds = $users->flatMap(fn ($u) => $u->companies->pluck('pivot.role_id'))->filter()->unique();
            $rolesById = Role::whereIn('id', $roleIds)->get()->keyBy('id');

            // role_id → count of system (company_id IS NULL) permissions
            $sysPermCountByRole = DB::table('role_has_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->whereNull('permissions.company_id')
                ->selectRaw('role_has_permissions.role_id, count(*) as cnt')
                ->groupBy('role_has_permissions.role_id')
                ->pluck('cnt', 'role_id');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', function (User $user) use ($rolesById) {
                    $roleId = $user->companies->first(fn ($c) => $c->pivot->role_id)?->pivot->role_id;
                    if (! $roleId) {
                        return '<span class="text-muted small">—</span>';
                    }
                    $role = $rolesById[$roleId] ?? null;

                    return $role
                        ? '<span class="badge" style="background:#ede9fe;color:#6d28d9;">'.e($role->name).'</span>'
                        : '<span class="text-muted small">—</span>';
                })
                ->addColumn('companies_badges', function (User $user) use ($sysPermCountByRole) {
                    $badges = '';

                    $activeCompanies = $user->is_super
                        ? $user->companies
                        : $user->companies->filter(fn ($c) => $c->pivot->role_id);

                    $roleId = $activeCompanies->first(fn ($c) => $c->pivot->role_id)?->pivot->role_id;
                    $hasSysPerms = $user->is_super || ($roleId && ($sysPermCountByRole[$roleId] ?? 0) > 0);

                    if ($hasSysPerms) {
                        $badges .= '<span class="badge me-1" style="background:#e2e8f0;color:#475569;font-size:.7rem"><i class="fa fa-gear me-1"></i>System</span>';
                    }

                    $badges .= $activeCompanies->map(function ($co) {
                        $color = match ($co->type) {
                            'cnf'     => 'success',
                            'freight' => 'info',
                            'trading' => 'warning',
                            default   => 'secondary',
                        };

                        return '<span class="badge bg-'.$color.' me-1">'.e($co->name).'</span>';
                    })->implode('');

                    return $badges ?: '<span class="text-muted small">—</span>';
                })
                ->addColumn('status_badge', fn (User $user) => $user->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('super_badge', fn (User $user) => $user->is_super
                    ? '<span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;"><i class="fa fa-crown me-1"></i>Super</span>'
                    : '<span class="text-muted small">—</span>')
                ->addColumn('action', function (User $user) use ($request) {
                    $html = '';
                    if ($request->user()->hasPermission('admin.users.edit')) {
                        $html .= '<a href="'.route('admin.users.edit', $user).'" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>';
                    }
                    if ($request->user()->hasPermission('admin.users.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                        data-id="'.$user->id.'"
                        data-name="'.e($user->name).'"
                        data-url="'.route('admin.users.destroy', $user).'"
                        title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>';
                    }

                    return $html;
                })
                ->rawColumns(['role_badge', 'companies_badges', 'status_badge', 'super_badge', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function create(CreateUserRequest $request)
    {
        $roles = Role::with('companies')->orderBy('name')->get();
        $companiesData = $this->buildCompaniesData();
        $rolesJson = $this->buildRolesJson($roles);

        return view('admin.users.form', [
            'user'          => null,
            'roles'         => $roles,
            'companiesData' => $companiesData,
            'rolesJson'     => $rolesJson,
            'currentRoleId' => null,
            'employeeLink'  => null,
            'branchAccess'  => [],
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'email'     => filled($validated['email'] ?? null) ? $validated['email'] : null,
            'password'  => $validated['password'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncRoleAndCompanies($user, $validated['role_id'] ?? null, $validated['employee_link'] ?? null);
        $this->syncBranchAccess($user, $validated['branch_access'] ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'User "'.$user->name.'" created successfully.');
    }

    public function edit(EditUserRequest $request, User $user)
    {
        $roles = Role::with('companies')->orderBy('name')->get();
        $companiesData = $this->buildCompaniesData();
        $rolesJson = $this->buildRolesJson($roles);

        $companyUserRow = $user->companies()->withPivot('role_id', 'employee_id')->get()->first();
        $currentRoleId = $companyUserRow?->pivot->role_id;

        $employeeLink = null;
        if ($companyUserRow && $companyUserRow->pivot->employee_id) {
            $employeeLink = $companyUserRow->id.':'.$companyUserRow->pivot->employee_id;
        }

        $branchAccess = UserBranchAccess::where('user_id', $user->id)
            ->get()
            ->groupBy('company_id')
            ->map(fn ($rows) => $rows->pluck('branch_id')->toArray())
            ->toArray();

        return view('admin.users.form', compact(
            'user', 'roles', 'companiesData', 'rolesJson',
            'currentRoleId', 'employeeLink', 'branchAccess'
        ));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $userData = [
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'email'     => filled($validated['email'] ?? null) ? $validated['email'] : null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (filled($validated['password'] ?? null)) {
            $userData['password'] = $validated['password'];
        }

        $user->update($userData);

        $this->syncRoleAndCompanies($user, $validated['role_id'] ?? null, $validated['employee_link'] ?? null);
        $this->syncBranchAccess($user, $validated['branch_access'] ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'User "'.$user->name.'" updated successfully.');
    }

    public function destroy(DestroyUserRequest $request, User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    private function syncRoleAndCompanies(User $user, ?string $roleId, ?string $employeeLink): void
    {
        if (! $roleId) {
            $user->companies()->detach();

            return;
        }

        $role = Role::with('companies')->find($roleId);

        if (! $role) {
            $user->companies()->detach();

            return;
        }

        $companyIds = $role->companies->pluck('id');

        [$empCompanyId, $empId] = $this->parseEmployeeLink($employeeLink);

        $sync = [];
        foreach ($companyIds as $companyId) {
            $sync[$companyId] = [
                'role_id'     => $roleId,
                'employee_id' => ($empCompanyId == $companyId) ? $empId : null,
                'is_active'   => true,
            ];
        }

        $user->companies()->sync($sync);
    }

    private function syncBranchAccess(User $user, array $branchAccess): void
    {
        DB::transaction(function () use ($user, $branchAccess) {
            UserBranchAccess::where('user_id', $user->id)->delete();

            $rows = [];
            foreach ($branchAccess as $companyId => $branchIds) {
                foreach ((array) $branchIds as $branchId) {
                    if ($branchId) {
                        $rows[] = [
                            'user_id'    => $user->id,
                            'company_id' => $companyId,
                            'branch_id'  => $branchId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            if ($rows) {
                UserBranchAccess::insert($rows);
            }
        });
    }

    private function parseEmployeeLink(?string $employeeLink): array
    {
        if (! $employeeLink) {
            return [null, null];
        }
        $parts = explode(':', $employeeLink, 2);

        return [intval($parts[0]), intval($parts[1])];
    }

    private function buildCompaniesData(): array
    {
        $branches = [
            1 => ChevronBranch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            2 => NasFreightsBranch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            3 => NasTradingBranch::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];

        $employees = [
            1 => ChevronEmployee::orderBy('name')->get(['id', 'employee_id as emp_code', 'name']),
            2 => NasFreightsEmployee::orderBy('name')->get(['id', 'code as emp_code', 'name']),
            3 => NasTradingEmployee::orderBy('name')->get(['id', 'code as emp_code', 'name']),
        ];

        $companies = Company::where('is_active', true)->orderBy('id')->get();
        $data = [];

        foreach ($companies as $co) {
            $data[$co->id] = [
                'id'        => $co->id,
                'name'      => $co->name,
                'type'      => $co->type,
                'branches'  => $branches[$co->id] ?? collect(),
                'employees' => $employees[$co->id] ?? collect(),
            ];
        }

        return $data;
    }

    private function buildRolesJson(Collection $roles): string
    {
        return $roles->mapWithKeys(fn ($role) => [
            $role->id => $role->companies->pluck('id')->toArray(),
        ])->toJson();
    }
}
