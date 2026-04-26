@extends('layouts.app')

@section('title', 'Student Area | Learnme')
@section('page-title', 'Interactive Dashboard')

@section('content')
<!-- Header Section: Premium Glassmorphism -->
<div class="dashboard-header animate-slide-up">
    <div class="header-content">
        <div class="header-text">
            <h1>Welcome back, {{ auth()->user()->name }} 🚀</h1>
            <p>Ready for a new learning adventure today? Keep going!</p>
        </div>
        <div class="xp-showcase">
            <div class="xp-pill">
                <div class="xp-icon"><i class='bx bxs-zap'></i></div>
                <div class="xp-details">
                    <span class="xp-label">Learning XP</span>
                    <span class="xp-value">{{ $xp }}</span>
                </div>
            </div>

            @if(is_null(auth()->user()->school_id))
            <div class="xp-pill" style="border-color: var(--d-accent); background: rgba(251, 191, 36, 0.05);">
                <div class="xp-icon"><i class='bx bxs-star'></i></div>
                <div class="xp-details">
                    <span class="xp-label">Current Plan</span>
                    <span class="xp-value" style="text-transform: capitalize;">{{ auth()->user()->subscription_tier ?? 'Free' }}</span>
                </div>
            </div>
            
            @php $activeOrg = auth()->user()->activeOrganization(); @endphp
            @if($activeOrg)
            <div class="xp-pill" style="border-color: #3b82f6; background: rgba(59, 130, 246, 0.05);">
                <div class="xp-icon"><i class='bx bxs-building-house' style="color: #3b82f6;"></i></div>
                <div class="xp-details">
                    <span class="xp-label" style="color: #3b82f6;">Organization</span>
                    <span class="xp-value">{{ $activeOrg->name }}</span>
                </div>
            </div>
            @endif

            @if(auth()->user()->isFree())
            <button onclick="document.getElementById('subscriptionModal').style.display='flex'" class="btn btn-primary" style="align-self: center; background: linear-gradient(135deg, #fbbf24, #d97706); border:none; box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);">
                Upgrade Premium <i class='bx bx-up-arrow-circle'></i>
            </button>
            @endif
            @if(!$activeOrg)
            <button onclick="document.getElementById('join-org-modal').style.display='flex'" class="btn btn-outline-primary" style="align-self: center; border: 2px solid #3b82f6; color: #3b82f6; background: transparent; font-weight: bold; border-radius: 12px; padding: 10px 15px;">
                <i class='bx bx-building-house'></i> Join Org
            </button>
            @endif
            @endif
        </div>
    </div>
</div>

<!-- Join Org Modal -->
<div id="join-org-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #ffffff; padding: 40px; border-radius: var(--radius); max-width: 400px; width: 90%; border: 1px solid var(--border); position: relative; box-shadow: var(--shadow-lg);">
        <button onclick="document.getElementById('join-org-modal').style.display='none'" style="position: absolute; right: 20px; top: 20px; background: transparent; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;">&times;</button>
        <h3 style="color: var(--text); margin-bottom: 10px; text-align: center;">Join an Organization</h3>
        <p style="text-align: center; color: var(--text-muted); font-size: 14px; margin-bottom: 25px;">Enter your invite code below to join your organization and access group benefits.</p>
        <form action="{{ route('user.organization.join') }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            <div>
                <input type="text" name="organization_code" placeholder="ORG-XXXXX" required class="form-control" style="width: 100%; padding: 15px; border-radius: 12px; border: 2px solid var(--border); font-size: 16px; text-align: center; letter-spacing: 2px; font-weight: bold; text-transform: uppercase;">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">Verify Code & Join</button>
        </form>
    </div>
</div>



