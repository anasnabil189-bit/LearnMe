@extends('layouts.app')

@section('title', 'Manage Quizzes for ' . $grade->name)
@section('page-title', $grade->name . ' - ' . $language->name)

@section('topbar-actions')
    <a href="{{ route('teacher.quizzes.create', ['grade_language' => $grade->id . '|' . $language->id]) }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Quiz
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 class="card-title">Grade Quiz Library</h2>
            <p style="color:var(--text-muted); font-size:14px; margin-top:5px;">Managing evaluations for {{ $grade->name }} ({{ $language->name }})</p>
        </div>
        <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-secondary btn-sm"><i class='bx bx-arrow-back'></i> Back to Grades</a>
    </div>

    @if($quizzes->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Quiz Title</th>
                        <th style="background: none; border: none;">Type</th>
                        <th style="background: none; border: none;">Questions</th>
                        <th style="background: none; border: none;">Added Date</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px;">{{ $quiz->id }}</td>
                        <td style="font-weight:700;">{{ $quiz->title }}</td>
                        <td>
                            @if($quiz->academic_type === 'lesson')
                                <span class="badge" style="background:#dcfce7; color:#166534;">Lesson Bound</span>
                            @else
                                <span class="badge" style="background:#fef3c7; color:#92400e;">General Exam</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                                <i class='bx bx-list-check' style="color: var(--primary);"></i>
                                {{ $quiz->questions_count }} Questions
                            </div>
                        </td>
                        <td style="color:var(--text-muted); font-size:14px;">{{ $quiz->created_at->format('Y-m-d') }}</td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('teacher.quizzes.show', $quiz->id) }}" class="btn btn-sm btn-ghost" title="Manage Questions"><i class='bx bx-list-ul' style="color: var(--primary);"></i></a>
                            <a href="{{ route('teacher.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            
                            <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this quiz permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
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
            <h3>No quizzes added yet</h3>
            <p>You haven't added any evaluations for this grade yet. Create a quiz to get started!</p>
        </div>
    @endif
</div>
@endsection
