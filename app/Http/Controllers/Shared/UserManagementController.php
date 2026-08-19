<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBranchAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Yajra\DataTables\Facades\DataTables;

abstract class UserManagementController extends Controller
{
    abstract protected function routePrefix(): string;

    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        if ($request->ajax()) {
            // Users who have this company in BOTH employee_branch_access and role_company
            $employeeIdsWithAccess = EmployeeBranchAccess::where('company_id', $companyId)
                ->pluck('employee_id')
                ->unique();

            $roleIdsWithAccess = DB::table('role_company')
                ->where('company_id', $companyId)
                ->pluck('role_id');

            $users = User::whereIn('employee_id', $employeeIdsWithAccess)
                ->whereIn('role_id', $roleIdsWithAccess)
                ->with('role')
                ->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('role_badge', fn (User $r) => $r->role
                    ? '<span class="badge bg-secondary">'.e($r->role->name).'</span>'
                    : '<span class="text-muted small">—</span>')
                ->addColumn('status_badge', fn (User $r) => $r->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', fn (User $r) => '
                    <a href="'.route('admin.users.edit', $r->id).'" class="btn btn-sm btn-outline-primary me-1" title="Edit in Admin Panel" target="_blank">
                        <i class="fa fa-edit"></i>
                    </a>')
                ->rawColumns(['role_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view($this->routePrefix().'.users.index');
    }

    public function show(User $user)
    {
        return response()->json([
            'id'          => $user->id,
            'name'        => $user->name,
            'username'    => $user->username,
            'email'       => $user->email,
            'is_active'   => $user->is_active,
            'role_id'     => $user->role_id,
            'role_name'   => $user->role?->name,
            'employee_id' => $user->employee_id,
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

        User::create([
            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->filled('email') ? $request->email : null,
            'password'    => $request->password,
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

        $data = [
            'name'        => $request->name,
            'username'    => $request->username,
            'email'       => $request->filled('email') ? $request->email : null,
            'is_active'   => $request->boolean('is_active'),
            'employee_id' => $request->filled('employee_id') ? $request->employee_id : $user->employee_id,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => [Password::min(6)]]);
            $data['password'] = $request->password;
        }

        $user->update($data);

        return response()->json(['message' => 'User updated successfully.']);
    }

    public function destroy(User $user)
    {
        if ($user->is_super) {
            return response()->json(['message' => 'Cannot delete super admin.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
