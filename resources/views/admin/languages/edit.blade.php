@extends('layouts.app')

@section('title', 'Edit Language')
@section('page-title', 'Languages')

@section('topbar-actions')
    <a href="{{ route('admin.languages.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Modify Language Settings</h2>
    </div>

    <form method="POST" action="{{ route('admin.languages.update', $language->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Language Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $language->name) }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="code">Language Code *</label>
            <input type="text" id="code" name="code" value="{{ old('code', $language->code) }}" required>
            @error('code')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Update Language <i class='bx bx-check-circle'></i>
        </button>
    </form>

    @if($language->levels()->count() > 0)
        <div style="margin-top: 20px; padding: 15px; background: rgba(245, 158, 11, 0.1); border-radius: 8px; border: 1px solid rgba(245, 158, 11, 0.2);">
            <p style="color: var(--accent); font-size: 13px; font-weight: 600;">
                <i class='bx bx-info-circle'></i> Status: This language is currently used by {{ $language->levels()->count() }} levels.
            </p>
        </div>
    @endif
</div>
@endsection