<!-- Conditionally Render Content Based on System Type -->
@if(is_null(auth()->user()->school_id))
    <!-- Courses System - Professional Roadmap -->
    <div id="courses-journey" class="animate-slide-up">
        <div class="section-heading">
            <div class="heading-icon"><i class='bx bx-map-alt'></i></div>
            <div>
                <h2>Learning Roadmap (Courses)</h2>
                <p>Pass levels and unlock new skills to reach proficiency</p>
            </div>
        </div>

        <div class="roadmap-container">
            @php
                $levelCount = $courseLevels->count();
            @endphp
            @foreach($courseLevels as $index => $level)
                @php
                    $isUnlocked = in_array($level->id, $unlockedLevels);
                    $isCurrent = $isUnlocked && ($index === 0 || !in_array($courseLevels[$index+1]->id ?? -1, $unlockedLevels));
                    $isCompleted = $isUnlocked && !$isCurrent && isset($courseLevels[$index+1]) && in_array($courseLevels[$index+1]->id, $unlockedLevels);
                    $levelLessons = $level->lessons()->where('is_global', true)->orderBy('order')->get();
                    
                    // S-curve directions: even indices on the left, odd on the right
                    $alignment = ($index % 2 == 0) ? 'align-left' : 'align-right';
                @endphp
                
                <div class="roadmap-node {{ $alignment }} {{ !$isUnlocked ? 'is-locked' : '' }} {{ $isCurrent ? 'is-current' : '' }} {{ $isCompleted ? 'is-completed' : '' }}">
                    <!-- Connector Line -->
                    @if($index < $levelCount - 1)
                        <div class="node-connector"></div>
                    @endif

                    <div class="node-card shadow-premium">
                        <div class="node-header">
                            <div class="level-badge">
                                @if($isCompleted) <i class='bx bxs-check-shield'></i> @elseif(!$isUnlocked) <i class='bx bxs-lock-alt'></i> @else {{ $index + 1 }} @endif
                            </div>
                            <div class="level-info">
                                <h3>{{ $level->name }}</h3>
                                <div class="level-meta">
                                    <span><i class='bx bxs-book-open'></i> {{ $levelLessons->count() }} Lessons</span>
                                    <span><i class='bx bxs-award'></i> {{ $level->quizzes_count }} Quizzes</span>
                                </div>
                            </div>
                        </div>

                        <div class="node-content">
                            <div class="mini-lesson-list">
                                @foreach($levelLessons->take(3) as $lIndex => $lesson)
                                    @php
                                        $lessonCompleted = auth()->user()->completedLessons()->where('lesson_id', $lesson->id)->where('passed', true)->exists();
                                        $canAccess = $isUnlocked;
                                        $req = $lesson->required_tier;
                                        $blockedByTier = false;
                                        if ($req === 'pro' && !auth()->user()->hasPremiumAccess()) $blockedByTier = true;
                                        if ($req === 'basic' && auth()->user()->isFree()) $blockedByTier = true;
                                    @endphp
                                    <div class="mini-lesson {{ $lessonCompleted ? 'done' : ($canAccess && !$blockedByTier ? 'current' : 'locked') }}">
                                        @if($lessonCompleted) <i class='bx bxs-check-circle'></i> 
                                        @elseif($blockedByTier) <i class='bx bxs-star' style="color:var(--accent);"></i>
                                        @elseif(!$canAccess) <i class='bx bxs-lock'></i> 
                                        @else <i class='bx bx-play-circle'></i> @endif
                                        <span>{{ $lesson->title }} @if($blockedByTier)<span style="font-size:10px; color:var(--accent); margin-left:5px;">(Requires Upgrade)</span>@endif</span>
                                    </div>
                                @endforeach
                                @if($levelLessons->count() > 3)
                                    <div class="more-lessons-text">+ {{ $levelLessons->count() - 3 }} more lessons</div>
                                @endif
                            </div>
                        </div>

                        <div class="node-footer">
                            @if($isUnlocked)
                                <a href="{{ route('user.levels.show', $level->id) }}" class="btn ripple {{ $isCurrent ? 'btn-primary' : 'btn-ghost' }}">
                                    @if($isCurrent) Continue Learning @else Review Content @endif
                                    <i class='bx bx-right-arrow-alt' style="margin-left: 8px;"></i>
                                </a>
                            @else
                                <div class="lock-notice">
                                    <i class='bx bx-info-circle'></i> Requires {{ $level->required_xp }} XP to unlock
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


    </div>
