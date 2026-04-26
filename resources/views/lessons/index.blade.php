@extends('layouts.app')

@php
    $routePrefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher';
@endphp

@section('title', 'Manage Educational Lessons')
@section('page-title', 'Lesson List')

@section('topbar-actions')
    @if(in_array(auth()->user()->type, ['admin', 'manager']))
    @if(request()->has('language_id'))
    <a href="{{ route($routePrefix . '.lessons.index') }}" class="btn btn-secondary" style="margin-right: 10px;">
        <i class='bx bx-arrow-back'></i> Back to Languages
    </a>
    @endif
    <button type="button" class="btn btn-ghost" onclick="document.getElementById('aiLessonModal').style.display = 'flex'" style="border: 2px dashed #a855f7; color: #a855f7; display: inline-flex; align-items: center; gap: 8px;">
        <i class='bx bx-brain'></i> Generate AI Lesson
    </button>
    <a href="{{ route($routePrefix . '.lessons.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Lesson
    </a>
    @endif
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Lesson Content Library</h2>
    </div>

    @if(isset($assignments))
        {{-- Teacher Mode: Grouped by Grade --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">Grade Name</th>
                        <th style="background: none; border: none;">Subject / Language</th>
                        <th style="background: none; border: none;">Total Lessons</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr style="border-bottom: 2px solid var(--border); background: #fff; border-radius: 12px; transition: 0.3s;">
                        <td style="border-radius: 16px 0 0 16px; font-weight: 800;">{{ $assignment->grade->name }}</td>
                        <td>
                            <span class="badge badge-primary">{{ $assignment->schoolLanguage->name }}</span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: var(--text);">
                                <i class='bx bx-book-content' style="color: var(--primary);"></i>
                                {{ $assignment->lessons_count }} Lessons
                            </div>
                        </td>
                        <td style="border-radius: 0 16px 16px 0;">
                            <a href="{{ route('teacher.lessons.by_grade', [$assignment->grade_id, $assignment->school_language_id]) }}" class="btn btn-sm btn-ghost" style="color: var(--accent); font-weight: 800; gap: 8px; padding: 10px 20px; border-radius: 12px; border: 1px solid var(--border);">
                                <i class='bx bx-cog'></i> Manage
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($lessons->count() > 0)
        {{-- Admin Mode: Standard List --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Title</th>
                        <th style="background: none; border: none;">Type (Class / Courses)</th>
                        <th style="background: none; border: none;">Video</th>
                        <th style="background: none; border: none;">Added Date</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessons as $lesson)
                    <tr style="border-bottom: 2px solid var(--border); background: #fff; border-radius: 12px; transition: 0.3s;">
                        <td style="border-radius: 16px 0 0 16px; font-weight: 800; color: var(--primary);">#{{ $lesson->id }}</td>
                        <td style="font-weight: 700;">{{ $lesson->title }}</td>
                        <td>
                            @if($lesson->grade_id && $lesson->school_language_id)
                                <span class="badge badge-primary">{{ $lesson->grade->name ?? '' }} | {{ $lesson->schoolLanguage->name ?? '' }}</span>
                            @elseif($lesson->level_id)
                                <span class="badge badge-accent">{{ $lesson->level->name ?? '' }}</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:var(--text-muted);">Not Specified</span>
                            @endif
                        </td>
                        <td>
                            @if($lesson->video_url)
                                <div style="color:var(--primary); font-weight: 800; display: flex; align-items: center; gap: 4px;"><i class='bx bx-check-circle'></i> Yes</div>
                            @else
                                <div style="color:var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 4px;"><i class='bx bx-x-circle'></i> No</div>
                            @endif
                        </td>
                        <td style="color:var(--text-muted); font-size:14px; font-weight: 600;">{{ $lesson->created_at->format('M d, Y') }}</td>
                        <td style="border-radius: 0 16px 16px 0;">
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route($routePrefix . '.lessons.edit', $lesson->id) }}" class="btn btn-sm btn-ghost" style="padding: 8px; border-radius: 10px;"><i class='bx bx-edit' style="color: var(--accent); font-size: 18px;"></i></a>
                                
                                <form action="{{ route($routePrefix . '.lessons.destroy', $lesson->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="padding: 8px; border-radius: 10px; border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger); font-size: 18px;"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $lessons->links() }}</div>
    @else
        <div class="empty-state">
            <i class='bx bx-book-content'></i>
            <h3>No Course Tracks Lessons</h3>
            <p>Upload global course lessons that will be visible to all students in the courses system.</p>
        </div>
    @endif
