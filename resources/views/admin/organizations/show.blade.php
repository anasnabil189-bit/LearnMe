@extends('layouts.app')

@section('title', 'View Organization')
@section('header', 'Organization Details')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">{{ $organization->name }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Type:</strong> <span class="text-capitalize">{{ $organization->type }}</span></p>
                <p><strong>Allowed Domains:</strong> <span class="badge bg-info text-dark">{{ $organization->allowed_domains ?? 'Open (All Domains)' }}</span></p>
                <p><strong>Discount:</strong> {{ $organization->discount_percentage }}%</p>
                <p><strong>Users:</strong> {{ $organization->users->count() }} / {{ $organization->max_users ?? 'Unlimited' }}</p>
                <p><strong>Average Target XP:</strong> {{ $averageXp }}</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Generate Invite Code</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.organizations.generate-code', $organization->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Usage Limit (leave empty for unlimited)</label>
                        <input type="number" name="usage_limit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Expires At (optional)</label>
                        <input type="date" name="expires_at" class="form-control text-dark">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Generate Code</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header border-bottom-0">
                <h5 class="mb-0">Invite Codes</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Used</th>
                            <th>Limit</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organization->codes as $code)
                        <tr>
                            <td><strong class="text-primary">{{ $code->code }}</strong></td>
                            <td>{{ $code->used_count }}</td>
                            <td>{{ $code->usage_limit ?? '∞' }}</td>
                            <td>{{ $code->expires_at ? $code->expires_at->format('Y-m-d') : 'Never' }}</td>
                            <td>
                                @if($code->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td style="display: flex; gap: 5px;">
                                <form action="{{ route('admin.organizations.toggle-code-status', $code->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $code->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" style="min-width: 90px;">
                                        {{ $code->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.organizations.destroy-code', $code->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this code? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No active codes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header border-bottom-0">
                <h5 class="mb-0">Top Performers</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>XP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPerformers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->pivot->role }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $user->learning_xp }} XP</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No users have joined yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
