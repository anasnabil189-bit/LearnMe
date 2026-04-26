@extends('layouts.app')

@section('title', 'Dashboard | School')
@section('page-title', 'School Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Welcome, School Admin 👋</h1>
        <p>Overview for <strong style="color:var(--primary);">{{ $school->name }}</strong></p>
    </div>
    <div style="display: flex; align-items: center; gap: 32px;">
        <div style="display: flex; flex-direction: column; gap: 4px; text-align: right;">
            <span style="font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">School Code</span>
            <span style="font-size: 24px; font-weight: 900; color: var(--primary); letter-spacing: 2px;">{{ $school->code }}</span>
        </div>
        <div style="width: 1px; height: 50px; background: var(--border);"></div>
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <span style="font-size: 13px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Subscription</span>
            <span class="badge badge-accent" style="font-size: 14px; padding: 6px 14px; font-weight: 800;">PLAN {{ strtoupper($school->plan_type) }}</span>
        </div>
    </div>
</div>


<div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-top: 20px;">
    <div class="stat-card accent">
        <div class="stat-icon"><i class='bx bxs-user-badge'></i></div>
        <div class="stat-value">{{ $stats['teachers_count'] }}</div>
        <div class="stat-label">Total Teachers</div>
    </div>
    <div class="stat-card primary">
        <div class="stat-icon"><i class='bx bxs-graduation'></i></div>
        <div class="stat-value">{{ $stats['students_count'] }}</div>
        <div class="stat-label">Total Enrolled Students</div>
    </div>
</div>

<!-- Teachers and Students Statistics Table -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h2 class="card-title"><i class='bx bx-list-ul'></i> Teacher and Student Statistics</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Teacher Name</th>
                    <th>Languages Taught</th>
                    <th>Number of Grades</th>
                    <th>Following Students</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teachersData as $tData)
                <tr>
                    <td><strong>{{ $tData['name'] }}</strong></td>
                    <td><span class="badge badge-accent">{{ $tData['languages'] }}</span></td>
                    <td><span class="badge badge-primary">{{ $tData['assignments_count'] }}</span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class='bx bxs-group' style="color:var(--info);"></i> 
                            <b>{{ $tData['students_count'] }}</b>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('school.teachers.show', $tData['id']) }}" class="btn btn-sm btn-ghost" title="View Details"><i class='bx bx-show'></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(!$school)
<div class="alert alert-danger" style="margin-top:20px;">
    Please link your account to a school to view its statistics. Contact the administration.
</div>
@endif
@endsection
