@extends('layouts.app')

@php $routePrefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher'; @endphp

@section('title', 'Edit Lesson')
@section('page-title', 'Edit: ' . $lesson->title)

@section('topbar-actions')
    <a href="{{ route($routePrefix . '.lessons.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width:850px; margin:0 auto;">
    <div class="card-header">
        <h2 class="card-title">Modify Lesson</h2>
        <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">Add, delete, or reorder blocks as you wish.</p>
    </div>

    <form method="POST" action="{{ route($routePrefix . '.lessons.update', $lesson->id) }}" enctype="multipart/form-data" id="lesson-form">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Lesson Title *</label>
                <input type="text" name="title" value="{{ old('title', $lesson->title) }}" required>
                @error('title')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="language_id">Language *</label>
                <select name="language_id" id="language_id" required>
                    <option value="">-- Select Language --</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang->id }}" {{ old('language_id', $lesson->language_id) == $lang->id ? 'selected' : '' }}>{{ $lang->name }} ({{ $lang->code }})</option>
                    @endforeach
                </select>
                @error('language_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>



        <div class="form-grid">
            @if(auth()->user()->type === 'teacher')
            <div class="form-group" style="grid-column: span 2;">
                <label for="grade_language">Assigned Teaching Class *</label>
                <select id="grade_language" name="grade_language" required>
                    <option value="">-- Select Grade & Language --</option>
                    @foreach($teacherAssignments as $ta)
                        <option value="{{ $ta->grade_id }}|{{ $ta->school_language_id }}" {{ old('grade_language', "{$lesson->grade_id}|{$lesson->school_language_id}") == "{$ta->grade_id}|{$ta->school_language_id}" ? 'selected' : '' }}>
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
            <div class="form-group">
                <label>Level</label>
                <select name="level_id" id="level_id">
                    <option value="">-- Select Level --</option>
                    @foreach($levels as $l)
                        <option value="{{ $l->id }}" {{ old('level_id', $lesson->level_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="form-group">
            <label>Video URL (Optional)</label>
            <input type="url" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" placeholder="https://youtube.com/...">
        </div>

        {{-- Block Editor --}}
        <div style="margin-top:30px; margin-bottom:20px;">
            <label style="font-size:15px; font-weight:700; color:var(--text); margin-bottom:15px; display:block;">
                <i class='bx bx-layout' style="color:var(--primary-light);"></i> Lesson Content
            </label>
            <div id="blocks-container" style="display:flex; flex-direction:column; gap:15px;"></div>
            <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                <button type="button" onclick="addTextBlock()" class="btn btn-ghost" style="border:2px dashed var(--border); flex:1; min-width:180px; justify-content:center; padding:14px;">
                    <i class='bx bx-text' style="color:var(--primary-light);"></i> Add Text Explanation
                </button>
                <button type="button" onclick="addImageBlock()" class="btn btn-ghost" style="border:2px dashed var(--accent); flex:1; min-width:180px; justify-content:center; padding:14px;">
                    <i class='bx bx-image-add' style="color:var(--accent);"></i> Add Illustration Image
                </button>
            </div>
        </div>

        <input type="hidden" name="blocks_json" id="blocks_json">
        <div id="file-inputs-container"></div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; margin-top:15px;" onclick="return prepareSubmit()">
            Save Changes <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>

{{-- Pass existing blocks to JS --}}
@php
    $blocksArray = $lesson->blocks->map(function($b) {
        return [
            'type'          => $b->type,
            'content'       => $b->content,
            'existing_path' => $b->path,
        ];
    })->values()->toArray();
@endphp

<script>
    const existingBlocks = @json($blocksArray);
</script>

@include('lessons._block_editor_scripts', ['editMode' => true])

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