@else
    <!-- School System - Premium Interface -->
    <div id="school-system-dashboard" class="animate-slide-up" style="animation-delay: 0.1s">
        <div class="section-heading">
            <div class="heading-icon"><i class='bx bxs-school'></i></div>
            <div>
                @if($myGrade)
                    <h2>Academic Year: {{ $myGrade->name }}</h2>
                    <p>Manage your subjects and unlock your teachers' content</p>
                @else
                    <h2>Join Your School Grade</h2>
                    <p>Enter your grade code to access your school's curriculum</p>
                @endif
            </div>
            @if($myGrade)
                <div style="margin-left: auto;">
                    <form action="{{ route('user.leave_grade') }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this grade? This will lock the content of all teachers.');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--danger); font-weight: 700;">
                            <i class='bx bx-log-out'></i> Leave Grade
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if(!$myGrade)
            <!-- Enrollment Form for Grade -->
            <div class="enrollment-hero shadow-premium animate-fade-in">
                <div class="hero-content">
                    <div class="hero-text">
                        <h3>Unlock Your Learning Path 📚</h3>
                        <p>Your school has assigned you to a specific grade. Enter the code provided by your administration to begin.</p>
                        
                        <form action="{{ route('user.join_grade') }}" method="POST" class="hero-form">
                            @csrf
                            <div class="custom-input-group">
                                <input type="text" name="grade_code" placeholder="Example: GRD-XXXXXX" required>
                                <button type="submit" class="btn btn-primary ripple">Join My Grade <i class='bx bx-right-arrow-alt'></i></button>
                            </div>
                            @error('grade_code')<span class="error-msg">{{ $message }}</span>@enderror
                        </form>
                    </div>
                    <div class="hero-visual">
                        <i class='bx bxs-graduation'></i>
                    </div>
                </div>
            </div>
        @else
            <!-- Subjects & Teachers View -->
            <div class="academic-grid">
                @foreach($gradeLanguages as $language)
                    <div class="subject-card shadow-premium animate-slide-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                        <div class="subject-header">
                            <div class="subject-icon"><i class='bx bx-book-bookmark'></i></div>
                            <h4>{{ $language->name }}</h4>
                        </div>
                        
                        <div class="teachers-list">
                            @php $teachers = $language->teachers; @endphp
                            
                            @if($teachers->count() > 0)
                                @foreach($teachers as $teacher)
                                    @php 
                                        $isUnlocked = in_array($teacher->id, $unlockedTeacherIds);
                                    @endphp
                                    <div class="teacher-item {{ $isUnlocked ? 'unlocked' : 'locked' }}">
                                        <div class="teacher-meta">
                                            <div class="t-avatar">{{ mb_substr($teacher->name, 0, 1) }}</div>
                                            <div class="t-info">
                                                <span class="t-name">{{ $teacher->name }}</span>
                                                <span class="t-status">
                                                    @if($isUnlocked) 
                                                        <span class="status-badge"><i class='bx bxs-check-circle'></i> Unlocked</span>
                                                    @else
                                                        <span class="status-badge-locked"><i class='bx bxs-lock-alt'></i> Content Locked</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        @if($isUnlocked)
                                            <div class="teacher-actions">
                                                <a href="{{ route('user.teacher_content', ['teacher' => $teacher->id, 'language' => $language->id]) }}" class="btn btn-sm btn-enter-class ripple">
                                                    Enter Class <i class='bx bx-chevron-right'></i>
                                                </a>
                                            </div>
                                        @else
                                            <div class="unlock-form-container">
                                                <form action="{{ route('user.unlock_teacher') }}" method="POST" class="inline-unlock-form">
                                                    @csrf
                                                    <div class="mini-input-group">
                                                        <input type="text" name="teacher_code" placeholder="Teacher Code" required>
                                                        <button type="submit" class="btn-unlock"><i class='bx bx-key'></i></button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="empty-msg">No teachers assigned to this subject yet.</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($gradeLanguages->isEmpty())
                    <div class="empty-state-card shadow-premium">
                        <i class='bx bx-ghost'></i>
                        <h3>Empty Grade Structure</h3>
                        <p>No subjects or teachers have been assigned to your grade yet. Please contact your school admin.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif



