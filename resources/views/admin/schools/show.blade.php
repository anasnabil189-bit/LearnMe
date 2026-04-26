@extends('layouts.app')

@section('title', 'School Details')
@section('page-title', 'Viewing details for: ' . $school->name)

@section('topbar-actions')
    <a href="{{ route('admin.schools.edit', $school->id) }}" class="btn btn-primary">
        <i class='bx bx-edit'></i> Edit School
    </a>
    <a href="{{ route('admin.schools.index') }}" class="btn btn-ghost">
        <i class='bx bx-arrow-back'></i> Back to List
    </a>
@endsection

@section('content')
<div class="grid" style="grid-template-columns: 1fr 3fr; gap: 24px;">
    <!-- Right Side: Basic Information -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">School Information</h2>
            </div>
            <div style="padding: 15px 0;">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Short Activation Code</label>
                    <span class="badge badge-primary" style="font-size: 16px; font-family: monospace;">{{ $school->code }}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Subscription Start Date</label>
                    <div style="font-weight: 700;">{{ $school->subscription_start ? $school->subscription_start->format('Y-m-d') : 'Not set' }}</div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Subscription End Date</label>
                    <div style="font-weight: 700; color: {{ $school->subscription_end && $school->subscription_end->isPast() ? 'var(--danger)' : 'inherit' }}">
                        {{ $school->subscription_end ? $school->subscription_end->format('Y-m-d') : 'Not set' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quick Statistics</h2>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 15px 0;">
                <div style="background: var(--bg2); padding: 10px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary);">{{ $school->teachers->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">Teacher(s)</div>
                </div>
                <div style="background: var(--bg2); padding: 10px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 800; color: var(--accent);">{{ $school->grades->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">Grade(s)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Left Side: Detailed Tables -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Teachers -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">Assigned Teachers</h2>
            </div>
            @if($school->teachers->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($school->teachers as $teacher)
                                <tr>
                                    <td>{{ $teacher->name ?? 'Not available' }}</td>
                                    <td style="font-family: monospace;">{{ $teacher->email ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding: 20px; text-align: center; color: var(--text-muted);">No teachers registered for this school yet.</p>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Grades</h2>
            </div>
            @if($school->grades->count() > 0)
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Grade Name</th>
                                <th>Join Code</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($school->grades as $grade)
                                <tr>
                                    <td>{{ $grade->name }}</td>
                                    <td><span class="badge" style="background: var(--bg2); font-family: monospace;">{{ $grade->code }}</span></td>
                                    <td>{{ $grade->students()->count() }} Student(s)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding: 20px; text-align: center; color: var(--text-muted);">No grades have been created yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
