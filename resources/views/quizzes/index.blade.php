@extends('layouts.app')

@section('title', 'Manage Interactive Quizzes')
@section('page-title', 'Quizzes')

@section('topbar-actions')
    @if(in_array(auth()->user()->type, ['admin', 'manager']))
    @if(request()->has('language_id'))
    <a href="{{ route($prefix . '.quizzes.index') }}" class="btn btn-secondary" style="margin-right: 10px;">
        <i class='bx bx-arrow-back'></i> Back to Languages
    </a>
    @endif
    <a href="{{ route($prefix . '.quizzes.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Quiz
    </a>
    @endif
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Quiz Bank</h2>
        @if(auth()->user()->type === 'user')
        <p style="color:var(--text-muted); font-size:14px; margin-top:5px;">Available quizzes to evaluate your level and earn XP based on enrolled classes.</p>
        @endif
    </div>

    @if(isset($assignments))
        {{-- Teacher Mode: Grouped by Grade --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">Grade Name</th>
                        <th style="background: none; border: none;">Subject / Language</th>
                        <th style="background: none; border: none;">Total Quizzes</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px; font-weight: 700;">{{ $assignment->grade->name }}</td>
                        <td>
                            <span class="badge badge-primary">{{ $assignment->schoolLanguage->name }}</span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                                <i class='bx bx-brain' style="color: var(--success);"></i>
                                {{ $assignment->quizzes_count }} Quizzes
                            </div>
                        </td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('teacher.quizzes.by_grade', [$assignment->grade_id, $assignment->school_language_id]) }}" class="btn btn-sm btn-ghost" style="color: var(--accent); font-weight: 700; gap: 5px;">
                                <i class='bx bx-cog'></i> Manage Content
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($quizzes->count() > 0)
        {{-- Standard List (Admin/User) --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Quiz Title</th>
                        <th style="background: none; border: none;">Path/Context</th>
                        <th style="background: none; border: none;">Questions</th>
                        <th style="background: none; border: none;">Added Date</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                    <tr style="border-bottom: 2px solid var(--border); background: #fff; border-radius: 12px; transition: 0.3s;">
                        <td style="border-radius: 16px 0 0 16px; font-weight: 800; color: var(--primary);">#{{ $quiz->id }}</td>
                        <td style="font-weight: 700;">{{ $quiz->title }}</td>
                        <td>
                            @if($quiz->grade_id && $quiz->school_language_id)
                                <span class="badge badge-primary">{{ $quiz->grade->name ?? '' }} | {{ $quiz->schoolLanguage->name ?? '' }}</span>
                            @elseif($quiz->level_id)
                                <span class="badge badge-accent">{{ $quiz->level->name ?? '' }}</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:var(--text-muted);">Not Specified</span>
                            @endif
                        </td>
                        <td><div style="font-weight: 800; color: var(--text);"><i class='bx bx-brain' style="color:var(--accent);"></i> {{ $quiz->questions_count }} Qs</div></td>
                        <td style="color:var(--text-muted); font-size:14px; font-weight: 600;">{{ $quiz->created_at->format('M d, Y') }}</td>
                        <td style="border-radius: 0 16px 16px 0;">
                            <div style="display: flex; gap: 8px;">
                                @if(in_array(auth()->user()->type, ['admin', 'manager', 'teacher']))
                                <a href="{{ route($prefix . '.quizzes.show', $quiz->id) }}" class="btn btn-sm btn-ghost" style="padding: 8px; border-radius: 10px;" title="Manage Questions"><i class='bx bx-list-ul' style="color: var(--primary); font-size: 18px;"></i></a>
                                <a href="{{ route($prefix . '.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-ghost" style="padding: 8px; border-radius: 10px;" title="Edit"><i class='bx bx-edit' style="color: var(--accent); font-size: 18px;"></i></a>
                                
                                <form action="{{ route($prefix . '.quizzes.destroy', $quiz->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="padding: 8px; border-radius: 10px; border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger); font-size: 18px;"></i></button>
                                </form>
                                @elseif(auth()->user()->type === 'user')
                                <a href="{{ route('user.quizzes.take', $quiz->id) }}" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; border-radius: 10px;">Start Quiz</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $quizzes->links() }}</div>
    @else
        <div class="empty-state">
            <i class='bx bx-edit-alt'></i>
            <h3>No quizzes currently available</h3>
            <p>The quiz records are empty. Create content to evaluate students.</p>
        </div>
    @endif
</div>
@endsection
