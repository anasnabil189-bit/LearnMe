@extends('layouts.app')

@section('content')
<div class="courses-workspace animate-fade-in">
    <div class="container-premium">
        <!-- Explorer Header -->
        <div class="explorer-header">
            <div class="brand-box">
                <h1 class="headline">Course Tracks Explorer 🌍</h1>
                <p class="subline">Embark on a specialized path. Complete levels to earn XP and unlock advanced courses.</p>
            </div>
            <div class="points-display-card shadow-premium">
                <div class="points-icon"><i class='bx bxs-zap'></i></div>
                <div class="points-data">
                    <span class="label">Total XP Earned</span>
                    <span class="value">{{ $userXp }} XP</span>
                </div>
            </div>
        </div>

        <!-- Interactive Map of Levels -->
        <div class="levels-grid">
            @foreach($levels as $level)
                @php
                    $isUnlocked = in_array($level->id, $unlockedLevels);
                    $levelLessons = $level->lessons->sortBy('order');
                @endphp
                
                <div class="level-node-card {{ $isUnlocked ? 'is-accessible' : 'is-locked' }} animate-slide-up" style="animation-delay: {{ $loop->index * 0.1 }}s">
                    <div class="node-glow"></div>
                    <div class="node-inner shadow-sm">
                        <!-- Level Identity -->
                        <div class="node-header">
                            <div class="level-icon-box {{ $isUnlocked ? 'active-gradient' : 'locked-gradient' }}">
                                <span>{{ strtoupper(substr($level->name, 0, 1)) }}</span>
                            </div>
                            @if(!$isUnlocked)
                                <div class="lock-pill"><i class='bx bxs-lock-alt'></i> Locked</div>
                            @endif
                        </div>

                        <div class="node-body">
                            <h3 class="level-title">{{ $level->name }}</h3>
                            <p class="level-caption">Master the fundamentals and advance to proficiency.</p>

                            <!-- Lessons Quick Peek -->
                            <div class="mini-curriculum">
                                @if($levelLessons->isEmpty())
                                    <div class="coming-soon">New content arriving soon...</div>
                                @else
                                    <div class="lesson-dots">
                                        @foreach($levelLessons as $index => $lesson)
                                            @php
                                                $lessonCompleted = Auth::user()->completedLessons()->where('lesson_id', $lesson->id)->where('passed', true)->exists();
                                                $isAccessible = $isUnlocked;
                                                $req = $lesson->required_tier ?? 'free';
                                                $blockedByTier = false;
                                                if ($req === 'pro' && !Auth::user()->isPro()) $blockedByTier = true;
                                                if ($req === 'basic' && Auth::user()->isFree()) $blockedByTier = true;
                                                if ($blockedByTier) $isAccessible = false;
                                            @endphp
                                            <a href="{{ $isAccessible ? route('courses.lesson', $lesson->id) : '#' }}" 
                                               class="lesson-pill {{ $lessonCompleted ? 'completed' : ($isAccessible ? 'available' : 'blocked') }}"
                                               title="{{ $lesson->title }}">
                                                <span class="dot"></span>
                                                <span class="text">{{ Str::limit($lesson->title, 18) }}</span>
                                                @if($blockedByTier) <i class='bx bxs-star tier-star'></i> @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="node-footer">
                            @if($isUnlocked)
                                <a href="{{ route('user.levels.show', $level->id) }}" class="btn btn-primary btn-block ripple">
                                    Continue Learning <i class='bx bx-right-arrow-alt'></i>
                                </a>
                            @else
                                <div class="unlock-requirement">
                                    <i class='bx bx-info-circle'></i> Requires {{ $level->required_xp }} XP
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Courses Dashboard: Premium Light Design                                   */
/* -------------------------------------------------------------------------- */
.courses-workspace { background: #f8fafc; min-height: 100vh; padding: 50px 20px; }
.container-premium { max-width: 1200px; margin: 0 auto; }

/* Explorer Header */
.explorer-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 50px; gap: 30px; flex-wrap: wrap; }
.brand-box { flex: 1; }
.headline { font-size: 2.8rem; font-weight: 950; color: var(--text); letter-spacing: -1.5px; margin-bottom: 10px; }
.subline { font-size: 1.15rem; color: var(--text-muted); font-weight: 600; max-width: 600px; line-height: 1.6; }