</div>

<!-- AI Lesson Generator Modal -->
<div id="aiLessonModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">
    <div style="background:#fff; border-radius: 28px; padding: 40px; width:90%; max-width:500px; box-shadow: var(--shadow-lg); border: 1px solid var(--border);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 30px;">
            <h3 style="margin:0; font-size:1.5rem; font-weight: 900; display:flex; align-items:center; gap:12px; color: var(--text);">
                <i class='bx bx-brain' style="color:#a855f7;"></i> AI Generator
            </h3>
            <button type="button" onclick="document.getElementById('aiLessonModal').style.display='none'" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:2rem;">&times;</button>
        </div>
        
        <form id="aiLessonForm">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 700; color: var(--text); margin-bottom: 8px; display: block;">Lesson Topic</label>
                <input type="text" id="aiTopic" required placeholder="e.g. Past Tense Verbs" style="width:100%; padding:14px; border-radius:14px; border:1px solid var(--border); background:#f8fafc; font-weight: 600; outline:none;">
            </div>
            
            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label style="font-weight: 700; color: var(--text);">Difficulty</label>
                    <select id="aiLevel" style="width:100%; padding:14px; border-radius:14px; border:1px solid var(--border); background:#f8fafc; font-weight: 600; outline:none; margin-top: 8px;">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate" selected>Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700; color: var(--text);">Length</label>
                    <select id="aiLength" style="width:100%; padding:14px; border-radius:14px; border:1px solid var(--border); background:#f8fafc; font-weight: 600; outline:none; margin-top: 8px;">
                        <option value="short">Short</option>
                        <option value="medium" selected>Medium</option>
                        <option value="long">Long</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" id="aiGenerateBtn" class="btn btn-primary" style="width:100%; justify-content:center; padding:16px; background:linear-gradient(135deg, #a855f7, #6366f1); border:none; font-size: 16px;">
                <span>Generate Smart Draft</span> <i class='bx bx-magic-wand'></i>
            </button>
            <div id="aiLoadingDiv" style="display:none; text-align:center; padding:16px; color:#a855f7; font-weight:800; border-radius: 14px; background: rgba(168, 85, 247, 0.05);">
                <i class='bx bx-loader-alt bx-spin'></i> AI is crafting your lesson...
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('aiLessonForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const topic = document.getElementById('aiTopic').value;
    const level = document.getElementById('aiLevel').value;
    const length = document.getElementById('aiLength').value;
    
    const btn = document.getElementById('aiGenerateBtn');
    const loading = document.getElementById('aiLoadingDiv');
    const errorMsg = document.getElementById('aiErrorMsg');
    
    btn.style.display = 'none';
    loading.style.display = 'block';
    errorMsg.style.display = 'none';
    
    try {
        const response = await fetch("{{ route('ai.generate-lesson-draft') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ topic, level, length })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success && data.draft) {
            sessionStorage.setItem('ai_lesson_draft', JSON.stringify(data.draft));
            window.location.href = "{{ route($routePrefix . '.lessons.create') }}";
        } else {
            throw new Error(data.error || 'An error occurred while connecting to AI.');
        }
    } catch (err) {
        errorMsg.innerText = err.message || 'Generation failed. Please try again.';
        errorMsg.style.display = 'block';
        btn.style.display = 'flex';
        loading.style.display = 'none';
    }
});
</script>
@endsection
