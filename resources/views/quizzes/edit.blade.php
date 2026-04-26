@extends('layouts.app')

@section('title', 'Edit Quiz')
@section('page-title', 'Quizzes & Challenges')

@section('topbar-actions')
    <a href="{{ route($prefix . '.quizzes.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Update Quiz Details</h2>
    </div>

    <form method="POST" action="{{ route($prefix . '.quizzes.update', $quiz->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="title">Quiz Title *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
            @error('title')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="language_id">Language *</label>
            <select name="language_id" id="language_id" required>
                <option value="">-- Select Language --</option>
                @foreach($languages as $lang)
                    <option value="{{ $lang->id }}" {{ old('language_id', $quiz->language_id) == $lang->id ? 'selected' : '' }}>{{ $lang->name }} ({{ $lang->code }})</option>
                @endforeach
            </select>
            @error('language_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        

        
        @if(auth()->user()->type === 'teacher')
        <div class="form-group">
            <label for="grade_language">Assigned Teaching Class *</label>
            <select id="grade_language" name="grade_language" required>
                <option value="">-- Select Grade & Language --</option>
                @foreach($teacherAssignments as $ta)
                    <option value="{{ $ta->grade_id }}|{{ $ta->school_language_id }}" {{ old('grade_language', "{$quiz->grade_id}|{$quiz->school_language_id}") == "{$ta->grade_id}|{$ta->school_language_id}" ? 'selected' : '' }}>
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
        
        <div class="form-group">
            <label for="academic_type">Quiz Type *</label>
            <select id="academic_type" name="academic_type" required onchange="toggleLessonSelector()">
                <option value="general" {{ old('academic_type', $quiz->academic_type) == 'general' ? 'selected' : '' }}>General Class Exam</option>
                <option value="lesson" {{ old('academic_type', $quiz->academic_type) == 'lesson' ? 'selected' : '' }}>Lesson Quiz (Attached to a specific lesson)</option>
            </select>
            @error('academic_type')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div id="lesson_selector_container" class="form-group" style="display: none;">
            <label for="lesson_id">Select Lesson *</label>
            <select id="lesson_id" name="lesson_id">
                <option value="">-- Choose Lesson --</option>
                @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" {{ old('lesson_id', $quiz->lesson_id) == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                @endforeach
            </select>
            @error('lesson_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <script>
            function toggleLessonSelector() {
                const type = document.getElementById('academic_type').value;
                const container = document.getElementById('lesson_selector_container');
                const lessonSelect = document.getElementById('lesson_id');
                if (type === 'lesson') {
                    container.style.display = 'block';
                    lessonSelect.setAttribute('required', 'required');
                } else {
                    container.style.display = 'none';
                    lessonSelect.removeAttribute('required');
                }
            }
            // Initial run
            window.addEventListener('DOMContentLoaded', toggleLessonSelector);
        </script>

        @if(in_array(auth()->user()->type, ['admin', 'manager']))
        <div class="form-group">
            <label for="level_id">Or Assign to a Course Track Level</label>
            <select id="level_id" name="level_id">
                <option value="" selected>-- Not assigned --</option>
                @foreach($levels as $l)
                    <option value="{{ $l->id }}" {{ old('level_id', $quiz->level_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                @endforeach
            </select>
            @error('level_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        @else
            @if($quiz->level_id)
            <div class="form-group">
                <label>Common Learning Level</label>
                <input type="hidden" name="level_id" value="{{ $quiz->level_id }}">
                <input type="text" disabled value="This quiz is assigned to a common level and was pre-set.">
            </div>
            @endif
        @endif

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save Changes <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>

@if(in_array(auth()->user()->type, ['admin', 'manager']))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language_id');
    const levelSelect = document.getElementById('level_id');

    if (languageSelect && levelSelect) {
        languageSelect.addEventListener('change', function() {
            const languageId = this.value;
            if (!languageId) {
                levelSelect.innerHTML = '<option value="">-- Not assigned --</option>';
                return;
            }

            levelSelect.innerHTML = '<option value="">Loading levels...</option>';
            levelSelect.disabled = true;

            const url = "{{ route('admin.levels.by_language', ':id') }}".replace(':id', languageId);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    levelSelect.innerHTML = '<option value="">-- Not assigned --</option>';
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
