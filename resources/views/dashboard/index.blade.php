@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .tabs-container {
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 20px;
    }
    .tab-btn {
        padding: 12px 20px;
        border: none;
        background: none;
        color: var(--text-muted);
        cursor: pointer;
        font-family: 'Tajawal', sans-serif;
        font-weight: 600;
        font-size: 15px;
        position: relative;
        transition: all 0.2s;
    }
    .tab-btn:hover { color: var(--text); }
    .tab-btn.active { color: var(--primary-light); }
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary-light);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s ease; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .learning-section { margin-top: 24px; }
    .nested-list { list-style: none; padding-right: 20px; border-right: 2px solid var(--border); margin-top: 10px; }
    .nested-item { margin-bottom: 8px; display: flex; align-items: center; gap: 10px; color: var(--text-muted); font-size: 14px; text-decoration: none; transition: color 0.2s; }
    .nested-item:hover { color: var(--primary-light); }
    .nested-item i { font-size: 18px; }
</style>
@endpush

@section('content')

@if($user->type === 'admin')
    <div class="page-header">
        <div>
            <h1>Welcome, {{ $user->name }} 👋</h1>
            <p>Here is an overview of the platform's performance today</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class='bx bxs-school'></i></div>
            <div class="stat-value">{{ $data['stats']['schools'] ?? 0 }}</div>
            <div class="stat-label">Total Schools</div>
        </div>
        <div class="stat-card accent">
            <div class="stat-icon"><i class='bx bxs-user-badge'></i></div>
            <div class="stat-value">{{ $data['stats']['teachers'] ?? 0 }}</div>
            <div class="stat-label">Teachers</div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon"><i class='bx bxs-graduation'></i></div>
            <div class="stat-value">{{ $data['stats']['students_school'] ?? 0 }}</div>
            <div class="stat-label">School Students</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class='bx bxs-user'></i></div>
            <div class="stat-value">{{ $data['stats']['students_individual'] ?? 0 }}</div>
            <div class="stat-label">Course Learners</div>
        </div>
        <div class="stat-card primary">
            <div class="stat-icon"><i class='bx bxs-layer'></i></div>
            <div class="stat-value">{{ $data['stats']['global_levels'] ?? 0 }}</div>
            <div class="stat-label">Global Levels</div>
        </div>
    </div>

    <div class="form-grid">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Schools</h2>
                <a href="{{ route('admin.schools.index') }}" class="btn btn-sm btn-ghost">View All</a>
            </div>
            @if(isset($data['recentSchools']) && count($data['recentSchools']) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Subscription End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['recentSchools'] as $school)
                            <tr>
                                <td>{{ $school->name }}</td>
                                <td><span class="badge badge-primary">{{ $school->code }}</span></td>
                                <td>{{ $school->subscription_end }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class='bx bx-building-house'></i>
                    <p>No schools registered yet</p>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Teachers</h2>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-sm btn-ghost">View All</a>
            </div>
            @if(isset($data['recentTeachers']) && count($data['recentTeachers']) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>School</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['recentTeachers'] as $teacher)
                            <tr>
                                <td>{{ $teacher->name ?? 'Unassigned' }}</td>
                                <td>{{ $teacher->school->name ?? 'Unassigned' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class='bx bx-user-voice'></i>
                    <p>No teachers registered yet</p>
                </div>
            @endif
        </div>
    </div>
@endif

@if($user->type === 'school')
    <div class="page-header">
        <div>
            <h1>Welcome, School Admin 👋</h1>
            <p>Overview of your school's performance</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class='bx bxs-chalkboard'></i></div>
            <div class="stat-value">{{ $data['stats']['grades'] ?? 0 }}</div>
            <div class="stat-label">Total Grades</div>
        </div>
        <div class="stat-card accent">
            <div class="stat-icon"><i class='bx bxs-user-badge'></i></div>
            <div class="stat-value">{{ $data['stats']['teachers'] ?? 0 }}</div>
            <div class="stat-label">Teachers</div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon"><i class='bx bxs-graduation'></i></div>
            <div class="stat-value">{{ $data['stats']['students'] ?? 0 }}</div>
            <div class="stat-label">Students</div>
        </div>
    </div>

    <div class="form-grid" style="margin-top: 30px;">
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <h2 class="card-title">Teacher Statistics</h2>
                <a href="{{ route('school.teachers.index') }}" class="btn btn-sm btn-ghost">View All</a>
            </div>
            @if(isset($data['teachersData']) && count($data['teachersData']) > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Teacher Name</th>
                                <th>Language(s) Taught</th>
                                <th>Number of Grades</th>
                                <th>Following Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['teachersData'] as $teacher)
                            <tr>
                                <td style="font-weight: 600;">{{ $teacher->name }}</td>
                                <td>
                                    @forelse($teacher->languagesTaught as $lang)
                                        <span class="badge badge-accent">{{ $lang->name }}</span>
                                    @empty
                                        <span class="badge" style="background:var(--bg); color:var(--text-muted)">Unassigned</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $teacher->teacherAssignments->unique('grade_id')->count() }}</span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <i class='bx bxs-group' style="color:var(--primary-light);"></i> 
                                        <b>{{ $teacher->unlockedStudents->count() }}</b>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class='bx bx-user-voice'></i>
                    <p>No teachers registered yet.</p>
                </div>
            @endif
        </div>
    </div>
@endif

@if($user->type === 'teacher')
    <div class="page-header">
        <div>
            <h1>Welcome, Prof. {{ explode(' ', $user->name)[0] }} 👨‍🏫</h1>
            <p>Welcome to your Instructor Dashboard</p>
        </div>
        <div>
            <a href="{{ route('teacher.lessons.create') }}" class="btn btn-primary"><i class='bx bx-plus'></i> New Lesson</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class='bx bxs-chalkboard'></i></div>
            <div class="stat-value">{{ $data['stats']['grades'] ?? 0 }}</div>
            <div class="stat-label">My Grades</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class='bx bxs-book-content'></i></div>
            <div class="stat-value">{{ $data['stats']['lessons'] ?? 0 }}</div>
            <div class="stat-label">My Lessons</div>
        </div>
        <div class="stat-card accent">
            <div class="stat-icon"><i class='bx bxs-edit-alt'></i></div>
            <div class="stat-value">{{ $data['stats']['quizzes'] ?? 0 }}</div>
            <div class="stat-label">My Quizzes</div>
        </div>
    </div>
@endif

@if($user->type === 'user')
    <div class="page-header">
        <div>
            <h1>Welcome, {{ explode(' ', $user->name)[0] }} 👋</h1>
            @if($user->school_id)
                <p>Welcome to your school dashboard</p>
            @else
                <p>Ready for a fun learning journey? Let's go!</p>
            @endif
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <div style="background: linear-gradient(135deg, var(--accent), #d97706); padding: 10px 20px; border-radius: 12px; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(245,158,11,0.3);">
                <i class='bx bxs-star' style="color: white; font-size: 24px;"></i>
                <div>
                    <div style="font-size: 12px; color: rgba(255,255,255,0.8); font-weight: 600;">Experience Points (XP)</div>
                    <div style="font-size: 20px; color: white; font-weight: 800; line-height: 1;">{{ $data['stats']['xp_points'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($user->school_id)
        {{-- My School View --}}
        <div id="my-school">
            <div class="stats-grid">
                <div class="stat-card info">
                    <div class="stat-icon"><i class='bx bx-chalkboard'></i></div>
                    <div class="stat-value">{{ count($data['mySchool']['classes']) }}</div>
                    <div class="stat-label">School Grades</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class='bx bx-check-double'></i></div>
                    <div class="stat-value">{{ $data['stats']['results'] ?? 0 }}</div>
                    <div class="stat-label">Completed Quizzes</div>
                </div>
            </div>

            <div class="form-grid">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">My Grades & Teachers</h2>
                    </div>
                    @if(count($data['mySchool']['classes']) > 0)
                        @foreach($data['mySchool']['classes'] as $class)
                            <div style="margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h3 style="color: var(--primary-light); font-size: 18px;">{{ $class->name }}</h3>
                                    <span class="badge badge-primary">Teacher: {{ $class->teacher->name ?? 'Unassigned' }}</span>
                                </div>
                                
                                <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--text-muted);"><i class='bx bx-book-open'></i> School Lessons</h4>
                                        <ul class="nested-list">
                                            @forelse($class->lessons as $lesson)
                                                <li>
                                                    <a href="{{ route('user.lessons.show', $lesson->id) }}" class="nested-item">
                                                        <i class='bx bx-play-circle'></i> {{ $lesson->title }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li style="font-size: 12px; color: var(--text-muted);">No lessons currently</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--text-muted);"><i class='bx bx-edit'></i> School Quizzes</h4>
                                        <ul class="nested-list">
                                            @forelse($class->quizzes as $quiz)
                                                <li>
                                                    <a href="{{ route('user.quizzes.take', $quiz->id) }}" class="nested-item">
                                                        <i class='bx bx-right-arrow-circle'></i> {{ $quiz->title }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li style="font-size: 12px; color: var(--text-muted);">No quizzes currently</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class='bx bx-school'></i>
                            <p>You haven't joined any school grades yet</p>
                        </div>
                    @endif
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">My Recent Activity</h2>
                    </div>
                    @if(isset($data['results']) && count($data['results']) > 0)
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['results'] as $res)
                                    <tr>
                                        <td>{{ $res->quiz->title ?? 'Unknown Quiz' }}</td>
                                        <td>
                                            <span class="badge {{ $res->score >= 50 ? 'badge-success' : 'badge-danger' }}">{{ $res->score }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class='bx bx-list-check'></i>
                            <p>You haven't completed any quizzes yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- Self Learning View --}}
        <div id="courses-learning">
            <div class="stats-grid">
                <div class="stat-card accent">
                    <div class="stat-icon"><i class='bx bxs-layer'></i></div>
                <div class="stat-value">{{ count($data['coursesLevels']['levels'] ?? []) }}</div>
                    <div class="stat-label">Total Accessible Course Levels</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class='bx bxs-medal'></i></div>
                    <div class="stat-value">{{ $data['stats']['xp_points'] ?? 0 }}</div>
                    <div class="stat-label">My XP Balance</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Explore Course Tracks</h2>
                </div>
                @if(count($data['coursesLevels']['levels'] ?? []) > 0)
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        @foreach($data['coursesLevels']['levels'] as $level)
                            <div style="background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h3 style="font-size: 18px; color: var(--accent);">{{ $level->name }}</h3>
                                    <span class="badge badge-accent">{{ $level->required_xp }} XP Required</span>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <div style="height: 6px; background: var(--bg3); border-radius: 99px; overflow: hidden;">
                                        @php
                                            $progress = 0;
                                            if(isset($data['stats']['xp_points']) && $level->required_xp > 0) {
                                                $progress = min(100, round(($data['stats']['xp_points'] / $level->required_xp) * 100));
                                            }
                                        @endphp
                                        <div style="width: {{ $progress }}%; height: 100%; background: var(--accent);"></div>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                    <div>
                                        <h4 style="font-size: 13px; margin-bottom: 8px; color: var(--text-muted);"><i class='bx bx-book-bookmark'></i> Lessons</h4>
                                        <ul class="nested-list" style="border-right-color: var(--accent);">
                                            @forelse($level->lessons as $lesson)
                                                <li>
                                                    <a href="{{ route('user.lessons.show', $lesson->id) }}" class="nested-item">
                                                        {{ $lesson->title }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li style="font-size: 11px; color: var(--text-muted);">No lessons</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 style="font-size: 13px; margin-bottom: 8px; color: var(--text-muted);"><i class='bx bx-star'></i> Quizzes</h4>
                                        <ul class="nested-list" style="border-right-color: var(--accent);">
                                            @forelse($level->quizzes as $quiz)
                                                <li>
                                                    <a href="{{ route('user.quizzes.take', $quiz->id) }}" class="nested-item">
                                                        {{ $quiz->title }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li style="font-size: 11px; color: var(--text-muted);">No quizzes</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class='bx bx-book-content'></i>
                        <p>No global levels available currently.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif

@endsection
