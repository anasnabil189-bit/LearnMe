@extends('layouts.app')

@section('title', 'Edit Student Data')
@section('page-title', 'Edit Student Data: ' . ($student->user->name ?? ''))

@section('topbar-actions')
    <a href="{{ route('admin.students.index') }}" class="btn btn-ghost">
        <i class='bx bx-arrow-back'></i> Back to List
    </a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Edit Student Data</h2>
    </div>

    <form action="{{ route('admin.students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Student Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $student->user->name) }}" required>
            @error('name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email', $student->user->email) }}" required style="font-family: monospace;" dir="ltr">
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class='bx bx-save'></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
