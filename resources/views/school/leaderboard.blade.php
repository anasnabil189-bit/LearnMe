@extends('layouts.app')

@section('title', 'Student Ranking | School')
@section('page-title', 'Leaderboard')

@section('content')
<div class="page-header">
    <div>
        <h1>🏆 Leaderboard</h1>
        <p>Ranking of students in <strong style="color:var(--primary-light);">{{ $school->name }}</strong> by Experience Points (XP)</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Top Students List</h2>
    </div>

    @if($students->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Rank</th>
                        <th>Student Name</th>
                        <th style="text-align: center;">Student ID</th>
                        <th style="text-align: center;">Total Points (XP)</th>
                        <th>Current Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                    <tr style="{{ $index < 3 ? 'background: rgba(15, 118, 110, 0.05);' : '' }}">
                        <td style="text-align: center;">
                            @if($index == 0)
                                <span style="font-size: 24px;">🥇</span>
                            @elseif($index == 1)
                                <span style="font-size: 24px;">🥈</span>
                            @elseif($index == 2)
                                <span style="font-size: 24px;">🥉</span>
                            @else
                                <span class="badge badge-ghost" style="font-size: 14px;">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px; background: {{ $index < 3 ? 'var(--accent)' : 'var(--primary)' }}">
                                    {{ mb_substr($student->name, 0, 1) }}
                                </div>
                                <strong>{{ $student->name }}</strong>
                            </div>
                        </td>
                        <td style="text-align: center; font-family: monospace; font-weight: bold; color: var(--primary);">{{ $student->id }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-accent" style="font-size: 14px; padding: 6px 12px;">
                                <i class='bx bxs-zap'></i> {{ ($student->learning_xp ?? 0) + ($student->challenge_xp ?? 0) }} XP
                            </span>
                        </td>
                        <td>
                            @php
                                $currentLevel = \App\Models\Level::where('required_xp', '<=', $student->learning_xp ?? 0)
                                    ->orderBy('required_xp', 'desc')
                                    ->first();
                            @endphp
                            @if($currentLevel)
                                <span class="badge badge-primary">{{ $currentLevel->name }}</span>
                            @else
                                <span class="badge badge-ghost">Beginner</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-trophy'></i>
            <h3>No students registered yet</h3>
            <p>Once students join the school, they will appear here based on their activity and points.</p>
        </div>
    @endif
</div>
@endsection
