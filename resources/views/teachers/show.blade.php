@extends('layouts.app')

@section('title', 'Teacher Details | School')
@section('page-title', 'Teacher Data')

@section('topbar-actions')
    <a href="{{ url()->previous() }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> Back</a>
@endsection

@section('content')
<div class="stats-grid" style="grid-template-columns: 1fr 2fr;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Personal Information</h2>
        </div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 20px 0;">
            <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px;">
                {{ mb_substr($teacher->name, 0, 1) }}
            </div>
            <div style="text-align: center;">
                <h3 style="font-size: 20px; font-weight: 700;">{{ $teacher->name }}</h3>
                <p style="color: var(--text-muted); font-size: 14px; font-family: monospace;">{{ $teacher->email }}</p>
                <div class="badge badge-primary" style="margin-top: 10px;">{{ optional($teacher->school)->name ?? 'Not Assigned' }}</div>
            </div>
        </div>
        
        <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 10px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Join Date:</span>
                <span>{{ $teacher->created_at->format('Y-m-d') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Total Assignments:</span>
                <span class="badge badge-ghost">{{ $teacher->teacherAssignments->count() }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Assigned Subjects and Grades</h2>
        </div>

        @if($teacher->teacherAssignments->count() > 0)
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Grade Level</th>
                            <th>Subject / Language</th>
                            <th style="text-align: center;">Assignment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teacher->teacherAssignments as $assignment)
                        <tr>
                            <td><strong>{{ $assignment->grade->name }}</strong></td>
                            <td><span class="badge badge-info">{{ $assignment->schoolLanguage->name }}</span></td>
                            <td style="text-align: center; color: var(--text-muted);">{{ $assignment->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class='bx bx-folder-open'></i>
                <h3>No assignments found</h3>
                <p>No subjects or grades have been assigned to this teacher yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
