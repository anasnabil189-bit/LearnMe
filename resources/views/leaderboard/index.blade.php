@extends('layouts.app')

@section('title', $title ?? 'Global Leaderboards')
@section('page-title', $title ?? 'Leaderboards')

@section('content')
<div class="leaderboard-workspace animate-fade-in">
    <!-- Premium Podium Header -->
    <div class="leaderboard-hero shadow-premium">
        <div class="hero-brand">
            <div class="hero-icon"><i class='bx bxs-trophy'></i></div>
            <div class="hero-text">
                <h1>{{ $title ?? 'Competitions & Rankings' }}</h1>
                <p>Rising stars of the Learnme community. Keep learning, keep climbing!</p>
            </div>
        </div>
    </div>

    @if(count($leaderboardGroups) > 0)
        <!-- Interactive Language Tabs -->
        @if(count($leaderboardGroups) > 1)
        <div class="pill-navigation-wrap scroll-x animate-slide-up">
            <div class="pill-tabs">
                @php $first = true; @endphp
                @foreach($leaderboardGroups as $langName => $students)
                    <button class="pill-btn {{ $first ? 'is-active' : '' }}" data-target="lang-{{ Str::slug($langName) }}">
                        <i class='bx bx-globe'></i> {{ $langName }}
                    </button>
                    @php $first = false; @endphp
                @endforeach
            </div>
        </div>
        @endif

        @php $firstGroup = true; @endphp
        @foreach($leaderboardGroups as $langName => $students)
            <div id="lang-{{ Str::slug($langName) }}" class="tab-content-panel animate-fade-in" style="display: {{ $firstGroup ? 'block' : 'none' }};">
                <div class="ranking-table-card shadow-sm">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-rank">Rank</th>
                                <th class="col-learner">Student Profile</th>
                                @if(auth()->user()->isAdmin())
                                <th class="col-school">Academy</th>
                                @endif
                                <th class="col-points">Growth XP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $rank = $index + 1;
                                    $isTop3 = $rank <= 3;
                                @endphp
                                <tr class="ranking-row {{ $isTop3 ? 'rank-highlight-'.$rank : '' }} hover-elevate">
                                    <td class="col-rank">
                                        <div class="rank-indicator">
                                            @if($rank == 1) <i class='bx bxs-crown crown-gold'></i> @endif
                                            <span>{{ $rank }}</span>
                                        </div>
                                    </td>
                                    <td class="col-learner">
                                        <div class="learner-profile">
                                            <div class="avatar-box">
                                                <span>{{ mb_substr($student->name, 0, 1) }}</span>
                                            </div>
                                            <div class="learner-details">
                                                <span class="name">{{ $student->name }}</span>
                                                @if($isTop3)
                                                    <span class="status-tag">
                                                        @if($rank == 1) Ultimate Champion @elseif($rank == 2) Elite Runner @else Rising Star @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    @if(auth()->user()->isAdmin())
                                    <td class="col-school">
                                        <div class="school-tag"><i class='bx bxs-school'></i> {{ $student->school->name ?? 'Courses Track' }}</div>
                                    </td>
                                    @endif
                                    <td class="col-points">
                                        <div class="xp-ticker">
                                            <i class='bx bxs-zap animate-pulse-soft'></i>
                                            <span>{{ number_format($student->display_xp) }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @php $firstGroup = false; @endphp
        @endforeach
    @else
        <div class="empty-leaderboard animate-slide-up">
            <div class="empty-orb"><i class='bx bx-ghost'></i></div>
            <h3>No entries yet</h3>
            <p>Complete your first assessment to appear on the leaderboard!</p>
        </div>
    @endif
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Leaderboard: Premium Elite Light Style                                    */
/* -------------------------------------------------------------------------- */
.leaderboard-workspace { max-width: 1100px; margin: 0 auto; padding-bottom: 60px; }

/* Hero Card */
.leaderboard-hero {
    background: #ffffff; border-radius: 40px; padding: 50px; 
    border: 1px solid var(--border); box-shadow: 0 15px 40px rgba(0,0,0,0.03);
    margin-bottom: 40px; position: relative; overflow: hidden;
}
.leaderboard-hero::after {
    content: ''; position: absolute; right: -60px; bottom: -60px; width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(20, 184, 166, 0.08), transparent 70%); border-radius: 50%;
}

