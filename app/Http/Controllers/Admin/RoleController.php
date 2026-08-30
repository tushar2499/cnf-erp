<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\CreateRoleRequest;
use App\Http\Requests\Admin\Role\DestroyRoleRequest;
use App\Http\Requests\Admin\Role\EditRoleRequest;
use App\Http\Requests\Admin\Role\IndexRoleRequest;
use App\Http\Requests\Admin\Role\ShowRoleRequest;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(IndexRoleRequest $request)
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
                ->filterColumn('companies_badges', function ($query, $keyword) {
                    $query->whereHas('companies', function ($q) use ($keyword) {
                        $q->where('companies.name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function (Role $role) use ($request) {
                    $html = '';
                    if ($request->user()->hasPermission('admin.roles.view')) {
                        $html .= '<a href="'.route('admin.roles.show', $role).'" class="btn btn-sm btn-outline-secondary me-1" title="View">
                                <i class="fa fa-eye"></i>
                            </a>';
                    }
                    if ($request->user()->hasPermission('admin.roles.edit')) {
                        $html .= '<a href="'.route('admin.roles.edit', $role).'" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>';
                    }
                    if ($request->user()->hasPermission('admin.roles.delete')) {
                        $html .= '<button class="btn btn-sm btn-outline-danger btn-delete"
                                data-id="'.$role->id.'"
                                data-name="'.e($role->name).'"
                                data-url="'.route('admin.roles.destroy', $role).'"
                                title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>';
                    }

                    return $html;
                })
                ->rawColumns(['companies_badges', 'action'])
                ->make(true);
        }

        return view('admin.roles.index');
    }

    public function show(ShowRoleRequest $request, Role $role)
    {
        $role->load(['companies', 'permissions.company']);

        $systemPermissions = $role->permissions->whereNull('company_id')->groupBy('module');
        $companyPermissions = $role->permissions->whereNotNull('company_id')->groupBy('company_id');

        return view('admin.roles.show', compact('role', 'systemPermissions', 'companyPermissions'));
    }

    public function create(CreateRoleRequest $request)
    {
        $companies = $this->companiesWithPermissions();
        $systemPermissions = $this->systemPermissions();

        return view('admin.roles.form', [
            'role'               => null,
            'companies'          => $companies,
            'systemPermissions'  => $systemPermissions,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        $permissionIds = $data['permission_ids'] ?? [];
        if ($permissionIds) {
            $role->permissions()->sync($permissionIds);
            $companyIds = Permission::whereIn('id', $permissionIds)->whereNotNull('company_id')->distinct()->pluck('company_id');
            $role->companies()->sync($companyIds);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "'.$role->name.'" created successfully.');
    }

    public function edit(EditRoleRequest $request, Role $role)
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

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();

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

    public function destroy(DestroyRoleRequest $request, Role $role)
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
