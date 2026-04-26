@extends('layouts.app')

@section('title', 'Add New Staff')
@section('page-title', 'Create Staff Account')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost" style="margin-bottom: 20px;">
        <i class='bx bx-arrow-back'></i> Back to List
    </a>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">New Staff Details</h2>
        </div>

        <form action="{{ route('admin.staff.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter name">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="staff@example.com">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="type">Role</label>
                <select id="type" name="type" required>
                    <option value="admin" {{ old('type') == 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="manager" {{ old('type') == 'manager' ? 'selected' : '' }}>Content Manager</option>
                </select>
                @error('type') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Choose a secure password">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password">
                </div>
            </div>
            @error('password') <div class="form-error" style="margin-top: -10px; margin-bottom: 15px;">{{ $message }}</div> @enderror

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class='bx bx-user-plus'></i> Create Staff Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