.hero-brand { display: flex; align-items: center; gap: 30px; position: relative; z-index: 2; }
.hero-icon { 
    width: 80px; height: 80px; border-radius: 22px; background: #fef3c7; color: #f59e0b; 
    display: flex; align-items: center; justify-content: center; font-size: 3rem; 
    box-shadow: 0 8px 15px rgba(245, 158, 11, 0.15);
}
.hero-text h1 { font-size: 2.8rem; font-weight: 950; color: var(--text); letter-spacing: -1.5px; margin-bottom: 5px; }
.hero-text p { font-size: 1.15rem; color: var(--text-muted); font-weight: 600; }

/* Navigation */
.pill-navigation-wrap { margin-bottom: 30px; }
.pill-tabs { display: flex; gap: 12px; background: rgba(255,255,255,0.8); padding: 8px; border-radius: 20px; border: 1px solid var(--border); width: fit-content; }
.pill-btn {
    border: none; padding: 10px 24px; border-radius: 14px; font-weight: 800; font-size: 0.95rem; cursor: pointer;
    transition: 0.3s; background: transparent; color: var(--text-muted); display: flex; align-items: center; gap: 8px;
}
.pill-btn.is-active { background: var(--primary); color: #fff; box-shadow: 0 6px 15px rgba(20, 184, 166, 0.2); }
.pill-btn:not(.is-active):hover { background: #f8fafc; color: var(--primary); }

/* Table Styling */
.ranking-table-card { background: #ffffff; border-radius: 32px; border: 1px solid var(--border); overflow: hidden; }
table { width: 100%; border-collapse: separate; border-spacing: 0; }
th { padding: 25px; text-align: left; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid #f1f5f9; }

.ranking-row { border-bottom: 1px solid #f8fafc; transition: 0.3s; }
.ranking-row:last-child { border-bottom: none; }
.ranking-row td { padding: 20px 25px; transition: 0.3s; }

.col-rank { width: 100px; }
.rank-indicator { position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }
.rank-indicator span { font-size: 1.4rem; font-weight: 950; color: var(--text-muted); font-family: 'Outfit', sans-serif; }
.crown-gold { position: absolute; top: -12px; font-size: 1.6rem; color: #f59e0b; }

.learner-profile { display: flex; align-items: center; gap: 20px; }
.avatar-box { 
    width: 50px; height: 50px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), #0d9488);
    display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; font-weight: 900;
}
.learner-details .name { display: block; font-size: 1.15rem; font-weight: 800; color: var(--text); }
.status-tag { font-size: 10px; font-weight: 800; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.5px; }

.school-tag { font-size: 13px; font-weight: 700; color: #64748b; background: #f8fafc; padding: 6px 12px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; }

.xp-ticker { display: flex; align-items: center; gap: 8px; color: #f59e0b; font-size: 1.5rem; font-weight: 950; }
.xp-ticker span { font-family: 'Outfit', sans-serif; min-width: 60px; }

/* Rank Highlights */
.rank-highlight-1 { background: rgba(254, 243, 199, 0.4); }
.rank-highlight-1 .rank-indicator span { color: #f59e0b; }
.rank-highlight-2 { background: rgba(241, 245, 249, 0.4); }
.rank-highlight-2 .rank-indicator span { color: #64748b; }
.rank-highlight-3 { background: rgba(255, 237, 213, 0.4); }
.rank-highlight-3 .rank-indicator span { color: #c2410c; }

/* Empty State */
.empty-leaderboard { text-align: center; padding: 100px 40px; }
.empty-orb { width: 120px; height: 120px; background: #fff; border-radius: 40px; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 4rem; color: #cbd5e1; }
.empty-leaderboard h3 { font-size: 1.8rem; font-weight: 950; color: var(--text); }

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes pulse-soft { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
.animate-pulse-soft { animation: pulse-soft 2s infinite ease-in-out; }

/* Mobile */
@media (max-width: 768px) {
    .leaderboard-hero { padding: 30px; text-align: center; }
    .hero-brand { flex-direction: column; }
    .hero-text h1 { font-size: 2rem; }
    .col-school { display: none; }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.pill-btn');
    const contents = document.querySelectorAll('.tab-content-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            const target = tab.getAttribute('data-target');
            contents.forEach(c => {
                c.style.display = (c.id === target) ? 'block' : 'none';
                if(c.id === target) c.classList.add('animate-fade-in');
            });
        });
    });
});
</script>
@endpush
@endsection
