<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::with([
                'companies',
                'permissions' => fn ($q) => $q->whereNull('company_id')->select('permissions.id'),
            ])->withCount('permissions');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('companies_badges', function (Role $role) {
                    $badges = '';

                    if ($role->permissions->isNotEmpty()) {
                        $badges .= '<span class="badge me-1" style="background:#e2e8f0;color:#475569;font-size:.7rem">
                                        <i class="fa fa-gear me-1"></i>System
                                    </span>';
                    }

                    $badges .= $role->companies->map(function ($company) {
                        $color = match ($company->type) {
                            'cnf'     => 'success',
                            'freight' => 'info',
                            'trading' => 'warning',
                            default   => 'secondary',
                        };

                        return '<span class="badge bg-'.$color.' me-1">'.e($company->name).'</span>';
                    })->implode('');

                    return $badges ?: '<span class="text-muted small">—</span>';
                })
                ->addColumn('action', function (Role $role) {
                    return '<a href="'.route('admin.roles.show', $role).'" class="btn btn-sm btn-outline-secondary me-1" title="View">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="'.route('admin.roles.edit', $role).'" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                data-id="'.$role->id.'"
                                data-name="'.e($role->name).'"
                                data-url="'.route('admin.roles.destroy', $role).'"
                                title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>';
                })
                ->rawColumns(['companies_badges', 'action'])
                ->make(true);
        }

        return view('admin.roles.index');
    }

    public function show(Role $role)
    {
        $role->load(['companies', 'permissions.company']);

        $systemPermissions = $role->permissions->whereNull('company_id')->groupBy('module');
        $companyPermissions = $role->permissions->whereNotNull('company_id')->groupBy('company_id');

        return view('admin.roles.show', compact('role', 'systemPermissions', 'companyPermissions'));
    }

    public function create()
    {
        $companies = $this->companiesWithPermissions();
        $systemPermissions = $this->systemPermissions();

        return view('admin.roles.form', [
            'role'               => null,
            'companies'          => $companies,
            'systemPermissions'  => $systemPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permission_ids'   => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        $permissionIds = $data['permission_ids'] ?? [];
        if ($permissionIds) {
            $role->permissions()->sync($permissionIds);
            // derive company_ids from selected permissions (exclude system permissions with null company_id)
            $companyIds = Permission::whereIn('id', $permissionIds)->whereNotNull('company_id')->distinct()->pluck('company_id');
            $role->companies()->sync($companyIds);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "'.$role->name.'" created successfully.');
    }

    public function edit(Role $role)
    {
        $companies = $this->companiesWithPermissions();
        $systemPermissions = $this->systemPermissions();
        $selectedPermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.form', [
            'role'                => $role,
            'companies'           => $companies,
            'systemPermissions'   => $systemPermissions,
            'selectedPermissions' => $selectedPermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100', 'unique:roles,name,'.$role->id],
            'permission_ids'   => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => $data['name']]);

        $permissionIds = $data['permission_ids'] ?? [];
        $role->permissions()->sync($permissionIds);

        $companyIds = $permissionIds
            ? Permission::whereIn('id', $permissionIds)->whereNotNull('company_id')->distinct()->pluck('company_id')
            : [];
        $role->companies()->sync($companyIds);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "'.$role->name.'" updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    private function systemPermissions(): Collection
    {
        return Permission::whereNull('company_id')->orderBy('sorting_order')->get();
    }

    private function companiesWithPermissions(): Collection
    {
        return Company::where('is_active', true)
            ->with(['permissions' => function ($q) {
                $q->orderBy('sorting_order');
            }])
            ->get();
    }
}
