@extends('layouts.app')

@section('title', 'Teacher Content | ' . $teacher->name)
@section('page-title', $language->name . ' with ' . $teacher->name)

@section('content')
<div class="teacher-view-container animate-fade-in">
    <!-- Teacher Brand Header: Premium Light -->
    <div class="teacher-brand-header">
        <div class="brand-left">
            <div class="instructor-avatar">
                <div class="avatar-ring"></div>
                <span>{{ mb_substr($teacher->name, 0, 1) }}</span>
            </div>
            <div class="instructor-meta">
                <div class="badge-row">
                    <span class="pill-badge primary-pill"><i class='bx bxs-graduation'></i> {{ $grade->name }}</span>
                    <span class="pill-badge accent-pill"><i class='bx bxs-book-heart'></i> {{ $language->name }}</span>
                </div>
                <h1>Instructor: {{ $teacher->name }}</h1>
                <p class="instructor-subtitle">Member of the Academic Staff</p>
            </div>
        </div>
        <div class="brand-right">
            <button class="rankings-trigger shadow-accent ripple" onclick="openClassLeaderboard()">
                <div class="trigger-icon"><i class='bx bxs-trophy'></i></div>
                <div class="trigger-text">
                    <span class="t-lab">Hall of Fame</span>
                    <span class="t-val">Class Rankings</span>
                </div>
            </button>
            <div class="quick-stats">
                <div class="q-stat">
                    <span class="v">{{ $lessons->count() }}</span>
                    <span class="l">Lessons</span>
                </div>
                <div class="q-stat">
                    <span class="v">{{ $generalQuizzes->count() }}</span>
                    <span class="l">Exams</span>
                </div>
            </div>
        </div>
    </div>

    <div class="teacher-grid-layout">
        <!-- Lessons Main Column -->
        <div class="curriculum-area">
            <div class="area-header">
                <div class="header-icon"><i class='bx bxs-collection'></i></div>
                <h2>Course Curriculum</h2>
            </div>

            <div class="lesson-stack">
                @forelse($lessons as $index => $lesson)
                    @php
                        $isCompleted = auth()->user()->completedLessons()->where('lesson_id', $lesson->id)->where('passed', true)->exists();
                    @endphp
                    <div class="lesson-wrapper">
                        <div class="lesson-wide-premium {{ $isCompleted ? 'is-completed' : '' }}">
                            <div class="ordinal">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="content-box">
                                <h3>{{ $lesson->title }}</h3>
                                <p>{{ Str::limit(strip_tags($lesson->content), 120) }}</p>
                            </div>
                            <div class="action-box">
                                @if($isCompleted)
                                    <div class="completion-stamp"><i class='bx bxs-check-shield'></i> Mastery Achieved</div>
                                @endif
                                <div class="button-group">
                                    @if($lesson->quizzes->count() > 0)
                                        <button class="btn-drawer-toggle" onclick="toggleLessonQuizzes(this, 'lq-{{ $lesson->id }}')">
                                            <i class='bx bxs-brain'></i> {{ $lesson->quizzes->count() }} Quizzes <i class='bx bx-chevron-down arrow'></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('user.lessons.show', $lesson->id) }}" class="btn btn-primary ripple shadow-primary">
                                        @if($isCompleted) Review Lesson @else Enter Class @endif
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Dynamic Quiz Drawer --}}
                        @if($lesson->quizzes->count() > 0)
                        <div id="lq-{{ $lesson->id }}" class="quiz-expansion-drawer">
                            <div class="drawer-inner">
                                <span class="drawer-title"><i class='bx bx-task'></i> Check for Understanding</span>
                                <div class="drawer-grid">
                                    @foreach($lesson->quizzes as $q)
                                        <div class="drawer-quiz-card">
                                            <div class="dq-text">
                                                <span class="dq-label">{{ $q->title }}</span>
                                                <span class="dq-meta">{{ $q->questions->count() }} Questions</span>
                                            </div>
                                            <a href="{{ route('user.quizzes.take', $q->id) }}" class="btn-quiz-start">Solve <i class='bx bx-pencil'></i></a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="curriculum-empty">
                        <i class='bx bx-calendar-edit'></i>
                        <p>Your instructor is building the next learning blocks. Stay tuned!</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar Exams area -->
        <div class="assessments-area">
            <div class="area-header">
                <div class="header-icon accent-bg"><i class='bx bxs-graduation'></i></div>
                <h2>General Exams</h2>
            </div>

            <div class="exam-list">
                @forelse($generalQuizzes as $quiz)
                    <div class="exam-pill-card">
                        <div class="pill-left">
                            <h4>{{ $quiz->title }}</h4>
                            <span class="p-meta">{{ $quiz->questions()->count() }} Questions</span>
                        </div>
                        <a href="{{ route('user.quizzes.take', $quiz->id) }}" class="pill-btn ripple">
                            <i class='bx bx-right-arrow-alt'></i>
                        </a>
                    </div>
                @empty
                    <div class="exams-empty-box">
                        <p>No major exams scheduled yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Hall of Fame Modal: Light Premium --}}
