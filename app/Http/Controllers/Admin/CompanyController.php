<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('admin.companies.index', compact('companies'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'address'   => ['nullable', 'string', 'max:500'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'email'     => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
            'logo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo) {
                $oldPath = public_path('assets/logos/' . $company->logo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file     = $request->file('logo');
            $filename = $company->slug . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/logos'), $filename);
            $data['logo'] = $filename;
        }

        $data['is_active'] = $request->boolean('is_active');
        $company->update($data);

        return response()->json(['message' => 'Company updated successfully.']);
    }
}
