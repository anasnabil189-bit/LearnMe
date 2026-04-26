@extends('layouts.app')

@section('title', 'School Management')
@section('page-title', 'Schools')

@section('topbar-actions')
    <a href="{{ route('schools.create') }}" class="btn btn-primary"><i class='bx bx-plus'></i> Add School</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Registered Schools List</h2>
    </div>

    @if(isset($schools) && count($schools) > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>School Name</th>
                        <th>Activation Code</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schools as $school)
                    <tr>
                        <td>{{ $school->id }}</td>
                        <td>{{ $school->name }}</td>
                        <td><span class="badge badge-primary">{{ $school->code }}</span></td>
                        <td>{{ $school->subscription_start }}</td>
                        <td>{{ $school->subscription_end }}</td>
                        <td>
                            <a href="{{ route('schools.edit', $school->id) }}" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px;"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            <form action="{{ route('schools.destroy', $school->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this school?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px; border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-building-house'></i>
            <h3>No schools yet</h3>
            <p>Click "Add School" to add the first school to the system</p>
        </div>
    @endif
</div>
@endsection
