@extends('layouts.app')

@section('title', 'Staff Management')
@section('page-title', 'Platform Staff (Admins & Managers)')

@section('topbar-actions')
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class='bx bx-plus'></i> Add New Staff</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Staff List</h2>
    </div>

    @if(isset($admins) && count($admins) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td>{{ $admin->id }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 13px;">{{ mb_substr($admin->name, 0, 1) }}</div>
                                <strong>{{ $admin->name }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($admin->type === 'admin')
                                <span class="badge badge-primary">Admin</span>
                            @else
                                <span class="badge badge-accent">Manager</span>
                            @endif
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->created_at->format('Y-m-d') }}</td>
                        <td style="text-align: center;">
                            @if(auth()->id() !== $admin->id)
                            <form action="{{ route('admin.staff.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this staff account?');" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="border:none; background:transparent;" title="Remove Account">
                                    <i class='bx bx-trash' style="color: var(--danger);"></i>
                                </button>
                            </form>
                            @else
                                <span class="badge badge-info">(You)</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">
            {{ $admins->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-user-shield'></i>
            <h3>No other staff yet</h3>
        </div>
    @endif
</div>
@endsection
