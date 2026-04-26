@extends('layouts.app')

@section('title', 'Teacher Assignments')
@section('page-title', 'Academic Staff')

@section('topbar-actions')
    <a href="{{ route('school.teacher-assignments.create') }}" class="btn btn-primary">
        <i class='bx bx-user-plus'></i> Assign Teachers
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Teacher Academic Assignments</h2>
    </div>

    @if($teachers->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">Teacher Name</th>
                        <th style="background: none; border: none;">Assigned Teaching Classes</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px; font-weight: 700;">{{ $teacher->name }}</td>
                        <td>
                            @if($teacher->teacherAssignments->isEmpty())
                                <span class="badge" style="background:#eee;color:#333;">Not Assigned</span>
                            @else
                                <div style="display:flex; flex-wrap:wrap; gap:5px;">
                                @foreach($teacher->teacherAssignments as $assignment)
                                    <span class="badge badge-primary">{{ $assignment->grade->name }} - {{ $assignment->schoolLanguage->name }}</span>
                                @endforeach
                                </div>
                            @endif
                        </td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('school.teacher-assignments.create', ['teacher_id' => $teacher->id]) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i> Manage</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-user-x'></i>
            <h3>No Teachers Found</h3>
            <p>You need to add teachers to your school first before assigning them.</p>
            <a href="{{ route('school.teachers.create') }}" class="btn btn-primary" style="margin-top: 15px;">Add Teacher</a>
        </div>
    @endif
</div>
@endsection
