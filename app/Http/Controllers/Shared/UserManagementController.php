<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronEmployee;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasTrading\NasTradingEmployee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

abstract class UserManagementController extends Controller
{
    abstract protected function routePrefix(): string;

    protected function getEmployees(): Collection
    {
        return match (session('active_company_type')) {
            'freight' => NasFreightsEmployee::where('branch_id', session('nas_freights_branch_id'))
                ->orderBy('name')->get(['id', 'code as emp_code', 'name']),
            'trading' => NasTradingEmployee::orderBy('name')
                ->get(['id', 'code as emp_code', 'name']),
            'cnf'     => ChevronEmployee::orderBy('name')
                ->get(['id', 'employee_id as emp_code', 'name']),
            default   => collect(),
        };
    }

    public function index(Request $request)
    {
        $companyId = session('active_company_id');
        $employees = $this->getEmployees()->keyBy('id');

        if ($request->ajax()) {
            $users = User::whereHas('companies', fn ($q) => $q->where('company_id', $companyId))
                ->with(['companies' => fn ($q) => $q->where('companies.id', $companyId)])
                ->get();

            $roleIds = $users->flatMap(fn ($u) => $u->companies->pluck('pivot.role_id'))->filter()->unique()->values();
            $rolesById = Role::whereIn('id', $roleIds)->pluck('name', 'id');

            $users = $users->map(function ($u) use ($employees, $rolesById) {
                $pivot = $u->companies->first()?->pivot;
                $u->role_id = $pivot?->role_id;
                $u->role_name = $pivot?->role_id
                    ? ($rolesById[$pivot->role_id] ?? '—')
                    : null;
                $u->company_active = (bool) ($pivot?->is_active ?? true);
                $u->employee_id = $pivot?->employee_id;
                $u->employee_name = $pivot?->employee_id
                    ? ($employees->get($pivot->employee_id)?->name ?? '—')
                    : '—';

                return $u;
            });

            $prefix = $this->routePrefix();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', fn ($r) => $r->role_name
                    ? '<span class="badge bg-secondary">'.e($r->role_name).'</span>'
                    : '<span class="text-muted small">—</span>')
                ->addColumn('status_badge', fn ($r) => ($r->is_active && $r->company_active)
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn ($r) => '
                    <a href="'.route('admin.users.edit', $r->id).'" class="btn btn-sm btn-outline-primary me-1" title="Edit in Admin Panel" target="_blank">
                        <i class="fa fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="'.route($prefix.'.users.destroy', $r->id).'"
                        data-name="'.e($r->name).'">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['role_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view($this->routePrefix().'.users.index', [
            'employees' => $this->getEmployees(),
        ]);
    }

    public function show(User $user)
    {
        $companyId = session('active_company_id');
        $pivot = $user->companies()->where('companies.id', $companyId)->first()?->pivot;

        $roleName = $pivot?->role_id
            ? Role::find($pivot->role_id)?->name
            : null;

        return response()->json([
            'id'             => $user->id,
            'name'           => $user->name,
            'username'       => $user->username,
            'email'          => $user->email,
            'is_active'      => $user->is_active,
            'role_id'        => $pivot?->role_id,
            'role_name'      => $roleName,
            'company_active' => (bool) ($pivot?->is_active ?? true),
            'employee_id'    => $pivot?->employee_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'email'    => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(6)],
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $companyId = session('active_company_id');

        $user = User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->filled('email') ? $request->email : null,
            'password'  => $request->password,
            'is_active' => true,
        ]);

        $user->companies()->attach($companyId, [
            'is_active'   => true,
            'employee_id' => $request->filled('employee_id') ? $request->employee_id : null,
        ]);

        return response()->json(['message' => 'User created successfully.']);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id, 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'email'    => ['nullable', 'email', 'unique:users,email,'.$user->id],
        ], [
            'username.regex' => 'The username field must only contain letters, numbers, dashes, underscores, and dots.',
        ]);

        $companyId = session('active_company_id');

        $data = [
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->filled('email') ? $request->email : null,
            'is_active' => $request->boolean('is_active'),
        ];
        if ($request->filled('password')) {
            $request->validate(['password' => [Password::min(6)]]);
            $data['password'] = $request->password;
        }
        $user->update($data);

        $user->companies()->syncWithoutDetaching([
            $companyId => [
                'is_active'   => $request->boolean('company_active'),
                'employee_id' => $request->filled('employee_id') ? $request->employee_id : null,
            ],
        ]);

        return response()->json(['message' => 'User updated successfully.']);
    }

    public function destroy(User $user)
    {
        $companyId = session('active_company_id');
        $user->companies()->detach($companyId);

        return response()->json(['message' => 'User removed from company.']);
    }
}
