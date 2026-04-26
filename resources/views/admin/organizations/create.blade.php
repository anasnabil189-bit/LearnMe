@extends('layouts.app')

@section('title', 'Add New Organization')
@section('page-title', 'Create B2B Organization')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <a href="{{ route('admin.organizations.index') }}" class="btn btn-ghost" style="margin-bottom: 20px;">
        <i class='bx bx-arrow-back'></i> Back to List
    </a>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Organization Details</h2>
        </div>

        <form action="{{ route('admin.organizations.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Organization Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Arab Open University">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="type">Entity Type *</label>
                <select id="type" name="type" required>
                    <option value="school" {{ old('type') == 'school' ? 'selected' : '' }}>School</option>
                    <option value="company" {{ old('type', 'company') == 'company' ? 'selected' : '' }}>Company</option>
                    <option value="university" {{ old('type') == 'university' ? 'selected' : '' }}>University</option>
                    <option value="center" {{ old('type') == 'center' ? 'selected' : '' }}>Learning Center</option>
                </select>
                @error('type') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="allowed_domains">Allowed Email Domains (Optional)</label>
                <input type="text" id="allowed_domains" name="allowed_domains" value="{{ old('allowed_domains') }}" placeholder="e.g. eelu.edu.eg, staff.eelu.edu.eg">
                <small style="color: var(--text-muted); font-size: 12px; display: block; margin-top: 5px;">
                    Users must possess an email address from one of these comma-separated domains to join.
                </small>
                @error('allowed_domains') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="discount_percentage">Discount Percentage (%) *</label>
                    <input type="number" step="0.01" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" required min="0" max="100">
                </div>
                <div class="form-group">
                    <label for="max_users">Max Users Limit (Optional)</label>
                    <input type="number" id="max_users" name="max_users" value="{{ old('max_users') }}" placeholder="Unlimited if empty">
                </div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                    <i class='bx bx-check-circle'></i> Save & Continue to Codes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