<style>
/* -------------------------------------------------------------------------- */
/*  Refined Cheerful Design System for Student Dashboard                      */
/* -------------------------------------------------------------------------- */

/* Dashbord Header: Clear & Vibrant */
.dashboard-header {
    background: linear-gradient(135deg, #ffffff, #f1f5f9);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 40px;
    margin-bottom: 40px;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}
.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(20, 184, 166, 0.05), transparent 70%);
    z-index: 0;
}

.header-content { position: relative; z-index: 1; width: 100%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px; }
.header-text h1 { font-size: 2.5rem; font-weight: 900; letter-spacing: -1.5px; margin-bottom: 8px; color: var(--text); }
.header-text p { font-size: 1.1rem; color: var(--text-muted); font-weight: 500; }

.xp-showcase { display: flex; gap: 16px; align-items: center; }
.xp-pill {
    background: #ffffff;
    border: 1px solid var(--border);
    padding: 12px 24px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
}
.xp-pill:hover { transform: translateY(-4px); border-color: var(--primary); box-shadow: var(--shadow); }
.xp-icon { font-size: 1.8rem; color: var(--accent); }
.xp-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; }
.xp-value { font-size: 1.6rem; font-weight: 900; color: var(--text); line-height: 1; }

/* Roadmap Journey */
.roadmap-container {
    position: relative;
    padding: 60px 0;
    display: flex;
    flex-direction: column;
    gap: 80px;
}
.roadmap-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 100%;
    background: var(--border);
    border-radius: 3px;
}

.roadmap-node {
    display: flex;
    justify-content: center;
    width: 100%;
    position: relative;
    z-index: 1;
}
.node-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 32px;
    width: 440px;
    transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
    position: relative;
    box-shadow: var(--shadow);
}
.node-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); border-color: var(--primary); }

.node-header { display: flex; align-items: center; gap: 20px; }
.node-card::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40px;
    height: 6px;
    background: var(--border);
}
.align-left { justify-content: flex-start; padding-left: 50px; }
.align-right { justify-content: flex-end; padding-right: 50px; }
.align-left .node-card::after { right: -40px; }
.align-right .node-card::after { left: -40px; }

