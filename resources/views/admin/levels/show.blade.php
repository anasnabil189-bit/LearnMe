@extends('layouts.app')

@section('title', 'Level Details')
@section('page-title', 'View Level: ' . $level->name)

@section('topbar-actions')
    <a href="{{ route('admin.levels.edit', $level->id) }}" class="btn btn-primary">
        <i class='bx bx-edit'></i> Edit Level
    </a>
    <a href="{{ route('admin.levels.index') }}" class="btn btn-ghost">
        <i class='bx bx-left-arrow-alt'></i> Back to List
    </a>
@endsection

@section('content')
<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Left Side: Level Info -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Basic Information</h2>
            </div>
            <div style="padding: 15px 0;">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Language</label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class='bx bx-globe' style="color: var(--primary-light);"></i>
                        <span style="font-size: 18px; font-weight: 700;">{{ $level->language->name ?? '---' }}</span>
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">XP Unlock Points</label>
                    <div style="font-size: 24px; font-weight: 800; color: var(--accent);">{{ $level->required_xp }} XP</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Level Statistics</h2>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 15px 0;">
                <div style="background: var(--bg2); padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 22px; font-weight: 800; color: var(--primary);">{{ $level->lessons->count() }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">Lessons</div>
                </div>
                <div style="background: var(--bg2); padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 22px; font-weight: 800; color: var(--accent);">{{ $level->quizzes->count() }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">Quizzes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Lessons & Quizzes -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">Lessons in this Level</h2>
                <a href="{{ route('admin.lessons.create', ['level_id' => $level->id]) }}" class="btn btn-sm btn-primary">Add New Lesson</a>
            </div>
            @if($level->lessons->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="background: none; border: none;">#</th>
                                <th style="background: none; border: none;">Title</th>
                                <th style="background: none; border: none;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($level->lessons as $lesson)
                                <tr>
                                    <td>{{ $lesson->id }}</td>
                                    <td>{{ $lesson->title }}</td>
                                    <td>
                                        <a href="{{ route('admin.lessons.edit', $lesson->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding: 20px; text-align: center; color: var(--text-muted);">Currently, there are no lessons assigned to this level.</p>
            @endif
        </div>

        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">Quizzes in this Level</h2>
                <a href="{{ route('admin.quizzes.create', ['level_id' => $level->id]) }}" class="btn btn-sm btn-primary">Add New Quiz</a>
            </div>
            @if($level->quizzes->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="background: none; border: none;">#</th>
                                <th style="background: none; border: none;">Title</th>
                                <th style="background: none; border: none;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($level->quizzes as $quiz)
                                <tr>
                                    <td>{{ $quiz->id }}</td>
                                    <td>{{ $quiz->title }}</td>
                                    <td>
                                        <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding: 20px; text-align: center; color: var(--text-muted);">Currently, there are no quizzes assigned to this level.</p>
            @endif
        </div>
    </div>
</div>
@endsection
