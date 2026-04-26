@extends('layouts.app')

@php $routePrefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher'; @endphp

@section('title', 'Add New Lesson')
@section('page-title', 'Lesson Content')

@section('topbar-actions')
    <a href="{{ route($routePrefix . '.lessons.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width:850px; margin:0 auto;">
    <div class="card-header">
        <h2 class="card-title">Create New Lesson</h2>
        <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">Add text and images alternately as you wish — drag blocks to change their order.</p>
    </div>

    <form method="POST" action="{{ route($routePrefix . '.lessons.store') }}" enctype="multipart/form-data" id="lesson-form">
        @csrf

        {{-- Title & Language --}}
        <div class="form-grid">
            <div class="form-group">
                <label for="title">Lesson Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="e.g. Addition and Subtraction">
                @error('title')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            @if(in_array(auth()->user()->type, ['admin', 'manager']))
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
            @endif
        </div>



        <div class="form-grid">
            @if(auth()->user()->type === 'teacher')
            <div class="form-group" style="grid-column: span 2;">
                <label for="grade_language">Assigned Teaching Class *</label>
                <select id="grade_language" name="grade_language" required>
                    <option value="">-- Select Grade & Language --</option>
                    @foreach($teacherAssignments as $ta)
                        <option value="{{ $ta->grade_id }}|{{ $ta->school_language_id }}" {{ (old('grade_language') ?? request('grade_language')) == "{$ta->grade_id}|{$ta->school_language_id}" ? 'selected' : '' }}>
                            {{ $ta->grade->name }} - {{ $ta->schoolLanguage->name }}
                        </option>
                    @endforeach
                </select>
                @error('grade_language')<div class="form-error">{{ $message }}</div>@enderror
                @if($teacherAssignments->isEmpty())
                    <p style="color:var(--danger); font-size:12px; margin-top:5px;">You have no classes assigned. Contact your administrator.</p>
                @endif
            </div>
            @endif
            @if(in_array(auth()->user()->type, ['admin', 'manager']))
            <div class="form-group" style="grid-column: span 2;">
                <label for="level_id">Level (for Course Tracks)</label>
                <select id="level_id" name="level_id" required>
                    <option value="">-- Select Level --</option>
                    @foreach($levels as $l)
                        <option value="{{ $l->id }}" {{ old('level_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                    @endforeach
                </select>
                @error('level_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            @endif
        </div>

        <div class="form-group">
            <label for="video_url">Video URL (Optional)</label>
            <input type="url" id="video_url" name="video_url" value="{{ old('video_url') }}" placeholder="https://youtube.com/...">
            @error('video_url')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Block Editor --}}
        <div style="margin-top:30px; margin-bottom:20px;">
            <label style="font-size:15px; font-weight:700; color:var(--text); margin-bottom:15px; display:block;">
                <i class='bx bx-layout' style="color:var(--primary-light);"></i> Lesson Content (Blocks)
            </label>

            <div id="blocks-container" style="display:flex; flex-direction:column; gap:15px;">
                {{-- Blocks rendered by JS --}}
            </div>

            {{-- Add Block Buttons --}}
            <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                <button type="button" onclick="addTextBlock()" class="btn btn-ghost" style="border: 2px dashed var(--border); flex:1; min-width:180px; justify-content:center; padding:14px;">
                    <i class='bx bx-text' style="color:var(--primary-light);"></i> Add Text Explanation
                </button>
                <button type="button" onclick="addImageBlock()" class="btn btn-ghost" style="border: 2px dashed var(--accent); flex:1; min-width:180px; justify-content:center; padding:14px;">
                    <i class='bx bx-image-add' style="color:var(--accent);"></i> Add Illustration Image
                </button>
            </div>
        </div>

        {{-- Hidden inputs --}}
        <input type="hidden" name="blocks_json" id="blocks_json">
        {{-- Actual file inputs are injected here by JS --}}
        <div id="file-inputs-container"></div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; margin-top:15px;" onclick="return prepareSubmit()">
            Publish Lesson <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>

@include('lessons._block_editor_scripts')

@if(in_array(auth()->user()->type, ['admin', 'manager']))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language_id');
    const levelSelect = document.getElementById('level_id');

    if (languageSelect && levelSelect) {
        languageSelect.addEventListener('change', function() {
            const languageId = this.value;
            if (!languageId) {
                levelSelect.innerHTML = '<option value="">-- Select Level --</option>';
                return;
            }

            levelSelect.innerHTML = '<option value="">Loading levels...</option>';
            levelSelect.disabled = true;

            const url = "{{ route('admin.levels.by_language', ':id') }}".replace(':id', languageId);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    levelSelect.innerHTML = '<option value="">-- Select Level --</option>';
                    data.forEach(level => {
                        const option = document.createElement('option');
                        option.value = level.id;
                        option.textContent = level.name;
                        levelSelect.appendChild(option);
                    });
                    levelSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching levels:', error);
                    levelSelect.innerHTML = '<option value="">Error loading levels</option>';
                    levelSelect.disabled = false;
                });
        });
    }
});
</script>
@endif
@endsection