.points-display-card { 
    background: #fff; padding: 15px 25px; border-radius: 20px; display: flex; align-items: center; gap: 20px; 
    border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.02);
}
.points-icon { width: 54px; height: 54px; background: rgba(245, 158, 11, 0.1); color: var(--accent); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
.points-data { display: flex; flex-direction: column; }
.points-data .label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
.points-data .value { font-size: 1.8rem; font-weight: 900; color: var(--text); line-height: 1.1; }

/* Levels Grid */
.levels-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 30px; }

.level-node-card { position: relative; }
.node-inner { 
    background: #fff; border: 1px solid var(--border); border-radius: 32px; padding: 32px; 
    height: 100%; display: flex; flex-direction: column; transition: 0.4s cubic-bezier(0.2, 1, 0.2, 1);
    position: relative; z-index: 2;
}
.level-node-card:hover .node-inner { transform: translateY(-10px); border-color: var(--primary); box-shadow: 0 20px 40px rgba(0,0,0,0.06); }

.node-glow { 
    position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
    background: radial-gradient(circle at 50% 0%, rgba(20, 184, 166, 0.08), transparent 70%); 
    opacity: 0; transition: 0.4s; z-index: 1; border-radius: 32px;
}
.level-node-card:hover .node-glow { opacity: 1; }

/* Node Header */
.node-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
.level-icon-box { 
    width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; 
    font-size: 1.8rem; font-weight: 900; color: #fff; box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}
.active-gradient { background: linear-gradient(135deg, var(--primary), #0d9488); }
.locked-gradient { background: linear-gradient(135deg, #94a3b8, #64748b); }

.lock-pill { background: #f1f5f9; color: #64748b; padding: 6px 12px; border-radius: 50px; font-size: 11px; font-weight: 800; display: flex; align-items: center; gap: 6px; }

.level-title { font-size: 1.6rem; font-weight: 900; color: var(--text); margin-bottom: 8px; }
.level-caption { font-size: 0.95rem; color: var(--text-muted); font-weight: 600; line-height: 1.5; margin-bottom: 25px; }

/* Mini Curriculum */
.mini-curriculum { flex: 1; margin-bottom: 25px; }
.lesson-dots { display: flex; flex-direction: column; gap: 10px; }
.lesson-pill { 
    display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-radius: 16px; 
    text-decoration: none; border: 1px solid transparent; transition: 0.2s;
}

.lesson-pill.completed { background: #f0fdf4; color: var(--success); }
.lesson-pill.available { background: #fff; border-color: #f1f5f9; color: var(--text); }
.lesson-pill.available:hover { border-color: var(--primary); background: #f0fdfa; }
.lesson-pill.blocked { opacity: 0.5; color: #94a3b8; background: #f8fafc; }

.dot { width: 10px; height: 10px; border-radius: 50%; background: #e2e8f0; flex-shrink: 0; }
.lesson-pill.completed .dot { background: var(--success); }
.lesson-pill.available .dot { background: var(--primary); }

.lesson-pill .text { font-size: 0.95rem; font-weight: 700; }
.tier-star { color: var(--accent); font-size: 14px; margin-left: auto; }

.coming-soon { font-style: italic; color: #94a3b8; font-size: 0.9rem; padding: 20px 0; text-align: center; }

/* Footer */
.node-footer { margin-top: auto; border-top: 1px solid #f1f5f9; padding-top: 25px; }
.btn-block { width: 100%; display: flex; justify-content: center; padding: 14px; border-radius: 14px; font-weight: 800; }
.unlock-requirement { text-align: center; font-size: 0.85rem; font-weight: 700; color: #64748b; display: flex; align-items: center; justify-content: center; gap: 6px; }

.is-locked .node-inner { opacity: 0.7; }
.is-locked .lesson-pill { pointer-events: none; }

@media (max-width: 600px) {
    .headline { font-size: 2.22rem; }
    .explorer-header { flex-direction: column; align-items: flex-start; }
    .points-display-card { width: 100%; }
}
</style>
@endsection
