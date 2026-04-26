@extends('layouts.app')

@section('title', 'Manage Lessons for ' . $grade->name)
@section('page-title', $grade->name . ' - ' . $language->name)

@section('topbar-actions')
    <button type="button" class="btn btn-ghost" onclick="document.getElementById('aiLessonModal').style.display = 'flex'" style="border: 2px dashed #a855f7; color: #a855f7; display: inline-flex; align-items: center; gap: 8px;">
        <i class='bx bx-brain'></i> Generate AI Lesson
    </button>
    <a href="{{ route('teacher.lessons.create', ['grade_language' => $grade->id . '|' . $language->id]) }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Lesson
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 class="card-title">Grade Lessons Library</h2>
            <p style="color:var(--text-muted); font-size:14px; margin-top:5px;">Managing content for {{ $grade->name }} ({{ $language->name }})</p>
        </div>
        <a href="{{ route('teacher.lessons.index') }}" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Back to Grades</a>
    </div>

    @if($lessons->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Title</th>
                        <th style="background: none; border: none;">Video</th>
                        <th style="background: none; border: none;">Added Date</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessons as $lesson)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px;">{{ $lesson->id }}</td>
                        <td style="font-weight:700;">{{ $lesson->title }}</td>
                        <td>
                            @if($lesson->video_url)
                                <span style="color:var(--accent);"><i class='bx bx-play-circle'></i> Available</span>
                            @else
                                <span style="color:var(--text-muted);">No Video</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted); font-size:14px;">{{ $lesson->created_at->format('Y-m-d') }}</td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('teacher.lessons.edit', $lesson->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            
                            <form action="{{ route('teacher.lessons.destroy', $lesson->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this lesson permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $lessons->links() }}</div>
    @else
        <div class="empty-state">
            <i class='bx bx-book-bookmark'></i>
            <h3>No lessons added yet</h3>
            <p>You haven't added any lessons for this grade yet. Start creating content now!</p>
        </div>
    @endif
</div>

{{-- AI Modal --}}
<div id="aiLessonModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--bg); border-radius: 20px; padding: 30px; width:90%; max-width:500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid var(--border);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h3 style="margin:0; font-size:1.3rem; display:flex; align-items:center; gap:8px;">
                <i class='bx bx-brain' style="color:#a855f7;"></i> Generate AI Lesson
            </h3>
            <button type="button" onclick="document.getElementById('aiLessonModal').style.display='none'" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:1.5rem;">&times;</button>
        </div>
        
        <form id="aiLessonForm">
            <input type="hidden" id="aiGradeLanguage" value="{{ $grade->id }}|{{ $language->id }}">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Lesson Topic (e.g., Past Tense Verbs) *</label>
                <input type="text" id="aiTopic" required placeholder="Write the lesson topic..." style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--border); background:var(--bg2); color:var(--text); margin-top:5px; outline:none;">
            </div>
            
            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>Difficulty</label>
                    <select id="aiLevel" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--border); background:var(--bg2); color:var(--text); margin-top:5px; outline:none;">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate" selected>Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lesson Length</label>
                    <select id="aiLength" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--border); background:var(--bg2); color:var(--text); margin-top:5px; outline:none;">
                        <option value="short">Short</option>
                        <option value="medium" selected>Medium</option>
                        <option value="long">Long</option>
                    </select>
                </div>
            </div>
            
            <div id="aiErrorMsg" style="display:none; color:var(--danger); margin-bottom:15px; font-size:0.9rem;"></div>
            
            <button type="submit" id="aiGenerateBtn" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; background:linear-gradient(135deg, #a855f7, #6366f1); border:none;">
                <span>Generate Draft</span> <i class='bx bx-magic-wand'></i>
            </button>
            <div id="aiLoadingDiv" style="display:none; text-align:center; padding:14px; color:#a855f7; font-weight:700;">
                <i class='bx bx-loader-alt bx-spin'></i> Generating lesson draft... please wait
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
    const gradeLanguage = document.getElementById('aiGradeLanguage').value;
    
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
            window.location.href = "{{ route('teacher.lessons.create') }}?grade_language=" + encodeURIComponent(gradeLanguage);
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
