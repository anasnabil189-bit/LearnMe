@extends('layouts.app')

@section('title', 'Schools Management')
@section('page-title', 'Registered Schools')

@section('topbar-actions')
    {{-- Add School button removed as schools now self-register --}}
@endsection

@section('content')
<!-- Pending Schools Section -->
<div class="card" style="margin-bottom: 30px; border-top: 4px solid var(--accent);">
    <div class="card-header" style="background: rgba(245, 158, 11, 0.05);">
        <h2 class="card-title"><i class='bx bx-time-five' style="vertical-align: middle; margin-right: 8px;"></i> Pending School Registrations</h2>
    </div>

    @if(isset($pendingSchools) && count($pendingSchools) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>School Name</th>
                        <th>Admin Email</th>
                        <th>Plan</th>
                        <th>Max Students</th>
                        <th>Reg. Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingSchools as $school)
                    <tr>
                        <td>{{ $school->id }}</td>
                        <td><strong>{{ $school->name }}</strong></td>
                        <td>{{ $school->teachers->first()->email ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-primary">Plan {{ $school->plan_type }}</span>
                            @if($school->subscription_start)
                                <span class="badge badge-success" style="margin-top:4px; display:inline-block;"><i class='bx bx-check-circle'></i> Paid</span>
                            @else
                                <span class="badge badge-warning" style="margin-top:4px; display:inline-block;"><i class='bx bx-time'></i> Unpaid</span>
                            @endif
                        </td>
                        <td><span class="badge badge-info">{{ number_format($school->student_limit) }}</span></td>
                        <td>{{ $school->created_at->format('Y-m-d H:i') }}</td>
                        <td style="display: flex; gap: 8px;">
                            <form action="{{ route('admin.schools.approve', $school->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary" style="background: var(--success); border: none;">
                                    <i class='bx bx-check'></i> Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.schools.reject', $school->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to REJECT this school?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger); border-color: var(--danger);">
                                    <i class='bx bx-x'></i> Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state" style="padding: 30px;">
            <i class='bx bx-check-circle' style="color: var(--success);"></i>
            <h3>No pending registrations</h3>
            <p>All school registration requests have been processed.</p>
        </div>
    @endif
</div>

<!-- Registered Schools Section -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Approved Schools</h2>
    </div>

    @if(isset($approvedSchools) && count($approvedSchools) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>School Name</th>
                        <th>Activation Code</th>
                        <th>Subscription</th>
                        <th>Annual Fee</th>
                        <th>Students</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedSchools as $school)
                    <tr>
                        <td>{{ $school->id }}</td>
                        <td>{{ $school->name }}</td>
                        <td><span class="badge badge-primary">{{ $school->code }}</span></td>
                        <td><span class="badge badge-outline">Plan {{ $school->plan_type }}</span></td>
                        <td><span class="badge badge-success">{{ number_format($school->annual_subscription_fee, 2) }} EGP</span></td>
                        <td>
                            <span class="badge {{ $school->students_count >= $school->student_limit ? 'badge-danger' : 'badge-info' }}">
                                {{ $school->students_count }} / {{ $school->student_limit }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.schools.show', $school->id) }}" class="btn btn-sm btn-ghost" title="View Details"><i class='bx bx-show' style="color: var(--primary);"></i></a>
                            <a href="{{ route('admin.schools.edit', $school->id) }}" class="btn btn-sm btn-ghost" title="Edit"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            <form action="{{ route('admin.schools.destroy', $school->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this school and all its data?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="border:none; background:transparent;" title="Delete"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">
            {{ $approvedSchools->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-building-house'></i>
            <h3>No approved schools yet</h3>
            <p>Once schools register and are approved by you, they will Appear here.</p>
        </div>
    @endif
</div>
@endsection
