@extends('layouts.app')

@section('title', 'Manager Control Panel')
@section('page-title', 'Content Analytics')

@section('content')
<div class="page-header">
    <div>
        <h1>Welcome, Manager 👋</h1>
        <p>Here is an overview of the content and educational performance.</p>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
    <!-- Lessons -->
    <div class="stat-card accent">
        <div class="stat-icon"><i class='bx bxs-book-content'></i></div>
        <div class="stat-value">{{ $stats['total_lessons'] }}</div>
        <div class="stat-label">Total Lessons</div>
    </div>

    <!-- Quizzes -->
    <div class="stat-card info">
        <div class="stat-icon"><i class='bx bxs-edit-alt'></i></div>
        <div class="stat-value">{{ $stats['total_quizzes'] }}</div>
        <div class="stat-label">Total Quizzes</div>
    </div>

    <!-- Questions -->
    <div class="stat-card success">
        <div class="stat-icon"><i class='bx bxs-help-circle'></i></div>
        <div class="stat-value">{{ $stats['total_questions'] }}</div>
        <div class="stat-label">Total Questions</div>
    </div>
    
    <!-- Levels -->
    <div class="stat-card primary">
        <div class="stat-icon"><i class='bx bxs-layer'></i></div>
        <div class="stat-value">{{ $stats['total_levels'] }}</div>
        <div class="stat-label">Total Levels</div>
    </div>
</div>

<!-- Educational Statistics -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2 class="card-title"><i class='bx bxs-pie-chart-alt-2' style="color:var(--accent);"></i> Educational Statistics</h2>
    </div>
    
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Language</th>
                    <th>Proficiency Levels</th>
                    <th>Enrolled Students (Individual)</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($languages as $language)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 12px;">
                                {{ strtoupper($language->code) }}
                            </div>
                            <span style="font-weight: 700;">{{ $language->name }}</span>
                        </div>
                    </td>
                    <td><span class="badge badge-primary-light">{{ $language->levels_count }} Levels</span></td>
                    <td><strong style="color: var(--accent);">{{ $language->courses_users_count }}</strong> Course Learners</td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.languages.show', $language->id) }}" class="btn btn-sm btn-ghost" title="View Statistical Details" style="gap: 5px;">
                            <i class='bx bx-bar-chart-alt-2'></i> Details
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
