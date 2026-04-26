@extends('layouts.app')

@section('title', 'Teacher Dashboard')
@section('page-title', 'Teacher Space')

@section('content')
<div class="page-header" style="margin-bottom: 40px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 800; color: var(--text);">Welcome, {{ auth()->user()->name }} 👨‍🏫</h1>
        @if(auth()->user()->school)
            <p style="color: var(--primary-light); font-weight: 600; display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 16px;">
                <i class='bx bxs-school' style="font-size: 20px;"></i> {{ auth()->user()->school->name }}
            </p>
        @else
            <p style="color: var(--text-muted); margin-top: 5px;">Here is a quick overview of your students' performance and your content.</p>
        @endif
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="stat-card">
        <div class="stat-icon"><i class='bx bxs-chalkboard'></i></div>
        <div class="stat-value">{{ $stats['teaching_grades'] ?? 0 }}</div>
        <div class="stat-label">Assigned Grades</div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon"><i class='bx bxs-group'></i></div>
        <div class="stat-value">{{ $stats['students'] ?? 0 }}</div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-icon" style="color:var(--accent)"><i class='bx bxs-star'></i></div>
        <div class="stat-value">{{ $stats['avg_score'] ?? 0 }}%</div>
        <div class="stat-label">Average Performance</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon" style="color:var(--success)"><i class='bx bxs-book-content'></i></div>
        <div class="stat-value">{{ ($stats['lessons'] ?? 0) + ($stats['quizzes'] ?? 0) }}</div>
        <div class="stat-label">Total Content (Lessons/Quizzes)</div>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 30px; display: grid;">
    
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 20px;">
            <div class="card-header" style="padding: 20px 25px; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.02); margin-bottom: 0;">
                <h3 class="card-title"><i class='bx bx-list-ul' style="color:var(--primary-light);"></i> My Teaching Classes</h3>
            </div>
            <div class="table-wrap" style="padding: 15px 25px;">
                @if(isset($teacherAssignments) && $teacherAssignments->count() > 0)
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        @foreach($teacherAssignments as $assignment)
                            <div style="padding:10px 15px; background:var(--bg2); border-radius:10px; border-left:4px solid var(--primary);">
                                <strong>{{ $assignment->grade->name }}</strong><br>
                                <span style="font-size:12px; color:var(--text-muted);">{{ $assignment->schoolLanguage->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--text-muted); font-size:13px;">No classes assigned yet. Contact your school administrator.</p>
                @endif
            </div>
        </div>

        @if($topStudent)
        <div class="card" style="background: linear-gradient(135deg, rgba(251,191,36,0.1), rgba(245,158,11,0.2)); border:1px solid rgba(251,191,36,0.3); text-align:center; padding:30px 20px;">
            <i class='bx bxs-crown' style="font-size:50px; color:#fbbf24; margin-bottom:15px; display:block;"></i>
            <h3 style="color:#fbbf24; font-size:16px; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;">Top Performing Student</h3>
            <div style="font-size:24px; font-weight:800; color:var(--text); margin-bottom:10px;">{{ $topStudent->name }}</div>
            <div style="display:inline-flex; align-items:center; gap:5px; background:#fbbf24; color:#fff; padding:5px 15px; border-radius:20px; font-weight:800; font-size:14px;">
                <i class='bx bxs-star'></i> {{ $topStudent->total_teacher_xp }} XP
            </div>
        </div>
        @endif
    </div>
    
    {{-- Teacher Leaderboard --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="card-header" style="padding: 20px 25px; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.02);">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 class="card-title"><i class='bx bxs-bar-chart-alt-2' style="color:var(--accent);"></i> Class Leaderboard</h3>
                <span class="badge badge-accent" style="font-size:11px;">Ranked by points from your quizzes</span>
            </div>
        </div>
        <div class="table-wrap">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 15px 25px; width:80px; text-align:center;">Rank</th>
                        <th>Student</th>
                        <th style="text-align:right; padding-right:25px;">Earned Points</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaderboard as $index => $student)
                    <tr style="background: {{ $index === 0 ? 'rgba(251, 191, 36, 0.05)' : 'transparent' }}">
                        <td style="padding: 15px 25px; text-align:center;">
                            <div style="width: 30px; height: 30px; margin:0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; 
                                {{ $index === 0 ? 'background: #fbbf24; color: #fff;' : 
                                  ($index === 1 ? 'background: #94a3b8; color: #fff;' : 
                                  ($index === 2 ? 'background: #b45309; color: #fff;' : 'background: var(--bg3); color: var(--text-muted);')) }}">
                                {{ $index + 1 }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--primary-light)); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:14px;">
                                    {{ mb_substr($student->name ?? '?', 0, 1) }}
                                </div>
                                <span style="font-weight:600; font-size:15px; {{ $index === 0 ? 'color:#fbbf24;' : 'color:var(--text);' }}">{{ $student->name ?? 'Unknown Student' }}</span>
                            </div>
                        </td>
                        <td style="text-align:right; padding-right:25px;">
                            <div style="font-weight:800; font-size:16px; color:var(--accent);">
                                <i class='bx bxs-star'></i> {{ $student->total_teacher_xp }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center; padding:40px; color:var(--text-muted);">
                            <i class='bx bx-user-x' style="font-size:40px; display:block; margin-bottom:15px; opacity:0.5;"></i>
                            No students have earned points in your tests yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
