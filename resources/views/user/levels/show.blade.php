@extends('layouts.app')

@section('title', 'Level: ' . $level->name)
@section('page-title', 'Mastering ' . $level->name)

@section('content')
<!-- Level Hero Section: Premium Light Version -->
<div class="level-hero-card animate-fade-in">
    <div class="hero-content">
        <div class="hero-main">
            <div class="level-icon-large">
                <i class='bx bxs-layer'></i>
            </div>
            <div class="hero-text">
                <nav class="breadcrumb-flat">
                    <a href="{{ route('user.dashboard') }}">Dashboard</a>
                    <span>/</span>
                    <span class="active">{{ $level->name }}</span>
                </nav>
                <h1>Explore Level: {{ $level->name }} 🚀</h1>
                <p>Advance through the roadmap, complete lessons, and pass quizzes to master this level.</p>
            </div>
        </div>
        <div class="hero-stats">
            <div class="xp-requirement-box">
                <span class="label">Access Requirement</span>
                <div class="xp-badge-premium {{ $xp >= $level->required_xp ? 'is-unlocked' : 'is-needed' }}">
                    <i class='bx bxs-zap'></i> {{ $level->required_xp }} XP
                </div>
                @if($xp >= $level->required_xp)
                    <div class="xp-status-success"><i class='bx bxs-check-circle'></i> Requirement Met</div>
                @else
                    <div class="xp-status-needed">Need {{ $level->required_xp - $xp }} more XP</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($isEnrolled)
<div class="level-layout-grid animate-slide-up">
    <!-- Learning Path Section -->
    <div class="path-section">
        <div class="premium-section-header">
            <div class="icon-box"><i class='bx bxs-map-alt'></i></div>
            <div class="text-box">
                <h2>Learning Roadmap</h2>
                <p>Step-by-step path to mastery</p>
            </div>
        </div>

        @if($level->lessons->count() > 0)
            <div class="path-timeline">
                @foreach($level->lessons as $index => $lesson)
                    @php
                        $lessonCompleted = auth()->user()->completedLessons()->where('lesson_id', $lesson->id)->where('passed', true)->exists();
                        $isAccessible = true; 
                        $req = $lesson->required_tier ?? 'free';
                        $blockedByTier = false;
                        if ($req === 'pro' && !auth()->user()->isPro()) $blockedByTier = true;
                        if ($req === 'basic' && auth()->user()->isFree()) $blockedByTier = true;
                        
                        if ($blockedByTier) $isAccessible = false;
                        $isCurrent = $isAccessible && !$lessonCompleted;
                    @endphp

                    <div class="path-step {{ $lessonCompleted ? 'completed' : ($isCurrent ? 'active' : 'locked') }}">
                        <div class="step-indicator">
                            <div class="indicator-circle">
                                @if($lessonCompleted) <i class='bx bxs-check-circle'></i> @elseif($isCurrent) <i class='bx bx-play'></i> @else <i class='bx bxs-lock'></i> @endif
                            </div>
                            @if($index < $level->lessons->count() - 1)
                                <div class="indicator-line"></div>
                            @endif
                        </div>
                        
                        <div class="lesson-compact-card">
                            <div class="card-body">
                                <div class="lesson-meta">
                                    <span class="lesson-num">Lesson {{ $index + 1 }}</span>
                                    @if($lesson->video_url) <span class="video-pill"><i class='bx bxs-video'></i> Video</span> @endif
                                </div>
                                <h3>{{ $lesson->title }}</h3>
                                <p>{{ Str::limit(strip_tags($lesson->content), 100) }}</p>
                            </div>
                            <div class="card-action">
                                @if($isAccessible)
                                    <a href="{{ route('user.lessons.show', $lesson->id) }}" class="btn {{ $lessonCompleted ? 'btn-ghost' : 'btn-primary' }} ripple">
                                        {{ $lessonCompleted ? 'Review' : 'Start Now' }}
                                    </a>
                                @else
                                    <button class="btn btn-locked" disabled>
                                        {{ ucfirst($req) }} Required <i class='bx bxs-star'></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-light">
                <i class='bx bx-ghost'></i>
                <p>Curriculum is being updated. Check back soon!</p>
            </div>
        @endif
    </div>

    <!-- Final Assessments Sidebar -->
    <div class="assessment-section">
        <div class="premium-section-header">
            <div class="icon-box-accent"><i class='bx bxs-trophy'></i></div>
            <div class="text-box">
                <h2>Certify Skills</h2>
                <p>Pass to unlock next level</p>
            </div>
        </div>

        @if($level->quizzes->count() > 0)
            <div class="quiz-grid">
                @foreach($level->quizzes as $quiz)
                    <div class="quiz-card-light">
                        <div class="quiz-header">
                            <div class="quiz-badge">Level Exam</div>
                            <i class='bx bxs-award-star' style="color: var(--accent);"></i>
                        </div>
                        <h3>{{ $quiz->title }}</h3>
                        <div class="quiz-info">
                            <span><i class='bx bx-list-ul'></i> {{ $quiz->questions_count }} Qs</span>
                            <span><i class='bx bx-time-five'></i> 10m</span>
                        </div>
                        <a href="{{ route('user.quizzes.take', $quiz->id) }}" class="btn btn-accent btn-block shadow-accent">
                            Take Final Quiz <i class='bx bx-chevron-right'></i>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-sidebar-light">
                <p>No exams available for this level yet.</p>
            </div>
        @endif
    </div>
