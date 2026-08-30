<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Company\EditCompanyRequest;
use App\Http\Requests\Admin\Company\IndexCompanyRequest;
use App\Http\Requests\Admin\Company\UpdateCompanyRequest;
use App\Models\Company;
use Yajra\DataTables\Facades\DataTables;

class CompanyController extends Controller
{
    public function index(IndexCompanyRequest $request)
    {
        if ($request->ajax()) {
            $query = Company::query()->select('companies.*')->orderBy('name');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('type_badge', function (Company $company) {
                    $color = match ($company->type) {
                        'cnf'     => 'bg-success',
                        'freight' => 'bg-info text-dark',
                        'trading' => 'bg-warning text-dark',
                        default   => 'bg-secondary',
                    };

                    return '<span class="badge '.$color.'">'.e(strtoupper($company->type)).'</span>';
                })
                ->filterColumn('type_badge', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));
                    $match = match (true) {
                        str_contains($keyword, 'cnf')     => 'cnf',
                        str_contains($keyword, 'freight') => 'freight',
                        str_contains($keyword, 'trading') => 'trading',
                        default                           => null,
                    };
                    if ($match) {
                        $query->where('type', $match);
                    }
                })
                ->addColumn('status_badge', fn (Company $company) => $company->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->filterColumn('status_badge', function ($query, $keyword) {
                    $keyword = strtolower(trim($keyword));
                    if (str_contains($keyword, 'active') && ! str_contains($keyword, 'inactive')) {
                        $query->where('is_active', true);
                    } elseif (str_contains($keyword, 'inactive')) {
                        $query->where('is_active', false);
                    }
                })
                ->addColumn('action', fn (Company $company) => auth()->user()->hasPermission('admin.companies.edit')
                    ? '<a href="'.route('admin.companies.edit', $company).'" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>'
                    : '')
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.companies.index');
    }

    public function edit(EditCompanyRequest $request, Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {

            if ($company->logo) {
                $oldPath = public_path('assets/logos/'.$company->logo);

                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }

            }

            $file = $request->file('logo');
            $filename = $company->slug.'.'.$file->getClientOriginalExtension();
            $file->move(public_path('assets/logos'), $filename);
            $data['logo'] = $filename;
        }

        $data['is_active'] = $request->boolean('is_active');
        $company->update($data);

        return response()->json(['message' => 'Company updated successfully.']);
    }
}
