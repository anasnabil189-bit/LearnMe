@extends('layouts.app')

@section('title', 'Assign Teachers')
@section('page-title', 'Academic Staff')

@section('topbar-actions')
    <a href="{{ route('school.teacher-assignments.index') }}" class="btn btn-ghost"><i class='bx bx-left-arrow-alt'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Manage Teacher Assignments</h2>
    </div>

    <form method="POST" action="{{ route('school.teacher-assignments.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="teacher_id">Select Teacher *</label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">-- Choose a Teacher --</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            @error('teacher_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label>Select Grades and Languages</label>
            <p style="color:var(--text-muted); font-size:13px; margin-bottom:10px;">Choose which classes this teacher will handle.</p>
            
            <div style="display:flex; flex-direction:column; gap:15px; background:var(--bg2); padding:15px; border-radius:8px; border:1px solid var(--border);">
                @foreach($grades as $grade)
                    @if($grade->schoolLanguages->count() > 0)
                        <div>
                            <strong style="display:block; margin-bottom:5px; color:var(--text);">{{ $grade->name }}</strong>
                            <div style="display:flex; gap:15px; flex-wrap:wrap;">
                                @foreach($grade->schoolLanguages as $lang)
                                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:normal; margin:0; cursor:pointer;">
                                        <input type="checkbox" name="grade_language[]" value="{{ $grade->id }}|{{ $lang->id }}" 
                                        @if(request('teacher_id'))
                                            @php
                                                $teacher = $teachers->firstWhere('id', request('teacher_id'));
                                                $hasAssignment = $teacher ? $teacher->teacherAssignments->where('grade_id', $grade->id)->where('school_language_id', $lang->id)->isNotEmpty() : false;
                                            @endphp
                                            {{ $hasAssignment ? 'checked' : '' }}
                                        @endif
                                        >
                                        {{ $lang->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                @if($grades->isEmpty())
                    <p style="font-size: 13px; color: var(--accent);">No grades configured yet.</p>
                @endif
            </div>
            @error('grade_language')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save Assignments <i class='bx bx-check'></i>
        </button>
    </form>
</div>

<script>
    document.getElementById('teacher_id').addEventListener('change', function() {
        if (this.value) {
            window.location.href = "{{ route('school.teacher-assignments.create') }}?teacher_id=" + this.value;
        } else {
            window.location.href = "{{ route('school.teacher-assignments.create') }}";
        }
    });
</script>
@endsection
