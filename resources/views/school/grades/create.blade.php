@extends('layouts.app')

@section('title', 'Add New Grade')
@section('page-title', 'Academic Grades')

@section('topbar-actions')
    <a href="{{ route('school.grades.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Create Grade</h2>
    </div>

    <form method="POST" action="{{ route('school.grades.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Grade Name (e.g., Grade 7, First Prep) *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter grade name...">
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Link School Languages to this Grade</label>
            <p style="color:var(--text-muted); font-size:13px; margin-bottom:10px;">Select which languages are taught in this grade.</p>
            
            <div style="display:flex; flex-direction:column; gap:8px; background:var(--bg2); padding:15px; border-radius:8px; border:1px solid var(--border);">
                @foreach($schoolLanguages as $lang)
                    <label style="display:flex; align-items:center; gap:10px; font-weight:normal; margin:0; cursor:pointer;">
                        <input type="checkbox" name="languages[]" value="{{ $lang->id }}" {{ (is_array(old('languages')) && in_array($lang->id, old('languages'))) ? 'checked' : '' }}>
                        {{ $lang->name }}
                    </label>
                @endforeach
                @if($schoolLanguages->isEmpty())
                    <p style="font-size: 13px; color: var(--accent);">No school languages configured yet. Go to School Languages to add them.</p>
                @endif
            </div>
            @error('languages')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save Grade <i class='bx bx-check'></i>
        </button>
    </form>
</div>
@endsection
