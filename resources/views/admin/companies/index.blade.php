@extends('admin.layouts.app')

@section('title', 'Companies')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-building me-2 text-primary"></i> Companies</h4>
</div>

<div class="card">
    <div class="card-header"><i class="fa fa-list me-2"></i> All Companies</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th style="width:70px">Logo</th>
                    <th>Company Name</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th style="width:100px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $company)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if($company->logo)
                            <img src="{{ asset('assets/logos/' . $company->logo) }}"
                                 alt="logo" style="height:36px; width:36px; object-fit:contain; border-radius:4px;">
                        @else
                            <span class="text-muted"><i class="fa fa-building fa-lg"></i></span>
                        @endif
                    </td>
                    <td class="fw-600">{{ $company->name }}</td>
                    <td>
                        <span class="badge {{ $company->type === 'cnf' ? 'bg-success' : ($company->type === 'freight' ? 'bg-info text-dark' : 'bg-warning text-dark') }}">
                            {{ strtoupper($company->type) }}
                        </span>
                    </td>
                    <td>{{ $company->email ?? '—' }}</td>
                    <td>{{ $company->phone ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $company->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $company->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
