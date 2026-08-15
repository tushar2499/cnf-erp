<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with([
                'companies' => fn ($q) => $q->withPivot('role', 'role_id', 'is_active'),
            ])->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', function (User $user) {
                    $roleId = $user->companies->first()?->pivot->role_id;
                    if (! $roleId) {
                        $oldRole = $user->companies->first()?->pivot->role;

                        return $oldRole
                            ? '<span class="badge bg-secondary">'.ucfirst($oldRole).'</span>'
                            : '<span class="text-muted small">—</span>';
                    }
                    $role = Role::find($roleId);

                    return $role
                        ? '<span class="badge" style="background:#ede9fe;color:#6d28d9;">'.e($role->name).'</span>'
                        : '<span class="text-muted small">—</span>';
                })
                ->addColumn('companies_badges', function (User $user) {
                    return $user->companies->map(function ($co) {
                        $color = match ($co->type) {
                            'cnf'     => 'success',
                            'freight' => 'info',
                            'trading' => 'warning',
                            default   => 'secondary',
                        };

                        return '<span class="badge bg-'.$color.' me-1">'.e($co->name).'</span>';
                    })->implode('') ?: '<span class="text-muted small">—</span>';
                })
                ->addColumn('status_badge', fn (User $user) => $user->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('super_badge', fn (User $user) => $user->is_super
                    ? '<span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;"><i class="fa fa-crown me-1"></i>Super</span>'
                    : '<span class="text-muted small">—</span>')
                ->addColumn('action', fn (User $user) => '
                    <a href="'.route('admin.users.edit', $user).'" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-id="'.$user->id.'"
                        data-name="'.e($user->name).'"
                        data-url="'.route('admin.users.destroy', $user).'"
                        title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['role_badge', 'companies_badges', 'status_badge', 'super_badge', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function create()
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

    public function store(Request $request)
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'username'          => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'email'             => ['nullable', 'email', 'unique:users,email'],
            'password'          => ['required', Password::min(6)],
            'is_active'         => ['boolean'],
            'role_id'           => ['nullable', 'exists:roles,id'],
            'employee_link'     => ['nullable', 'string', 'regex:/^\d+:\d+$/'],
            'branch_access'     => ['nullable', 'array'],
            'branch_access.*'   => ['nullable', 'array'],
            'branch_access.*.*' => ['nullable', 'integer'],
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->filled('email') ? $request->email : null,
            'password'  => $request->password,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncRoleAndCompanies($user, $request->input('role_id'), $request->input('employee_link'));
        $this->syncBranchAccess($user, $request->input('branch_access', []));

        return redirect()->route('admin.users.index')
            ->with('success', 'User "'.$user->name.'" created successfully.');
    }

    public function edit(User $user)
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

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'username'          => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id, 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'email'             => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'is_active'         => ['boolean'],
            'role_id'           => ['nullable', 'exists:roles,id'],
            'employee_link'     => ['nullable', 'string', 'regex:/^\d+:\d+$/'],
            'branch_access'     => ['nullable', 'array'],
            'branch_access.*'   => ['nullable', 'array'],
            'branch_access.*.*' => ['nullable', 'integer'],
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $userData = [
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->filled('email') ? $request->email : null,
            'is_active' => $request->boolean('is_active', true),
        ];
        if ($request->filled('password')) {
            $request->validate(['password' => [Password::min(6)]]);
            $userData['password'] = $request->password;
        }
        $user->update($userData);

        $this->syncRoleAndCompanies($user, $request->input('role_id'), $request->input('employee_link'));
        $this->syncBranchAccess($user, $request->input('branch_access', []));

        return redirect()->route('admin.users.index')
            ->with('success', 'User "'.$user->name.'" updated successfully.');
    }

    public function destroy(User $user)
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
        $companyIds = $role->companies->pluck('id');

        [$empCompanyId, $empId] = $this->parseEmployeeLink($employeeLink);

        $sync = [];
        foreach ($companyIds as $companyId) {
            $sync[$companyId] = [
                'role_id'     => $roleId,
                'role'        => 'user',
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
