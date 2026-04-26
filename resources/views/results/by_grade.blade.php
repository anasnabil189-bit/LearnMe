@extends('layouts.app')

@section('title', 'Student Results for ' . $grade->name)
@section('page-title', $grade->name .' - ' . $language->name)

@section('content')
<div class="results-index-page animate-fade-in">
    <div class="card shadow-premium" style="border-radius: 32px; border: 1px solid var(--border);">
        <div class="card-header" style="padding: 35px; border-bottom: 1px solid var(--border); background: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(20, 184, 166, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px;">
                    <i class='bx bxs-report'></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 1.6rem; font-weight: 900; color: var(--text);">Performance Record</h2>
                    <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 600; margin-top: 5px;">
                        Learners in <span style="color: var(--primary);">{{ $grade->name }}</span> ({{ $language->name }})
                    </p>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('teacher.leaderboard.by_grade', [$grade->id, $language->id]) }}" class="btn btn-ghost" style="color: #f59e0b; border: 2px solid #fef3c7; background: #fffbeb; font-weight: 800; border-radius: 14px; padding: 10px 20px;">
                    <i class='bx bxs-trophy'></i> Class Ranking
                </a>
                <a href="{{ route('teacher.results.index') }}" class="btn btn-ghost" style="color: var(--text-muted); font-weight: 800; font-size: 14px;">
                    <i class='bx bx-arrow-back'></i> Back
                </a>
            </div>
        </div>

        @if($results->count() > 0)
            <div class="table-wrap" style="padding: 25px;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0 12px;">
                    <thead>
                        <tr style="text-align: left; color: var(--text-muted); font-size: 0.85rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 0 25px;">#</th>
                            <th>Student</th>
                            <th>Quiz Title</th>
                            <th>Points</th>
                            <th>Date</th>
                            <th style="text-align: right; padding-right: 25px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $res)
                        <tr style="background: #ffffff; border: 1px solid var(--border); transition: 0.3s;" class="hover-elevate">
                            <td style="padding: 24px 25px; border-radius: 18px 0 0 18px; border: 1px solid var(--border); border-right: none; color: var(--text-muted); font-weight: 700; font-size: 14px;">#{{ $res->id }}</td>
                            <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), #0d9488); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 900;">
                                        {{ mb_substr($res->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <span style="font-weight: 800; color: var(--text); font-size: 15px;">{{ $res->user->name ?? '---' }}</span>
                                </div>
                            </td>
                            <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                                <div style="font-weight: 700; color: var(--text); font-size: 15px;">{{ $res->quiz->title ?? '---' }}</div>
                                @if($res->quiz->academic_type === 'lesson')
                                    <span style="font-size: 11px; font-weight: 800; color: #9333ea; background: rgba(147, 51, 234, 0.05); padding: 2px 8px; border-radius: 6px; margin-top: 4px; display: inline-block;">
                                        <i class='bx bxs-book-open'></i> {{ $res->quiz->lesson->title ?? '' }}
                                    </span>
                                @endif
                            </td>
                            <td style="border: 1px solid var(--border); border-left: none; border-right: none;">
                                <div style="display: flex; align-items: center; gap: 6px; font-weight: 900; color: #f59e0b; font-size: 1.1rem;">
                                    <i class='bx bxs-star'></i> {{ (int)$res->xp_earned }}
                                </div>
                            </td>
                            <td style="border: 1px solid var(--border); border-left: none; border-right: none; color: var(--text-muted); font-size: 14px; font-weight: 700;">{{ $res->created_at->format('M d, Y') }}</td>
                            <td style="padding: 10px 25px; border-radius: 0 18px 18px 0; border: 1px solid var(--border); border-left: none; text-align: right;">
                                <a href="{{ route('teacher.results.show', $res->id) }}" class="btn btn-sm btn-ghost" style="color: var(--primary); font-weight: 900; font-size: 14px;">
                                    Analysis <i class='bx bx-right-arrow-alt'></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 20px;">{{ $results->links() }}</div>
        @else
            <div class="empty-state" style="padding: 100px 30px; text-align: center;">
                <div style="width: 120px; height: 120px; background: #f8fafc; border-radius: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 3.5rem; color: #cbd5e1;">
                    <i class='bx bx-history'></i>
                </div>
                <h3 style="font-weight: 950; color: var(--text); font-size: 1.5rem;">No attempts yet</h3>
                <p style="color: var(--text-muted); font-weight: 600; font-size: 1.1rem; max-width: 400px; margin: 10px auto;">Class performance metrics will appear here once students start completing assessments.</p>
            </div>
        @endif
    </div>
</div>
@endsection
