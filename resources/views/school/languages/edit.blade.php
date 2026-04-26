@extends('layouts.app')

@section('title', 'Edit Language')
@section('page-title', 'School Languages')

@section('topbar-actions')
    <a href="{{ route('school.school-languages.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Update Language: {{ $schoolLanguage->name }}</h2>
    </div>

    <form method="POST" action="{{ route('school.school-languages.update', $schoolLanguage->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Language Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $schoolLanguage->name) }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="teachers">Assigned Teachers</label>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">Update teachers who teach this language in your school.</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-height: 200px; overflow-y: auto; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                @php $assignedIds = $schoolLanguage->teachers->pluck('id')->toArray(); @endphp
                @foreach($teachers as $teacher)
                    <div style="display: flex; align-items: center; gap: 8px; background: var(--bg2); padding: 8px; border-radius: 6px;">
                        <input type="checkbox" id="teacher_{{ $teacher->id }}" name="teachers[]" value="{{ $teacher->id }}" {{ in_array($teacher->id, $assignedIds) ? 'checked' : '' }}>
                        <label for="teacher_{{ $teacher->id }}" style="margin: 0; font-size: 0.9rem; cursor: pointer;">{{ $teacher->name }}</label>
                    </div>
                @endforeach
            </div>
            @error('teachers')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save Changes <i class='bx bx-check'></i>
        </button>
    </form>
</div>
@endsection
