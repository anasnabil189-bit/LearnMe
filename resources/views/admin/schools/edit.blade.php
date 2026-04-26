@extends('layouts.app')

@section('title', 'Edit School')
@section('page-title', 'Schools Management')

@section('topbar-actions')
    <a href="{{ route('admin.schools.index') }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Edit School Data: {{ $school->name }}</h2>
    </div>

    <form method="POST" action="{{ route('admin.schools.update', $school->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">School Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $school->name) }}" required placeholder="Enter full school name">
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="code">School Activation Code *</label>
            <input type="text" id="code" name="code" value="{{ old('code', $school->code) }}" required>
            @error('code')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        @if($adminUser)
        <div class="form-group">
            <label for="email">School Admin Account Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $adminUser->email) }}" required style="font-family: monospace;" dir="ltr">
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        @endif

        <div class="form-group">
            <label for="annual_subscription_fee">Annual Subscription (EGP) *</label>
            <input type="number" id="annual_subscription_fee" name="annual_subscription_fee" value="{{ old('annual_subscription_fee', $school->annual_subscription_fee) }}" required step="0.01" min="0" placeholder="Example: 5000">
            @error('annual_subscription_fee')<div class="form-error">{{ $message }}</div>@enderror
        </div>


        <div class="form-group">
            <label for="student_limit">Max Student Limit *</label>
            <input type="number" id="student_limit" name="student_limit" value="{{ old('student_limit', $school->student_limit) }}" required min="1" placeholder="Example: 500">
            @error('student_limit')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="subscription_start">Subscription Start Date</label>
                <input type="date" id="subscription_start" name="subscription_start" value="{{ old('subscription_start', $school->subscription_start) }}">
                @error('subscription_start')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="subscription_end">End Date</label>
                <input type="date" id="subscription_end" name="subscription_end" value="{{ old('subscription_end', $school->subscription_end) }}">
                @error('subscription_end')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Update Data <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>
@endsection