</div>
@else
<!-- Locked Content Experience -->
<div class="locked-experience shadow-premium animate-slide-up">
    <div class="lock-icon-area">
        <div class="lock-ring"></div>
        <i class='bx bxs-lock-open-alt'></i>
    </div>
    <h2>Unlock Your Potential</h2>
    <p>
        @if($canEnroll)
            You've earned enough XP! Unlock this level now to explore new lessons and challenges.
        @else
            Keep learning to reach <strong>{{ $level->required_xp }} XP</strong> and unlock this level.
        @endif
    </p>
    <div class="lock-cta">
        @if($canEnroll)
            <form action="{{ route('user.levels.enroll', $level->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg shadow-glow">
                    Unlock Level Now <i class='bx bxs-zap'></i>
                </button>
            </form>
        @else
            <a href="{{ route('user.dashboard') }}" class="btn btn-ghost btn-lg">Back to Dashboard</a>
        @endif
    </div>
</div>
@endif

<style>
/* -------------------------------------------------------------------------- */
/*  Level Hero Section: Cheerful & Bright                                     */
/* -------------------------------------------------------------------------- */
.level-hero-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 40px;
    margin-bottom: 40px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    position: relative;
    overflow: hidden;
}
.level-hero-card::after {
    content: ''; position: absolute; top: -50px; right: -50px; width: 250px; height: 250px;
    background: radial-gradient(circle, rgba(20, 184, 166, 0.05), transparent 70%); border-radius: 50%;
}

.hero-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 30px; position: relative; z-index: 2; }
.hero-main { display: flex; align-items: center; gap: 24px; }

.level-icon-large {
    width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), #0d9488);
    border-radius: 20px; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 2.2rem; box-shadow: 0 8px 15px rgba(20, 184, 166, 0.2);
}

.hero-text h1 { font-size: 2rem; font-weight: 900; color: var(--text); letter-spacing: -1px; margin-bottom: 5px; }
.hero-text p { font-size: 1.1rem; color: var(--text-muted); font-weight: 500; }

.breadcrumb-flat { display: flex; gap: 8px; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; }
.breadcrumb-flat a { color: var(--text-muted); text-decoration: none; }
.breadcrumb-flat a:hover { color: var(--primary); }
.breadcrumb-flat .active { color: var(--primary); }

.xp-requirement-box { text-align: right; }
.xp-requirement-box .label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; display: block; margin-bottom: 8px; }

.xp-badge-premium {
    padding: 12px 28px; border-radius: 20px; font-size: 1.5rem; font-weight: 900; display: inline-flex; align-items: center; gap: 8px; border: 2px solid;
}
.xp-badge-premium.is-unlocked { background: rgba(20, 184, 166, 0.05); border-color: var(--primary); color: var(--primary); }
.xp-badge-premium.is-needed { background: rgba(245, 158, 11, 0.05); border-color: var(--accent); color: var(--accent); }

.xp-status-success { margin-top: 10px; font-size: 0.9rem; font-weight: 700; color: var(--success); }
.xp-status-needed { margin-top: 10px; font-size: 0.9rem; font-weight: 700; color: var(--accent); }

/* -------------------------------------------------------------------------- */
/*  Grid & Content Sections                                                   */
/* -------------------------------------------------------------------------- */
.level-layout-grid { display: grid; grid-template-columns: 1fr 340px; gap: 40px; }
@media (max-width: 992px) { .level-layout-grid { grid-template-columns: 1fr; } }

.premium-section-header { display: flex; align-items: center; gap: 16px; margin-bottom: 30px; }
.icon-box, .icon-box-accent {
    width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff;
}
.icon-box { background: var(--primary); box-shadow: 0 4px 10px rgba(20, 184, 166, 0.2); }
.icon-box-accent { background: var(--accent); box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2); }

