@extends('layouts.app')

@section('title', 'Manage Grades')
@section('page-title', 'Academic Grades')

@section('topbar-actions')
    <a href="{{ route('school.grades.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Grade
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">School Grades Overview</h2>
    </div>

    @if($grades->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Grade Name</th>
                        <th style="background: none; border: none;">Grade Code</th>
                        <th style="background: none; border: none;">Supported Languages</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $grade)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px;">{{ $loop->iteration }}</td>
                        <td style="font-weight: 700;">{{ $grade->name }}</td>
                        <td><span style="font-family: monospace; background: var(--bg); padding: 4px 8px; border-radius: 6px; user-select: all;">{{ $grade->code }}</span></td>
                        <td>
                            @foreach($grade->schoolLanguages as $lang)
                                <span class="badge badge-accent">{{ $lang->name }}</span>
                            @endforeach
                            @if($grade->schoolLanguages->isEmpty())
                                <span class="badge" style="background:#eee;color:#333;">None Linked</span>
                            @endif
                        </td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('school.grades.edit', $grade->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            <form action="{{ route('school.grades.destroy', $grade->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this grade?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-layer'></i>
            <h3>No Grades Found</h3>
            <p>Setup your school's academic hierarchy by adding grades.</p>
            <a href="{{ route('school.grades.create') }}" class="btn btn-primary" style="margin-top: 15px;">Add First Grade</a>
        </div>
    @endif
</div>
@endsection
