<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Company\EditCompanyRequest;
use App\Http\Requests\Admin\Company\IndexCompanyRequest;
use App\Http\Requests\Admin\Company\UpdateCompanyRequest;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(IndexCompanyRequest $request)
    {
        $companies = Company::all();

        return view('admin.companies.index', compact('companies'));
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
