<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Chevron\ChevronEmployee;
use App\Models\NasFreights\NasFreightsEmployee;
use App\Models\NasTrading\NasTradingEmployee;
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
        return match(session('active_company_type')) {
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
            $users = User::whereHas('companies', fn($q) => $q->where('company_id', $companyId))
                ->with(['companies' => fn($q) => $q->where('companies.id', $companyId)])
                ->get()
                ->map(function ($u) use ($employees) {
                    $pivot = $u->companies->first()?->pivot;
                    $u->role            = $pivot?->role ?? 'user';
                    $u->company_active  = (bool)($pivot?->is_active ?? true);
                    $u->employee_id     = $pivot?->employee_id;
                    $u->employee_name   = $pivot?->employee_id
                        ? ($employees->get($pivot->employee_id)?->name ?? '—')
                        : '—';
                    return $u;
                });

            $prefix = $this->routePrefix();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', fn($r) => $r->role === 'admin'
                    ? '<span class="badge bg-danger">Admin</span>'
                    : '<span class="badge bg-secondary">User</span>')
                ->addColumn('status_badge', fn($r) => ($r->is_active && $r->company_active)
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn($r) => '
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-id="' . $r->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="' . route($prefix . '.users.destroy', $r->id) . '"
                        data-name="' . e($r->name) . '">
                        <i class="fa fa-trash"></i>
                    </button>')
                ->rawColumns(['role_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view($this->routePrefix() . '.users.index', [
            'employees' => $this->getEmployees(),
        ]);
    }

    public function show(User $user)
    {
        $companyId = session('active_company_id');
        $pivot = $user->companies()->where('companies.id', $companyId)->first()?->pivot;

        return response()->json([
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'is_active'      => $user->is_active,
            'role'           => $pivot?->role ?? 'user',
            'company_active' => (bool)($pivot?->is_active ?? true),
            'employee_id'    => $pivot?->employee_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(6)],
            'role'     => ['required', 'in:admin,user'],
        ]);

        $companyId = session('active_company_id');

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => true,
        ]);

        $user->companies()->attach($companyId, [
            'role'        => $request->role,
            'is_active'   => true,
            'employee_id' => $request->filled('employee_id') ? $request->employee_id : null,
        ]);

        return response()->json(['message' => 'User created successfully.']);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'  => ['required', 'in:admin,user'],
        ]);

        $companyId = session('active_company_id');

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->boolean('is_active'),
        ];
        if ($request->filled('password')) {
            $request->validate(['password' => [Password::min(6)]]);
            $data['password'] = $request->password;
        }
        $user->update($data);

        $user->companies()->syncWithoutDetaching([
            $companyId => [
                'role'        => $request->role,
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
