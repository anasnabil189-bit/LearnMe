@extends('layouts.app')

@section('title', 'Class Rankings | ' . $grade->name)
@section('page-title', $grade->name . ' - ' . $language->name)

@section('content')
<div class="leaderboard-index-page animate-fade-in">
    <!-- Premium Elite Header -->
    <div class="leaderboard-teacher-hero shadow-premium">
        <div class="hero-decoration">
            <div class="circle-1"></div>
            <div class="circle-2"></div>
        </div>
        
        <div class="hero-content">
            <div class="brand-side">
                <div class="trophy-orb">
                    <i class='bx bxs-trophy'></i>
                </div>
                <div class="title-box">
                    <span class="context-tag">{{ $language->name }} Excellence</span>
                    <h1>Class Ranking</h1>
                    <p class="grade-info">Academic performance leaderboard for <strong>{{ $grade->name }}</strong></p>
                </div>
            </div>
            <div class="action-side">
                <a href="{{ route('teacher.results.index') }}" class="btn btn-ghost btn-back ripple">
                    <i class='bx bx-left-arrow-alt'></i> Results History
                </a>
            </div>
        </div>
    </div>

    @if($leaderboard->count() > 0)
        <!-- Ranking Table -->
        <div class="ranking-container animate-slide-up">
            <div class="ranking-card shadow-sm">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th class="col-rank">Pos</th>
                            <th class="col-student">Learner Profile</th>
                            <th class="col-xp text-right">Academic Power (XP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaderboard as $index => $student)
                            @php 
                                $rank = $index + 1;
                                $isTop = $rank <= 3;
                            @endphp
                            <tr class="ranking-row {{ $isTop ? 'highlight-'.$rank : '' }} hover-row">
                                <td class="col-rank">
                                    <div class="rank-box">
                                        @if($rank == 1) <div class="trophy-mini gold"><i class='bx bxs-crown'></i></div> @endif
                                        <span>{{ $rank }}</span>
                                    </div>
                                </td>
                                <td class="col-student">
                                    <div class="student-profile">
                                        <div class="avatar-frame">
                                            <div class="avatar-gradient">
                                                <span>{{ mb_substr($student->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="student-meta">
                                            <span class="s-name">{{ $student->name }}</span>
                                            <span class="s-id">ID #{{ $student->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-xp text-right">
                                    <div class="xp-display">
                                        <div class="val-group">
                                            <span class="xp-val">{{ number_format($student->display_xp) }}</span>
                                            <span class="xp-lab">POINTS</span>
                                        </div>
                                        <i class='bx bxs-zap xp-icon'></i>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="empty-rankings-box">
            <div class="empty-icon"><i class='bx bx-street-view'></i></div>
            <h3>No competition data yet</h3>
            <p>Scores will start appearing here once students take their first assessment for this class.</p>
        </div>
    @endif

    <div class="leaderboard-footer">
        <div class="info-pill">
            <i class='bx bx-info-circle'></i> 
            Rankings calibrated for <strong>{{ $teacherName }}'s</strong> curriculum in <strong>{{ $language->name }}</strong>.
        </div>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Teacher Class Leaderboard: Premium Light Aesthetic                        */
/* -------------------------------------------------------------------------- */
.leaderboard-index-page { max-width: 960px; margin: 0 auto; padding-bottom: 60px; }

/* Hero Section */
.leaderboard-teacher-hero {
    background: #ffffff; border-radius: 35px; border: 1px solid var(--border);
    padding: 40px; margin-bottom: 35px; position: relative; overflow: hidden;
    box-shadow: 0 15px 40px rgba(0,0,0,0.02);
}

.hero-decoration .circle-1 { position: absolute; top: -40px; right: -40px; width: 180px; height: 180px; background: rgba(20, 184, 166, 0.05); border-radius: 50%; }
.hero-decoration .circle-2 { position: absolute; bottom: -40px; left: -20px; width: 150px; height: 150px; background: rgba(245, 158, 11, 0.04); border-radius: 50%; }

.hero-content { display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 2; flex-wrap: wrap; gap: 24px; }
.brand-side { display: flex; align-items: center; gap: 25px; }

.trophy-orb { 
    width: 76px; height: 76px; background: #fffbeb; border-radius: 20px; border: 1px solid #fef3c7;
    display: flex; align-items: center; justify-content: center; font-size: 2.8rem; color: #f59e0b; 
    box-shadow: 0 10px 20px rgba(245, 158, 11, 0.1);
}

.title-box .context-tag { font-size: 11px; font-weight: 950; text-transform: uppercase; color: var(--primary); letter-spacing: 1px; display: block; margin-bottom: 5px; }
.title-box h1 { font-size: 2.22rem; font-weight: 950; color: var(--text); letter-spacing: -1.2px; margin: 0; }
.grade-info { font-size: 1rem; color: var(--text-muted); font-weight: 600; margin-top: 4px; }
.grade-info strong { color: var(--text); }

.btn-back { background: #f8fafc; border: 1px solid var(--border); color: #64748b; font-weight: 800; border-radius: 14px; padding: 10px 20px; transition: 0.3s; }
.btn-back:hover { background: #fff; border-color: var(--primary); color: var(--primary); transform: translateX(-5px); }

/* Table Section */
.ranking-card { background: #ffffff; border-radius: 30px; border: 1px solid var(--border); overflow: hidden; }
.premium-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.premium-table th { padding: 25px; background: #fcfcfc; text-align: left; font-size: 11px; font-weight: 950; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid #f1f5f9; }

.ranking-row { transition: 0.3s; }
.ranking-row td { padding: 18px 25px; border-bottom: 1px solid #f8fafc; }
.ranking-row:last-child td { border-bottom: none; }

.col-rank { width: 90px; }
.rank-box { position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; }
.rank-box span { font-size: 1.5rem; font-weight: 950; color: var(--text-muted); }
.trophy-mini { position: absolute; top: -12px; font-size: 1.5rem; color: #f59e0b; }

.student-profile { display: flex; align-items: center; gap: 18px; }
.avatar-frame { padding: 2px; border-radius: 14px; background: #f1f5f9; }
.avatar-gradient { 
    width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), #0d9488);
    display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 950; font-size: 1.2rem;
}
.student-meta .s-name { display: block; font-size: 1.1rem; font-weight: 800; color: var(--text); }
.student-meta .s-id { font-size: 0.8rem; font-weight: 700; color: #94a3b8; }

.xp-display { display: flex; align-items: center; justify-content: flex-end; gap: 12px; }
.val-group { text-align: right; line-height: 1; }
.xp-val { display: block; font-size: 1.6rem; font-weight: 950; color: #f59e0b; font-family: 'Outfit', sans-serif; }
.xp-lab { font-size: 8px; font-weight: 900; color: #cbd5e1; letter-spacing: 1px; }
.xp-icon { font-size: 1.6rem; color: #f59e0b; opacity: 0.8; }

/* Highlights */
.highlight-1 { background: rgba(254, 243, 199, 0.4); }
.highlight-1 .rank-box span { color: #f59e0b; }
.highlight-2 { background: rgba(241, 245, 249, 0.4); }
.highlight-2 .rank-box span { color: #64748b; }
.highlight-3 { background: rgba(255, 237, 213, 0.4); }
.highlight-3 .rank-box span { color: #c2410c; }

.hover-row:hover { background: rgba(20, 184, 166, 0.02); transform: scale(1.005); z-index: 2; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }

/* Footer */
.leaderboard-footer { margin-top: 30px; text-align: center; }
.info-pill { 
    display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px;
    background: #f8fafc; border: 1px solid var(--border); border-radius: 16px; 
    font-size: 0.9rem; color: #64748b; font-weight: 600;
}
.info-pill strong { color: var(--text); }
.info-pill i { color: var(--primary); font-size: 1.1rem; }

/* Empty State */
.empty-rankings-box { padding: 80px 40px; text-align: center; background: #fff; border-radius: 30px; border: 1px dashed #cbd5e1; }
.empty-icon { font-size: 4rem; color: #cbd5e1; margin-bottom: 20px; }
.empty-rankings-box h3 { font-size: 1.5rem; font-weight: 900; color: var(--text); }
.empty-rankings-box p { color: #94a3b8; font-weight: 600; }

@media (max-width: 600px) {
    .leaderboard-teacher-hero { padding: 30px; text-align: center; }
    .hero-content { flex-direction: column; }
    .brand-side { flex-direction: column; }
    .xp-lab { display: none; }
}
</style>
@endsection
