@extends('layouts.app')

@section('title', 'Add Teacher')
@section('page-title', 'Teacher Management')

@section('topbar-actions')
    <a href="{{ route(auth()->user()->type . '.teachers.index') }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">New Teacher Details</h2>
    </div>

    <form method="POST" action="{{ route(auth()->user()->type . '.teachers.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="email">Access Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        @if(auth()->user()->isAdmin())
        <div class="form-group">
            <label for="school_id">Assigned School</label>
            <select id="school_id" name="school_id">
                <option value="" disabled selected>Register teacher to a specific school...</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
            @error('school_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        @endif

        <div class="form-grid">
            <div class="form-group">
                <label for="password">Password (Temporary) *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
            @error('password')<div class="form-error" style="grid-column: 1 / -1; margin-top: -10px; margin-bottom: 10px;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Save & Create Record <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>
@endsection