<div id="class-leaderboard-modal" class="custom-modal-backdrop" style="display:none;">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class='bx bxs-trophy' style="color: var(--accent);"></i> Hall of Fame</h3>
            <button class="close-modal" onclick="closeClassLeaderboard()">&times;</button>
        </div>
        <div id="leaderboard-loading" class="modal-loader">
            <div class="spinner"></div>
            <p>Gathering the elite...</p>
        </div>
        <div id="leaderboard-content" class="modal-scroller" style="display:none;">
            {{-- Content injected via JS --}}
        </div>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Teacher Page: Premium Light Design System                                 */
/* -------------------------------------------------------------------------- */
.teacher-view-container { padding-bottom: 60px; }

/* Header Brand */
.teacher-brand-header {
    background: #ffffff; border-radius: 32px; padding: 45px; 
    border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    display: flex; justify-content: space-between; align-items: center; 
    margin-bottom: 45px; position: relative; overflow: hidden;
}
.teacher-brand-header::before {
    content: ''; position: absolute; left: -100px; bottom: -100px; width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(20, 184, 166, 0.05), transparent 70%); border-radius: 50%;
}

.brand-left { display: flex; align-items: center; gap: 30px; position: relative; z-index: 2; }
.instructor-avatar { 
    width: 90px; height: 90px; border-radius: 24px; position: relative;
    background: linear-gradient(135deg, var(--primary), #0d9488); 
    display: flex; align-items: center; justify-content: center;
    font-size: 2.8rem; font-weight: 900; color: #fff;
}
.avatar-ring { position: absolute; inset: -5px; border: 2px dashed var(--primary); border-radius: 28px; opacity: 0.3; }

.instructor-meta h1 { font-size: 2.4rem; font-weight: 900; color: var(--text); letter-spacing: -1px; margin: 8px 0 4px; }
.instructor-subtitle { color: var(--text-muted); font-weight: 600; font-size: 1.05rem; }

.badge-row { display: flex; gap: 10px; }
.pill-badge { padding: 4px 14px; border-radius: 50px; font-size: 11px; font-weight: 800; display: flex; align-items: center; gap: 6px; }
.primary-pill { background: rgba(20, 184, 166, 0.1); color: var(--primary); }
.accent-pill { background: rgba(245, 158, 11, 0.1); color: var(--accent); }

.brand-right { display: flex; align-items: center; gap: 30px; position: relative; z-index: 2; }
.rankings-trigger {
    background: #fff; border: 1px solid var(--accent); padding: 12px 25px; border-radius: 20px;
    display: flex; align-items: center; gap: 15px; cursor: pointer; transition: 0.3s;
}
.rankings-trigger:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2); }
.trigger-icon { font-size: 2rem; color: var(--accent); }
.trigger-text { text-align: left; display: flex; flex-direction: column; }
.trigger-text .t-lab { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); }
.trigger-text .t-val { font-size: 1.1rem; font-weight: 800; color: var(--text); }

