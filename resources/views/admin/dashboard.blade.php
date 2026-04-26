@extends('layouts.app')

@section('title', 'Admin Control Panel')
@section('page-title', 'System Overview')

@section('content')
<div class="page-header">
    <div>
        <h1>Welcome, Admin 👋</h1>
        <p>Here is an overview of the entire platform performance</p>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
    <!-- Income Stats -->
    <div class="stat-card success" style="cursor: pointer; position: relative;" onclick="window.location.href='{{ route('admin.transactions') }}'">
        <div class="stat-icon"><i class='bx bx-wallet'></i></div>
        <div class="stat-value">{{ number_format($stats['courses_total_income'], 0) }} <small style="font-size: 14px;">EGP</small></div>
        <div class="stat-label">Total Platform Revenue <i class='bx bx-link-external' style="font-size: 11px; margin-left: 2px;"></i></div>
    </div>

    <div class="stat-card accent" style="cursor: pointer; position: relative;" onclick="window.location.href='{{ route('admin.transactions') }}'">
        <div class="stat-icon"><i class='bx bx-line-chart'></i></div>
        <div class="stat-value">{{ number_format($stats['courses_monthly_income'], 0) }} <small style="font-size: 14px;">EGP</small></div>
        <div class="stat-label">Revenue This Month <i class='bx bx-link-external' style="font-size: 11px; margin-left: 2px;"></i></div>
    </div>

    <!-- Student Stats -->
    <div class="stat-card primary">
        <div class="stat-icon"><i class='bx bxs-graduation'></i></div>
        <div class="stat-value">{{ number_format($stats['school_students']) }}</div>
        <div class="stat-label">Total School Students</div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon"><i class='bx bxs-user-detail'></i></div>
        <div class="stat-value">{{ number_format($stats['individual_students']) }}</div>
        <div class="stat-label">Total Course Students</div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h2 class="card-title"><i class='bx bx-stats' style="color:var(--primary);"></i> Educational Distribution</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Language</th>
                    <th>Course Students</th>
                    <th>School Students</th>
                    <th>Total Enrollment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($languagesStats as $lang)
                <tr>
                    <td>
                        <strong style="color: var(--text);">{{ $lang->name }}</strong>
                    </td>
                    <td>{{ number_format($lang->courses_count) }}</td>
                    <td>{{ number_format($lang->school_count) }}</td>
                    <td>
                        <span class="badge badge-primary-light" style="font-weight: 800;">
                            {{ number_format($lang->courses_count + $lang->school_count) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-top: 24px;">
    <!-- Infrastructure -->
    <div style="background: #fff; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border); display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; background: rgba(20, 184, 166, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;"><i class='bx bxs-school'></i></div>
        <div>
            <div style="font-size: 18px; font-weight: 800; color: var(--text);">{{ $stats['total_schools'] }}</div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Active Schools</div>
        </div>
    </div>

    <div style="background: #fff; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border); display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; background: rgba(6, 182, 212, 0.1); color: var(--info); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;"><i class='bx bx-buildings'></i></div>
        <div>
            <div style="font-size: 18px; font-weight: 800; color: var(--text);">{{ $stats['total_organizations'] }}</div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Organizations</div>
        </div>
    </div>
</div>

@endsection
