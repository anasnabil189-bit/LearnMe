@extends('layouts.app')

@section('title', 'Manage Organizations')
@section('header', 'Organizations (B2B)')

@section('content')
<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <h4>All Organizations</h4>
        <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">
            <i class='bx bx-plus'></i> New Organization
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Discount (%)</th>
                        <th>Users</th>
                        <th>Max Users</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $org)
                    <tr>
                        <td>{{ $org->id }}</td>
                        <td>{{ $org->name }}</td>
                        <td class="text-capitalize">{{ $org->type }}</td>
                        <td>{{ $org->discount_percentage }}%</td>
                        <td>{{ $org->users_count }}</td>
                        <td>{{ $org->max_users ?? 'Unlimited' }}</td>
                        <td style="display: flex; gap: 5px; align-items: center;">
                            <a href="{{ route('admin.organizations.show', $org->id) }}" class="btn btn-sm btn-primary shadow-sm" style="font-weight: bold; padding: 6px 12px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border: none;">
                                <i class='bx bx-show'></i> Details
                            </a>
                            <form action="{{ route('admin.organizations.destroy', $org->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger shadow-sm" style="padding: 6px 10px;"><i class='bx bx-trash'></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No organizations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
