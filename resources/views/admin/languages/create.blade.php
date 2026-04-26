@extends('layouts.app')

@section('title', 'Add New Language')
@section('page-title', 'Languages')

@section('topbar-actions')
    <a href="{{ route('admin.languages.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Define New Language</h2>
    </div>

    <form method="POST" action="{{ route('admin.languages.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Language Name (e.g., German, Spanish) *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter language name">
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="code">Language Code (e.g., de, es, fr) *</label>
            <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="e.g. en">
            @error('code')<div class="form-error">{{ $message }}</div>@enderror
            <p style="font-size:12px; color:var(--text-muted); margin-top:5px;">This is used for internal slugs and UI identification.</p>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save & Add Language <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>
@endsection
