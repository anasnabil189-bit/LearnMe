@extends('layouts.app')

@section('title', 'Edit Teacher')
@section('page-title', 'Teacher Management')

@section('topbar-actions')
    <a href="{{ route(auth()->user()->type . '.teachers.index') }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> Back</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Edit Teacher Details: {{ $teacher->user->name ?? 'User not found' }}</h2>
    </div>

    <form method="POST" action="{{ route(auth()->user()->type . '.teachers.update', $teacher->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $teacher->user->name ?? '') }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="email">Access Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $teacher->user->email ?? '') }}" required style="font-family: monospace;" dir="ltr">
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        @if(auth()->user()->isAdmin())
        <div class="form-group">
            <label for="school_id">Assigned School</label>
            <select id="school_id" name="school_id">
                <option value="" disabled {{ !$teacher->school_id ? 'selected' : '' }}>Register teacher to a specific school...</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id', $teacher->school_id) == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                @endforeach
            </select>
            @error('school_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        @else
        <!-- Hidden input to preserve the school ID if edited by School Admin -->
        <input type="hidden" name="school_id" value="{{ auth()->user()->school->id }}">
        @endif

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            Update Record <i class='bx bx-check-circle'></i>
        </button>
    </form>
</div>
@endsection
