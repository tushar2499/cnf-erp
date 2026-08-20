<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateUserRequest;
use App\Http\Requests\Admin\User\DestroyUserRequest;
use App\Http\Requests\Admin\User\EditUserRequest;
use App\Http\Requests\Admin\User\IndexUserRequest;
use App\Http\Requests\Admin\User\ShowUserRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(IndexUserRequest $request)
    {
        if ($request->ajax()) {
            $users = User::with('role.companies', 'employee')->get();

            $sysPermCountByRole = DB::table('role_has_permissions')
                ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->whereNull('permissions.company_id')
                ->selectRaw('role_has_permissions.role_id, count(*) as cnt')
                ->groupBy('role_has_permissions.role_id')
                ->pluck('cnt', 'role_id');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('employee_badge', function (User $user) {
                    if (! $user->employee) {
                        return '<span class="text-muted small">—</span>';
                    }
                    $emp = $user->employee;
                    $label = $emp->code ? e($emp->code).' — '.e($emp->name) : e($emp->name);

                    return '<span class="badge bg-secondary me-1" style="font-size:.7rem">'.e($label).'</span>';
                })
                ->addColumn('role_badge', fn (User $user) => $user->role
                    ? '<span class="badge" style="background:#ede9fe;color:#6d28d9;">'.e($user->role->name).'</span>'
                    : '<span class="text-muted small">—</span>')
                ->addColumn('companies_badges', function (User $user) use ($sysPermCountByRole) {
                    $hasSysPerms = $user->is_super
                        || ($user->role_id && ($sysPermCountByRole[$user->role_id] ?? 0) > 0);

                    $badges = $hasSysPerms
                        ? '<span class="badge me-1" style="background:#e2e8f0;color:#475569;font-size:.7rem"><i class="fa fa-gear me-1"></i>System</span>'
                        : '';

                    if ($user->role) {
                        $badges .= $user->role->companies->map(function ($co) {
                            $color = match ($co->type) {
                                'cnf'     => 'success',
                                'freight' => 'info',
                                'trading' => 'warning',
                                default   => 'secondary',
                            };

                            return '<span class="badge bg-'.$color.' me-1">'.e($co->name).'</span>';
                        })->implode('');
                    }

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
                    if ($request->user()->hasPermission('admin.users.view')) {
                        $html .= '<a href="'.route('admin.users.show', $user).'" class="btn btn-sm btn-outline-secondary me-1" title="View Permissions">
                        <i class="fa fa-eye"></i>
                    </a>';
                    }
                    if ($request->user()->hasPermission('admin.users.edit')) {
                        $html .= '<a href="'.route('admin.users.edit', $user).'" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>';
                    }
                    if (! $user->is_super && $request->user()->hasPermission('admin.users.delete')) {
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
                ->rawColumns(['employee_badge', 'role_badge', 'companies_badges', 'status_badge', 'super_badge', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function show(ShowUserRequest $request, User $user)
    {
        $user->load(['role.permissions.company', 'role.companies', 'employee']);

        $systemPermissions = collect();
        $companyPermissions = collect();

        if ($user->role) {
            $systemPermissions = $user->role->permissions->whereNull('company_id')->groupBy('module');
            $companyPermissions = $user->role->permissions->whereNotNull('company_id')->groupBy('company_id');
        }

        $branchAccess = [];
        if ($user->employee_id) {
            $branchTableMap = [
                1 => 'chevron_branches',
                2 => 'nas_freights_branches',
                3 => 'nas_trading_branches',
            ];

            $accesses = DB::table('employee_branch_access')
                ->where('employee_id', $user->employee_id)
                ->get()
                ->groupBy('company_id');

            foreach ($accesses as $coId => $rows) {
                $table = $branchTableMap[$coId] ?? null;
                if (! $table) {
                    continue;
                }
                $branchIds = $rows->pluck('branch_id')->toArray();
                $branchAccess[$coId] = DB::table($table)
                    ->whereIn('id', $branchIds)
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();
            }
        }

        return view('admin.users.show', compact('user', 'systemPermissions', 'companyPermissions', 'branchAccess'));
    }

    public function create(CreateUserRequest $request)
    {
        $roles = Role::with('companies')->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return view('admin.users.form', [
            'user'          => null,
            'roles'         => $roles,
            'employees'     => $employees,
            'currentRoleId' => null,
            'employeeId'    => null,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'        => $validated['name'],
            'username'    => $validated['username'],
            'email'       => filled($validated['email'] ?? null) ? $validated['email'] : null,
            'password'    => $validated['password'],
            'is_active'   => $request->boolean('is_active', true),
            'role_id'     => $validated['role_id'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User "'.$user->name.'" created successfully.');
    }

    public function edit(EditUserRequest $request, User $user)
    {
        $roles = Role::with('companies')->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return view('admin.users.form', [
            'user'          => $user,
            'roles'         => $roles,
            'employees'     => $employees,
            'currentRoleId' => $user->role_id,
            'employeeId'    => $user->employee_id,
        ]);
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

        if (! $user->is_super) {
            $userData['role_id'] = $validated['role_id'] ?? null;
            $userData['employee_id'] = $validated['employee_id'] ?? null;
        }

        $user->update($userData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User "'.$user->name.'" updated successfully.');
    }

    public function destroy(DestroyUserRequest $request, User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