.quick-stats { display: flex; gap: 20px; }
.q-stat { background: #f8fafc; padding: 12px 20px; border-radius: 18px; text-align: center; min-width: 90px; }
.q-stat .v { display: block; font-size: 1.5rem; font-weight: 900; color: var(--text); }
.q-stat .l { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }

/* Content Grid */
.teacher-grid-layout { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }
@media (max-width: 992px) { .teacher-grid-layout { grid-template-columns: 1fr; } .brand-right { flex-direction: column; align-items: center; width: 100%; } .rankings-trigger { width: 100%; justify-content: center; } }

.area-header { display: flex; align-items: center; gap: 14px; margin-bottom: 30px; }
.header-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; }
.accent-bg { background: var(--accent); }
.area-header h2 { font-size: 1.5rem; font-weight: 900; color: var(--text); }

/* Lesson Cards */
.lesson-stack { display: flex; flex-direction: column; gap: 20px; }
.lesson-wide-premium {
    background: #ffffff; border: 1px solid var(--border); border-radius: 24px; padding: 28px;
    display: flex; align-items: center; gap: 30px; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.lesson-wide-premium:hover { border-color: var(--primary); transform: translateX(8px); box-shadow: 0 10px 25px rgba(0,0,0,0.04); }

.ordinal { font-size: 1.8rem; font-weight: 900; color: #f1f5f9; font-family: 'Outfit', sans-serif; flex-shrink: 0; }
.content-box { flex: 1; }
.content-box h3 { font-size: 1.35rem; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.content-box p { font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; font-weight: 500; }

.action-box { display: flex; flex-direction: column; align-items: flex-end; gap: 12px; }
.completion-stamp { font-size: 11px; font-weight: 800; color: var(--success); display: flex; align-items: center; gap: 6px; }

.button-group { display: flex; gap: 12px; }
.btn-drawer-toggle {
    background: #fff; border: 1px solid var(--border); color: var(--text-muted); 
    padding: 10px 18px; border-radius: 14px; font-size: 13px; font-weight: 800; 
    cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;
}
.btn-drawer-toggle:hover { border-color: var(--accent); color: var(--accent); }
.btn-drawer-toggle .arrow { transition: transform 0.3s; }
.btn-drawer-toggle.active .arrow { transform: rotate(180deg); }

/* Quiz Drawer */
.quiz-expansion-drawer { display: none; margin-top: -10px; animation: slideDown 0.3s ease-out; }
.drawer-inner { background: #f8fafc; border: 1px solid var(--border); border-top: none; border-radius: 0 0 24px 24px; padding: 25px 30px 30px; }
.drawer-title { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.drawer-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 15px; }

.drawer-quiz-card {
    background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 18px;
    display: flex; justify-content: space-between; align-items: center; transition: 0.2s;
}
.drawer-quiz-card:hover { border-color: var(--primary); transform: translateY(-3px); }
.dq-label { display: block; font-weight: 800; color: var(--text); font-size: 1rem; }
.dq-meta { font-size: 11px; font-weight: 600; color: var(--text-muted); }
.btn-quiz-start { background: rgba(20, 184, 166, 0.05); color: var(--primary); padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 800; }

/* Assessments (Sidebar) */
.exam-list { display: flex; flex-direction: column; gap: 15px; }
.exam-pill-card {
    background: #fff; border: 1px solid var(--border); border-radius: 20px; padding: 20px;
    display: flex; justify-content: space-between; align-items: center; transition: 0.2s;
}
.exam-pill-card:hover { border-color: var(--accent); transform: scale(1.02); }
.pill-left h4 { font-size: 1.1rem; font-weight: 800; color: var(--text); margin-bottom: 4px; }
.p-meta { font-size: 12px; font-weight: 600; color: var(--text-muted); }
.pill-btn {
    width: 42px; height: 42px; border-radius: 14px; background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
}
.exam-pill-card:hover .pill-btn { background: var(--accent); }

/* Hall of Fame Modal */
.custom-modal-backdrop { 
    position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); 
    backdrop-filter: blur(8px); z-index: 9999; display: flex; align-items: center; justify-content: center;
    animation: fadeIn 0.3s ease;
}
.modal-card { 
    background: #fff; border-radius: 32px; width: 90%; max-width: 500px; 
    overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.12); border: 1px solid var(--border);
}
.modal-header { padding: 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { font-size: 1.6rem; font-weight: 900; color: var(--text); margin: 0; }
.close-modal { font-size: 2.2rem; color: var(--text-muted); background: none; border: none; cursor: pointer; }
.modal-scroller { max-height: 450px; overflow-y: auto; padding: 20px; }

/* Generic Classes */
.curriculum-empty, .exams-empty-box { 
    padding: 60px 40px; text-align: center; border: 2px dashed var(--border); border-radius: 32px; color: var(--text-muted);
}
.shadow-primary { box-shadow: 0 4px 12px rgba(20, 184, 166, 0.2); }
.shadow-accent { box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); }

@keyframes slideDown { from { opacity: 0; transform: translateY(-15px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.spinner { 
    width: 40px; height: 40px; border: 4px solid #f1f5f9; border-top: 4px solid var(--primary); 
    border-radius: 50%; margin: 40px auto; animation: spin 1s linear infinite; 
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
function toggleLessonQuizzes(btn, drawerId) {
    const drawer = document.getElementById(drawerId);
    const isVisible = drawer.style.display === 'block';
    
    // Close all other drawers
    document.querySelectorAll('.quiz-expansion-drawer').forEach(d => {
        if (d.id !== drawerId) {
            d.style.display = 'none';
        }
    });
    document.querySelectorAll('.btn-drawer-toggle').forEach(b => {
        if (b !== btn) {
            b.classList.remove('active');
        }
    });
    
    if (isVisible) {
        drawer.style.display = 'none';
        btn.classList.remove('active');
    } else {
        drawer.style.display = 'block';
        btn.classList.add('active');
    }
}

async function openClassLeaderboard() {
    const modal = document.getElementById('class-leaderboard-modal');
    modal.style.display = 'flex';
    document.getElementById('leaderboard-loading').style.display = 'block';
    document.getElementById('leaderboard-content').style.display = 'none';

    try {
        const response = await fetch("{{ route('user.class_leaderboard', [$teacher->id, $language->id]) }}");
        const data = await response.json();
        
        const content = document.getElementById('leaderboard-content');
        if (data.leaderboard && data.leaderboard.length > 0) {
            let html = '<div style="display:flex; flex-direction:column; gap:12px;">';
            data.leaderboard.forEach((student, index) => {
                const isTop = index < 3;
                const badges = ['🥇', '🥈', '🥉'];
                html += `
                    <div style="display:flex; align-items:center; gap:16px; padding:16px; background:#f8fafc; border-radius:20px; border:1px solid ${isTop ? 'var(--accent)' : 'var(--border)'};">
                        <div style="width:32px; text-align:center; font-weight:900; font-size:1.2rem; color:var(--text-muted);">
                            ${isTop ? badges[index] : index + 1}
                        </div>
                        <div style="width:48px; height:48px; border-radius:14px; background:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:800; color:#fff; font-size: 1.4rem;">
                            ${student.avatar}
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:800; color:var(--text); font-size:1.1rem;">${student.name}</div>
                            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Student Learner</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:900; color:var(--accent); font-size:1.4rem;">${student.score}</div>
                            <div style="font-size:10px; font-weight:800; color:var(--text-muted);">XP ACCUMULATED</div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            content.innerHTML = html;
        } else {
            content.innerHTML = '<div style="text-align:center; padding:50px; color:var(--text-muted); opacity:0.6;"><i class="bx bx-info-circle" style="font-size:50px;"></i><p style="font-weight:700; margin-top:10px;">No points earned yet.</p></div>';
        }
        
        document.getElementById('leaderboard-loading').style.display = 'none';
        content.style.display = 'block';
    } catch (err) {
        document.getElementById('leaderboard-loading').innerHTML = '<p style="color:var(--danger); font-weight:800; padding:40px; text-align:center;">Failed to connect to hall of fame.</p>';
    }
}

function closeClassLeaderboard() {
    document.getElementById('class-leaderboard-modal').style.display = 'none';
}
</script>
@endsection
