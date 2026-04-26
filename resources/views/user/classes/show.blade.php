@extends('layouts.app')

@section('title', 'Class Content: ' . $class->name)
@section('page-title', 'Study')

@section('content')
<div class="class-workspace animate-fade-in">
    <!-- Premium Header Area -->
    <div class="class-header-card shadow-premium">
        <div class="header-main">
            <div class="class-icon-orb">
                <i class='bx bxs-school'></i>
            </div>
            <div class="header-info">
                <h1 class="class-title">{{ $class->name }}</h1>
                <p class="class-subtitle">
                    <span class="school-name">{{ $class->school->name ?? 'Integrated Academy' }}</span>
                    <span class="sep"></span>
                    <span class="teacher-name">Instructor: {{ $class->teacher->name ?? 'Academic Staff' }}</span>
                </p>
                <div class="code-badge">
                    <span class="lab">Class Identity</span>
                    <span class="val">{{ $class->class_code }}</span>
                </div>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-bubble">
                <span class="lab">Lessons</span>
                <span class="val">{{ $class->lessons->count() }}</span>
            </div>
            <div class="stat-bubble accent">
                <span class="lab">Quizzes</span>
                <span class="val">{{ $class->quizzes->count() }}</span>
            </div>
        </div>
    </div>

    <div class="curriculum-grid">
        <!-- Lessons Main Column -->
        <div class="card-section">
            <div class="section-heading">
                <div class="icon-box"><i class='bx bxs-book-reader'></i></div>
                <h2>Study Material</h2>
            </div>
            
            @if($class->lessons->count() > 0)
                <div class="lesson-list">
                    @foreach($class->lessons as $lesson)
                        <div class="lesson-elite-card animate-slide-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                            <div class="card-body">
                                <div class="card-top">
                                    <h3 class="lesson-title">{{ $lesson->title }}</h3>
                                    @if($lesson->video_url)
                                        <span class="video-tag"><i class='bx bxs-video'></i> Video</span>
                                    @endif
                                </div>
                                <p class="lesson-excerpt">{{ Str::limit(strip_tags($lesson->content), 120) }}</p>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('user.lessons.show', $lesson->id) }}" class="btn btn-primary btn-block ripple shadow-primary">
                                    Start Lesson <i class='bx bx-right-arrow-alt'></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-premium">
                    <i class='bx bx-book-open'></i>
                    <p>Curriculum is being prepared. Check back soon!</p>
                </div>
            @endif
        </div>

        <!-- Assessments Column -->
        <div class="card-section">
            <div class="section-heading">
                <div class="icon-box accent"><i class='bx bxs-edit-alt'></i></div>
                <h2>Assessments</h2>
            </div>
            
            @if($class->quizzes->count() > 0)
                <div class="quiz-list">
                    @foreach($class->quizzes as $quiz)
                        <div class="quiz-elite-card animate-slide-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                            <div class="accent-line"></div>
                            <div class="quiz-body">
                                <h3 class="quiz-title">{{ $quiz->title }}</h3>
                                <p class="quiz-meta"><i class='bx bx-list-check'></i> {{ $quiz->questions_count }} Practice Questions</p>
                            </div>
                            <div class="quiz-footer">
                                <a href="{{ route('user.quizzes.take', $quiz->id) }}" class="btn btn-accent btn-block ripple shadow-accent">
                                    Take Quiz <i class='bx bx-pencil'></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state-premium">
                    <i class='bx bx-task-x'></i>
                    <p>No active quizzes for this class.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Class Show: Premium Elite Light Theme                                     */
/* -------------------------------------------------------------------------- */
.class-workspace { padding-bottom: 60px; }

/* Header Card */
.class-header-card {
    background: #ffffff; border-radius: 32px; padding: 40px; 
    border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    display: flex; justify-content: space-between; align-items: center; 
    margin-bottom: 40px; position: relative; overflow: hidden;
}
.class-header-card::after {
    content: ''; position: absolute; right: -50px; bottom: -50px; width: 250px; height: 250px;
    background: radial-gradient(circle, rgba(20, 184, 166, 0.05), transparent 70%); border-radius: 50%;
}

