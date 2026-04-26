@extends('layouts.app')

@section('title', 'Student Profile')
@section('page-title', 'Viewing Data: ' . ($student->name ?? 'Student'))

@section('topbar-actions')
    <a href="{{ auth()->user()->isAdmin() ? route('admin.students.index') : route('school.students.index') }}" class="btn btn-ghost">
        <i class='bx bx-arrow-back'></i> Back to List
    </a>
@endsection

@section('content')
<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Right Side: Student Info -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card" style="text-align: center; padding: 30px 20px;">
            <div style="width: 80px; height: 80px; background: var(--primary-light); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 800; margin: 0 auto 15px;">
                {{ mb_substr($student->name ?? 'S', 0, 1) }}
            </div>
            <h2 style="font-size: 20px; font-weight: 800; margin-bottom: 5px;">{{ $student->name ?? 'Unknown' }}</h2>
            <p style="color: var(--text-muted); font-size: 14px; direction: ltr; font-family: monospace;">{{ $student->email ?? '-' }}</p>
            
            <div style="margin-top: 25px; display: inline-block; background: var(--bg2); padding: 12px 24px; border-radius: 12px; border: 1px solid var(--border);">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Total Experience Points</div>
                <div style="font-size: 28px; font-weight: 900; color: var(--accent);">{{ ($student->learning_xp ?? 0) + ($student->challenge_xp ?? 0) }} XP</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">School Enrollments</h2>
            </div>
            <div style="padding: 15px 0;">
                @if($student->school_id)
                    <div style="margin: 0 15px 15px; background: rgba(var(--primary-rgb), 0.05); padding: 12px; border-radius: 12px; border: 1px solid rgba(var(--primary-rgb), 0.1);">
                        <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">Enrolled School:</div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--primary-light);">
                            <i class='bx bxs-school'></i> {{ optional($student->school)->name ?? 'Not Assigned' }}
                        </div>
                    </div>
                    
                    <div style="padding: 0 15px;">
                        <div style="font-size: 12px; font-weight: 700; margin-bottom: 10px; color: var(--text-muted); display: flex; align-items: center; gap: 5px;">
                            <i class='bx bxs-chalkboard'></i> Grade Level:
                        </div>
                        @if($student->gradesAsStudent->count() > 0)
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                                @foreach($student->gradesAsStudent as $grade)
                                    <div style="background: var(--surface2); padding: 10px; border-radius: 10px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                                        <div style="font-size: 13px; font-weight: 700;">{{ $grade->name }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="background: var(--bg2); padding: 15px; border-radius: 10px; text-align: center; border: 1px dashed var(--border); margin-bottom: 20px;">
                                <p style="color: var(--text-muted); font-size: 13px; margin: 0;">No grade level assigned yet.</p>
                            </div>
                        @endif

                        <div style="font-size: 12px; font-weight: 700; margin-bottom: 10px; color: var(--text-muted); display: flex; align-items: center; gap: 5px;">
                            <i class='bx bxs-user-badge'></i> Unlocked Teachers:
                        </div>
                        @if($student->unlockedTeachers->count() > 0)
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($student->unlockedTeachers as $teacher)
                                    <div style="background: var(--surface2); padding: 10px; border-radius: 10px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                                        <div style="font-size: 13px; font-weight: 700;">{{ $teacher->name }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="background: var(--bg2); padding: 15px; border-radius: 10px; text-align: center; border: 1px dashed var(--border);">
                                <p style="color: var(--text-muted); font-size: 13px; margin: 0;">No teachers unlocked yet.</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div style="text-align: center; padding: 25px 15px;">
                        <div style="width: 65px; height: 65px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                            <i class='bx bx-rocket' style="font-size: 32px; color: #3b82f6;"></i>
                        </div>
                        <h4 style="font-size: 15px; margin-bottom: 5px;">Course Tracks Path</h4>
                        <p style="color: var(--text-muted); font-size: 13px; margin: 0;">This student is currently learning through the courses system and does not belong to any educational institution.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Left Side: Quiz Results & Activity -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Quiz Results</h2>
            </div>
            @if($student->quizAttempts && $student->quizAttempts->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Score</th>
                                <th>Points Earned</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->quizAttempts->take(10) as $attempt)
                                <tr>
                                    <td>{{ $attempt->quiz->title ?? '-' }}</td>
                                    <td>
                                        @php
                                            $percentage = $attempt->total_points > 0 ? ($attempt->score / $attempt->total_points) * 100 : 0;
                                        @endphp
                                        <span class="badge {{ $percentage >= 50 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $attempt->score }} / {{ $attempt->total_points }}
                                        </span>
                                    </td>
                                    <td style="color: var(--accent); font-weight: 700;">+{{ $attempt->xp_earned ?? 0 }} XP</td>
                                    <td style="font-size: 12px; color: var(--text-muted);">{{ $attempt->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding: 30px; text-align: center; color: var(--text-muted);">The student has not taken any quizzes yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
