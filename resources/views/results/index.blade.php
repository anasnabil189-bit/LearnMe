@extends('layouts.app')

@php
    $type = auth()->user()->type;
    $prefix = in_array($type, ['admin', 'manager']) ? 'admin' : ($type === 'teacher' ? 'teacher' : 'user');
@endphp

@section('title', 'Results & Assessment')
@section('page-title', 'Quiz Results')

@section('content')
<div class="card shadow-premium" style="border-radius: 28px; border: 1px solid var(--border);">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 30px; border-bottom: 1px solid var(--border); background: #ffffff;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(20, 184, 166, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class='bx bxs-bar-chart-alt-2'></i>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 900; color: var(--text);">
                    {{ ($type === 'user' && !empty($isSchoolStudent)) ? 'Academic Learning Results' : ($type === 'user' ? 'Course Progress Results' : 'Student Performance Results') }}
                </h2>
                <p style="color: var(--text-muted); font-size: 14px; margin: 0; font-weight: 600;">Review progress and assessment history.</p>
            </div>
        </div>

        @if($type === 'user' && !empty($isSchoolStudent) && $unlockedTeachers->count() > 0)
            <div class="teacher-filter">
                <form action="{{ route('user.results.index') }}" method="GET" id="teacherFilterForm">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Filter By:</span>
                        <div style="position: relative;">
                            <select name="teacher_id" id="teacher_id" onchange="this.form.submit()" style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 10px 40px 10px 15px; color: var(--text); font-size: 0.9rem; font-weight: 700; cursor: pointer; min-width: 220px; box-shadow: var(--shadow-sm); outline: none; transition: 0.3s; appearance: none;">
                                <option value="">All My Teachers</option>
                                @foreach($unlockedTeachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ (isset($selectedTeacherId) && $selectedTeacherId == $teacher->id) ? 'selected' : '' }}>
                                        👨‍🏫 {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class='bx bx-chevron-down' style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted);"></i>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>

    @if(isset($assignments))
        <div class="table-wrap" style="padding: 20px;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 12px;">
                <thead>
                    <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 0 20px;">Grade Name</th>
                        <th>Subject / Language</th>
                        <th>Total Results</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr style="background: #ffffff; border: 1px solid var(--border); transition: 0.3s;" class="hover-elevate">
                        <td style="padding: 24px 20px; border-radius: 16px 0 0 16px; border: 1px solid var(--border); border-right: none; font-weight: 800; color: var(--text);">
                            {{ $assignment->grade->name }}
                        </td>
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                            <span class="badge" style="background: rgba(20, 184, 166, 0.1); color: var(--primary); padding: 6px 14px; border-radius: 10px; font-weight: 800; font-size: 12px;">{{ $assignment->schoolLanguage->name }}</span>
                        </td>
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: var(--text);">
                                <i class='bx bxs-group' style="color: var(--primary); font-size: 18px;"></i>
                                {{ $assignment->results_count }} Attempts
                            </div>
                        </td>
                        <td style="padding: 10px 20px; border-radius: 0 16px 16px 0; border: 1px solid var(--border); border-left: none; text-align: right;">
                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <a href="{{ route('teacher.results.by_grade', [$assignment->grade_id, $assignment->school_language_id]) }}" class="btn btn-sm btn-ghost" style="color: var(--primary); font-weight: 800;">
                                    Results <i class='bx bx-chevron-right'></i>
                                </a>
                                <a href="{{ route('teacher.leaderboard.by_grade', [$assignment->grade_id, $assignment->school_language_id]) }}" class="btn btn-sm btn-ghost" style="color: #f59e0b; font-weight: 800;">
                                    Ranking <i class='bx bxs-trophy'></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($results->count() > 0)
        <div class="table-wrap" style="padding: 25px;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 12px;">
                <thead>
                    <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 0 25px;">#</th>
                        @unless($type === 'user') <th>Student</th> @endunless
                        <th>Quiz Title</th>
                        @if(in_array($type, ['admin', 'manager'])) <th>Language</th> @endif
                        <th>Context</th>
                        <th>Points</th>
                        <th>Date</th>
                        <th style="text-align: right; padding-right: 25px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $res)
                    @php $isBest = isset($bestAttemptIds) && in_array($res->id, $bestAttemptIds); @endphp
                    <tr style="background: #ffffff; border: 1px solid var(--border); transition: 0.3s;" class="hover-elevate">
                        <td style="padding: 24px 25px; border-radius: 18px 0 0 18px; border: 1px solid var(--border); border-right: none; color: var(--text-muted); font-weight: 700; font-size: 14px;">#{{ $res->id }}</td>
                        @unless($type === 'user')
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), #0d9488); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 900;">
                                    {{ mb_substr($res->user->name ?? '?', 0, 1) }}
                                </div>
                                <span style="font-weight: 800; color: var(--text); font-size: 15px;">{{ $res->user->name ?? '---' }}</span>
                            </div>
                        </td>
                        @endunless
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none; font-weight: 700; color: var(--text); font-size: 15px;">{{ $res->quiz->title ?? '---' }}</td>
                        @if(in_array($type, ['admin', 'manager']))
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                             <span class="badge" style="background: #f1f5f9; color: var(--text-muted); font-weight: 800; font-size: 11px;">{{ $res->quiz->schoolLanguage->name ?? $res->quiz->language->name ?? 'Global' }}</span>
                        </td>
                        @endif
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                            @if($res->quiz->lesson_id)
                                <div style="color: #9333ea; font-weight: 800; font-size: 11px; display: flex; align-items: center; gap: 6px;">
                                    <i class='bx bxs-book-open'></i> Lesson: {{ $res->quiz->lesson->title ?? '' }}
                                </div>
                            @elseif($res->quiz->grade_id)
                                <div style="color: var(--primary); font-weight: 800; font-size: 11px; display: flex; align-items: center; gap: 6px;">
                                    <i class='bx bxs-graduation'></i> General
                                </div>
                            @else
                                <span style="font-size: 11px; font-weight: 800; color: #64748b;">Self-Path</span>
                            @endif
                        </td>
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                            <div style="display: flex; align-items: center; gap: 5px; font-weight: 900; color: #f59e0b; font-size: 1.1rem;">
                                <i class='bx bxs-star'></i> {{ (int)$res->xp_earned }}
                                @if($isBest) <span title="Personal Best" style="font-size: 12px;">🏆</span> @endif
                            </div>
                        </td>
                        <td style="border: 1px solid var(--border); border-left: none; border-right: none; color: var(--text-muted); font-size: 13px; font-weight: 600;">{{ $res->created_at->format('M d, Y') }}</td>
                        <td style="padding: 10px 20px; border-radius: 0 16px 16px 0; border: 1px solid var(--border); border-left: none; text-align: right;">
                            <a href="{{ route($prefix . '.results.show', $res->id) }}" class="btn btn-sm btn-ghost" style="color: var(--primary); font-weight: 800;">
                                <i class='bx bx-show'></i> Analysis
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 20px;">{{ $results->links() }}</div>
    @else
        <div class="empty-state" style="padding: 80px 20px; text-align: center;">
            <div style="width: 100px; height: 100px; background: #f8fafc; border-radius: 30px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 3rem; color: #cbd5e1;">
                <i class='bx bx-bar-chart-alt-2'></i>
            </div>
            <h3 style="font-weight: 900; color: var(--text);">No entries found</h3>
            <p style="color: var(--text-muted); font-weight: 600;">Complete assessments to see performance metrics here.</p>
        </div>
    @endif
</div>
@endsection