.header-main { display: flex; align-items: center; gap: 30px; position: relative; z-index: 2; }
.class-icon-orb {
    width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), #0d9488);
    border-radius: 22px; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 2.2rem; box-shadow: 0 8px 20px rgba(20, 184, 166, 0.2);
}

.header-info .class-title { font-size: 2.2rem; font-weight: 950; color: var(--text); letter-spacing: -1.5px; margin-bottom: 5px; }
.class-subtitle { font-size: 1rem; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
.class-subtitle .sep { width: 4px; height: 4px; background: #cbd5e1; border-radius: 50%; }

.code-badge { display: inline-flex; overflow: hidden; border-radius: 8px; border: 1px solid var(--border); font-size: 11px; font-weight: 800; }
.code-badge .lab { background: #f8fafc; padding: 4px 10px; color: var(--text-muted); border-right: 1px solid var(--border); text-transform: uppercase; }
.code-badge .val { padding: 4px 10px; color: var(--primary); font-family: 'Outfit', monospace; }

.header-stats { display: flex; gap: 20px; position: relative; z-index: 2; }
.stat-bubble { background: #f8fafc; padding: 12px 24px; border-radius: 18px; text-align: center; min-width: 100px; border: 1px solid transparent; transition: 0.3s; }
.stat-bubble:hover { border-color: var(--primary); transform: translateY(-3px); }
.stat-bubble .lab { display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px; }
.stat-bubble .val { font-size: 1.8rem; font-weight: 900; color: var(--primary); }
.stat-bubble.accent .val { color: var(--accent); }

/* Grid Layout */
.curriculum-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
@media (max-width: 992px) { .curriculum-grid { grid-template-columns: 1fr; } .class-header-card { flex-direction: column; text-align: center; } .header-main { flex-direction: column; } .header-stats { width: 100%; justify-content: center; } }

.section-heading { display: flex; align-items: center; gap: 14px; margin-bottom: 25px; }
.icon-box { width: 44px; height: 44px; border-radius: 12px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; box-shadow: 0 4px 12px rgba(20, 184, 166, 0.2); }
.icon-box.accent { background: var(--accent); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); }
.section-heading h2 { font-size: 1.4rem; font-weight: 900; color: var(--text); }

/* Lesson List */
.lesson-list { display: flex; flex-direction: column; gap: 20px; }
.lesson-elite-card { 
    background: #fff; border: 1px solid var(--border); border-radius: 24px; padding: 25px; 
    transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.lesson-elite-card:hover { border-color: var(--primary); transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.04); }

.card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.lesson-title { font-size: 1.25rem; font-weight: 800; color: var(--text); }
.video-tag { background: rgba(var(--primary-rgb), 0.05); color: var(--primary); padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; display: flex; align-items: center; gap: 5px; }

.lesson-excerpt { font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; font-weight: 500; margin-bottom: 20px; }

/* Quiz List */
.quiz-list { display: flex; flex-direction: column; gap: 20px; }
.quiz-elite-card { 
    background: #fff; border: 1px solid var(--border); border-radius: 24px; padding: 25px; 
    position: relative; overflow: hidden; transition: 0.3s;
}
.quiz-elite-card:hover { border-color: var(--accent); transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.04); }
.accent-line { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--accent); }

.quiz-title { font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 8px; }
.quiz-meta { font-size: 0.9rem; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 20px; }
.quiz-meta i { color: var(--accent); }

/* Common Utilities */
.btn-block { width: 100%; display: flex; justify-content: center; padding: 14px; border-radius: 12px; font-weight: 800; }
.empty-state-premium { padding: 50px 20px; text-align: center; border: 2px dashed var(--border); border-radius: 24px; color: var(--text-muted); }
.empty-state-premium i { font-size: 3rem; margin-bottom: 10px; opacity: 0.3; }

.shadow-primary { box-shadow: 0 6px 15px rgba(20, 184, 166, 0.2); }
.shadow-accent { box-shadow: 0 6px 15px rgba(245, 158, 11, 0.2); }
</style>
@endsection
