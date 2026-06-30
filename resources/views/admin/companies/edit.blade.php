@extends('admin.layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="page-header">
    <h4><i class="fa fa-edit me-2 text-primary"></i> Edit Company</h4>
    <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back
    </a>
</div>

<form id="companyForm"
      action="{{ route('admin.companies.update', $company) }}"
      method="POST"
      enctype="multipart/form-data"
      data-ajax-form
      data-success="redirect:{{ route('admin.companies.index') }}">
    @csrf

    <div class="row g-3">

        {{-- Basic Info --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fa fa-info-circle me-2 text-primary"></i> Basic Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $company->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <input type="text" class="form-control"
                                   value="{{ strtoupper($company->type) }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $company->email) }}"
                                       placeholder="company@example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa fa-phone text-muted"></i></span>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $company->phone) }}"
                                       placeholder="+880 1xxx-xxxxxx">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"
                                      placeholder="Full company address">{{ old('address', $company->address) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       value="1" id="isActive"
                                       {{ $company->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Logo --}}
        <div class="col-12 col-md-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa fa-image me-2 text-primary"></i> Company Logo</div>
                <div class="card-body text-center">
                    <div id="logoPreview" class="mb-3">
                        @if($company->logo)
                            <img src="{{ asset('assets/logos/' . $company->logo) }}"
                                 id="previewImg"
                                 style="max-height:120px; max-width:100%; object-fit:contain; border-radius:6px; border:1px solid #dee2e6; padding:8px;">
                        @else
                            <div id="previewImg" class="text-muted py-3">
                                <i class="fa fa-building fa-3x mb-2 d-block"></i>
                                No logo uploaded
                            </div>
                        @endif
                    </div>
                    <input type="file" name="logo" id="logoInput"
                           class="form-control form-control-sm"
                           accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG or WebP · Max 2MB</div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="col-12">
            <button type="submit" class="btn btn-primary px-4" data-label="Save Changes">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
// Live logo preview
$('#logoInput').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        $('#logoPreview').html(
            '<img src="' + e.target.result + '" id="previewImg" style="max-height:120px;max-width:100%;object-fit:contain;border-radius:6px;border:1px solid #dee2e6;padding:8px;">'
        );
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