.text-box h2 { font-size: 1.4rem; font-weight: 800; color: var(--text); }
.text-box p { font-size: 0.9rem; color: var(--text-muted); font-weight: 600; }

/* -------------------------------------------------------------------------- */
/*  Roadmap Timeline: Modern Light Version                                    */
/* -------------------------------------------------------------------------- */
.path-timeline { position: relative; padding-left: 20px; }
.path-step { display: flex; gap: 30px; margin-bottom: 40px; position: relative; }

.step-indicator { display: flex; flex-direction: column; align-items: center; position: relative; width: 32px; flex-shrink: 0; }
.indicator-circle {
    width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; border: 4px solid #fff;
    box-shadow: 0 0 0 1px #e2e8f0; display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem; color: var(--text-muted); z-index: 2; transition: 0.3s;
}
.indicator-line { position: absolute; top: 32px; bottom: -40px; width: 4px; background: #e2e8f0; z-index: 1; border-radius: 2px; }

.path-step.completed .indicator-circle { background: var(--success); color: #fff; box-shadow: 0 0 0 1px var(--success); }
.path-step.completed .indicator-line { background: var(--success); }
.path-step.active .indicator-circle { background: var(--primary); color: #fff; box-shadow: 0 0 0 1px var(--primary); }

.lesson-compact-card {
    background: #ffffff; border: 1px solid var(--border); border-radius: 24px; padding: 25px;
    width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 24px;
    transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 6px rgba(0,0,0,0.02);
}
.lesson-compact-card:hover { transform: translateX(8px); border-color: var(--primary); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

.lesson-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.lesson-num { font-size: 10px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; }
.video-pill { padding: 4px 10px; border-radius: 6px; background: #f1f5f9; color: var(--text-muted); font-size: 10px; font-weight: 800; display: flex; align-items: center; gap: 4px; }

.card-body h3 { font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.card-body p { font-size: 0.95rem; color: var(--text-muted); line-height: 1.5; font-weight: 500; }

/* -------------------------------------------------------------------------- */
/*  Quiz Card: Cheerful Versions                                              */
/* -------------------------------------------------------------------------- */
.quiz-grid { display: flex; flex-direction: column; gap: 20px; }
.quiz-card-light {
    background: #ffffff; border-radius: 24px; padding: 28px; border: 1px solid var(--border);
    box-shadow: 0 4px 15px rgba(0,0,0,0.02); position: relative; overflow: hidden;
}
.quiz-card-light::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--accent); }

.quiz-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.quiz-badge { font-size: 10px; font-weight: 800; color: var(--accent); background: rgba(245, 158, 11, 0.05); padding: 4px 10px; border-radius: 6px; }

.quiz-card-light h3 { font-size: 1.3rem; font-weight: 800; color: var(--text); margin-bottom: 12px; }
.quiz-info { display: flex; gap: 16px; margin-bottom: 24px; font-size: 0.9rem; color: var(--text-muted); font-weight: 600; }
.quiz-info i { color: var(--primary); }

/* -------------------------------------------------------------------------- */
/*  Locked Experience                                                         */
/* -------------------------------------------------------------------------- */
.locked-experience { text-align: center; padding: 80px 40px; background: #fff; border-radius: 40px; border: 1px solid var(--border); max-width: 700px; margin: 60px auto; }
.lock-icon-area { width: 120px; height: 120px; margin: 0 auto 30px; position: relative; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: var(--primary); }
.lock-ring { position: absolute; inset: 0; border: 4px solid var(--primary); border-radius: 50%; opacity: 0.15; animation: pulseGlow 2s infinite; }

.locked-experience h2 { font-size: 2.2rem; font-weight: 900; color: var(--text); margin-bottom: 15px; }
.locked-experience p { font-size: 1.2rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 40px; }

/* -------------------------------------------------------------------------- */
/*  Utilities                                                                 */
/* -------------------------------------------------------------------------- */
.shadow-accent { box-shadow: 0 6px 15px rgba(245, 158, 11, 0.2); }
.shadow-glow { box-shadow: 0 10px 20px rgba(20, 184, 166, 0.2); }
.btn-block { width: 100%; display: flex; justify-content: center; padding: 14px; font-weight: 800; border-radius: 12px; }
.btn-lg { padding: 18px 45px; font-size: 1.2rem; font-weight: 800; border-radius: 20px; }

@media (max-width: 600px) {
    .lesson-compact-card { flex-direction: column; align-items: flex-start; }
    .card-action { width: 100%; }
    .card-action .btn { width: 100%; justify-content: center; }
}
</style>
@endsection
