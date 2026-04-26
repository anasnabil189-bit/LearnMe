@extends('layouts.app')

@section('title', 'Manage Educational Levels')
@section('page-title', 'Course Tracks Levels System')

@section('topbar-actions')
    @if(request()->has('language_id'))
    <a href="{{ route('admin.levels.index') }}" class="btn btn-secondary" style="margin-right: 10px;">
        <i class='bx bx-arrow-back'></i> Back to Languages
    </a>
    @endif
    <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Level
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">All levels</h2>
        <p style="color:var(--text-muted); font-size:14px; margin-top:5px;">Levels are used for the course system available to all students outside of classroom settings.</p>
    </div>

    @if($levels->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Level</th>
                        <th style="background: none; border: none;">Language</th>
                        <th style="background: none; border: none;">XP Required</th>
                        <th style="background: none; border: none;">Lessons</th>
                        <th style="background: none; border: none;">Quizzes</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($levels as $level)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px;">{{ $level->id }}</td>
                        <td><span class="badge badge-accent">{{ $level->name }}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class='bx bx-globe' style="color: var(--primary-light);"></i>
                                {{ $level->language->name ?? '---' }}
                            </div>
                        </td>
                        <td>{{ $level->required_xp }} XP</td>
                        <td>{{ $level->lessons_count }}</td>
                        <td>{{ $level->quizzes_count }}</td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('admin.levels.show', $level->id) }}" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px;" title="View Content"><i class='bx bx-show' style="color: var(--primary);"></i></a>
                            <a href="{{ route('admin.levels.edit', $level->id) }}" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px;" title="Edit"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            
                            <form action="{{ route('admin.levels.destroy', $level->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this level?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px; border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $levels->links() }}</div>
    @else
        <div class="empty-state">
            <i class='bx bx-layer'></i>
            <h3>No levels found</h3>
            <p>Add levels like (Beginner, Intermediate, Advanced) to organize course track lessons.</p>
        </div>
    @endif
</div>
@endsection
