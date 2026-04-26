@extends('layouts.app')

@section('title', 'Edit Educational Level')
@section('page-title', 'Edit Level: ' . $level->name)

@section('topbar-actions')
    <a href="{{ route('admin.levels.index') }}" class="btn btn-ghost">
        <i class='bx bx-left-arrow-alt'></i> Back to List
    </a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Modify Level Details</h2>
    </div>

    <form action="{{ route('admin.levels.update', $level->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Level Title *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $level->name) }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="language_id">Language *</label>
            <select name="language_id" id="language_id" required>
                <option value="">-- Select Language --</option>
                @foreach($languages as $lang)
                    <option value="{{ $lang->id }}" {{ old('language_id', $level->language_id) == $lang->id ? 'selected' : '' }}>{{ $lang->name }} ({{ $lang->code }})</option>
                @endforeach
            </select>
            @error('language_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="required_xp">Required XP to Unlock level *</label>
            <input type="number" id="required_xp" name="required_xp" value="{{ old('required_xp', $level->required_xp) }}" required min="0">
            @error('required_xp')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class='bx bx-save'></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
