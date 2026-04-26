@extends('layouts.app')

@section('title', 'Add New Level')
@section('page-title', 'Educational Levels')

@section('topbar-actions')
    <a href="{{ route('admin.levels.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Create Course Track Level</h2>
    </div>

    <form method="POST" action="{{ route('admin.levels.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Level Title (e.g., Level 1: Basics) *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter level title">
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="language_id">Language *</label>
            <select name="language_id" id="language_id" required>
                <option value="">-- Select Language --</option>
                @foreach($languages as $lang)
                    <option value="{{ $lang->id }}" {{ old('language_id') == $lang->id ? 'selected' : '' }}>{{ $lang->name }} ({{ $lang->code }})</option>
                @endforeach
            </select>
            @error('language_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="required_xp">Required XP to Unlock Level *</label>
            <input type="number" id="required_xp" name="required_xp" value="{{ old('required_xp', 0) }}" min="0" required>
            @error('required_xp')<div class="form-error">{{ $message }}</div>@enderror
            <p style="font-size:12px; color:var(--text-muted); margin-top:5px;">Use zero for levels available immediately after registration.</p>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save & Create Level <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>
@endsection
