@extends('layouts.app')

@section('title', 'Language Details - ' . $language->name)
@section('page-title', $language->name . ' Statistics')

@section('content')
<div class="page-header">
    <div>
        <h1 style="display:flex; align-items:center; gap:12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 18px;">
                {{ strtoupper($language->code) }}
            </div>
            {{ $language->name }} Performance
        </h1>
        <p>Detailed breakdown of curriculum distribution and proficiency levels</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Back to Dashboard</a>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2 class="card-title"><i class='bx bxs-layer' style="color:var(--accent);"></i> Proficiency Levels Breakdown</h2>
    </div>
    
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Level Name</th>
                    <th>Required XP</th>
                    <th>Lessons Count</th>
                    <th>Quizzes Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse($levels as $level)
                <tr>
                    <td>
                        <span class="badge badge-primary-light" style="font-size: 14px; padding: 6px 12px; font-weight: 700;">
                            {{ $level->name }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--accent);">
                            <i class='bx bxs-star'></i> {{ number_format($level->required_xp) }} XP
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class='bx bx-book-content' style="color: var(--info);"></i> {{ $level->lessons_count }} Lessons
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class='bx bx-brain' style="color: var(--success);"></i> {{ $level->quizzes_count }} Quizzes
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding: 40px; color: var(--text-muted);">
                        <i class='bx bx-info-circle' style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                        No levels documented for this language yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