.level-badge {
    width: 64px;
    height: 64px;
    background: #f1f5f9;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 900;
    color: var(--primary);
    border: 4px solid #fff;
    box-shadow: 0 0 0 2px var(--border);
    flex-shrink: 0;
}
.is-current .level-badge { background: var(--primary); color: #fff; box-shadow: 0 0 0 2px var(--primary-light); }
.is-locked .node-card { opacity: 0.7; filter: grayscale(0.5); }
.is-locked .level-badge { color: #94a3b8; }

.mini-lesson-list { display: flex; flex-direction: column; gap: 12px; margin: 28px 0; }
.mini-lesson {
    background: #f8fafc;
    padding: 14px 20px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text);
    border: 1px solid transparent;
    transition: 0.2s;
}
.mini-lesson.done { color: var(--success); background: #f0fdf4; }
.mini-lesson.locked { color: var(--text-muted); opacity: 0.6; }
.mini-lesson.current:hover { border-color: var(--primary); transform: translateX(5px); }

/* Activity & Sections */
.section-heading {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 32px;
}
.heading-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #fff;
    box-shadow: 0 10px 20px rgba(20, 184, 166, 0.2);
}
.section-heading h2 { font-size: 1.8rem; font-weight: 900; color: var(--text); letter-spacing: -0.5px; }
.section-heading p { color: var(--text-muted); font-weight: 600; }

.recent-activities {
    background: #ffffff;
    border-radius: 28px;
    padding: 40px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
}



.enrollment-hero {
    background: #ffffff;
    border-radius: 32px;
    padding: 60px;
    margin-bottom: 50px;
    box-shadow: var(--shadow);
}
.hero-text h3 { font-size: 2.5rem; font-weight: 900; color: var(--text); }
.hero-visual { color: var(--primary); opacity: 0.1; }

/* Academic Grid & Teacher Items */
.academic-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px; margin-top: 24px; }
.subject-card { background: #fff; border-radius: 24px; padding: 24px; border: 1px solid var(--border); box-shadow: var(--shadow); }
.subject-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 16px; }
.subject-icon { width: 40px; height: 40px; border-radius: 12px; background: rgba(20, 184, 166, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px; }
.subject-header h4 { font-size: 1.1rem; font-weight: 800; margin: 0; }

.teacher-item { 
    display: flex; align-items: center; justify-content: space-between; 
    padding: 16px; background: #f8fafc; border-radius: 18px; margin-bottom: 12px;
    border: 1px solid transparent; transition: 0.3s;
}
.teacher-item:hover { border-color: var(--primary); background: #fff; transform: translateY(-3px); box-shadow: var(--shadow); }
.teacher-meta { display: flex; align-items: center; gap: 12px; }
.t-avatar {
    width: 44px; height: 44px; border-radius: 12px; 
    background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
    color: #fff; display: flex; align-items: center; justify-content: center; 
    font-weight: 800; font-size: 18px;
}
.t-info { display: flex; flex-direction: column; }
.t-name { font-weight: 800; font-size: 15px; color: var(--text); }
.t-status { margin-top: 2px; }

.status-badge { font-size: 11px; font-weight: 800; color: var(--success); display: flex; align-items: center; gap: 4px; }
.status-badge-locked { font-size: 11px; font-weight: 800; color: var(--danger); display: flex; align-items: center; gap: 4px; }

/* Mini Input Group for Unlock */
.mini-input-group {
    display: flex; align-items: center; background: #fff; 
    border: 1px solid var(--border); border-radius: 12px; 
    overflow: hidden; padding: 2px; transition: 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.mini-input-group:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1); }
.mini-input-group input { 
    border: none !important; padding: 8px 12px !important; font-size: 13px !important; 
    font-weight: 700 !important; width: 110px !important; outline: none !important;
    background: transparent !important;
}
.btn-unlock { 
    background: var(--primary); color: #fff; border: none; 
    width: 34px; height: 34px; border-radius: 10px; 
    display: flex; align-items: center; justify-content: center; 
    cursor: pointer; transition: 0.2s;
}
.btn-unlock:hover { background: var(--primary-dark); transform: scale(1.05); }

/* Enter Class Button Design */
.btn-enter-class {
    background: var(--primary);
    color: #fff !important;
    border: none;
    padding: 8px 18px !important;
    border-radius: 12px !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    box-shadow: 0 4px 12px rgba(20, 184, 166, 0.2);
    transition: all 0.3s ease !important;
}
.btn-enter-class:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(20, 184, 166, 0.3);
}
.btn-enter-class i {
    margin-left: 4px;
    font-size: 16px;
    vertical-align: middle;
}

@media (max-width: 768px) {
    .teacher-item { flex-direction: column; align-items: flex-start; gap: 15px; }
    .unlock-form-container { width: 100%; }
    .mini-input-group { width: 100%; }
    .mini-input-group input { width: 100% !important; }
}
</style>
@endsection
