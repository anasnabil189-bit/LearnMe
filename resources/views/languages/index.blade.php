@extends('layouts.app')

@section('title', 'Select Language')
@section('page-title', 'Select Language')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="page-header">
        <div>
            <h1>Available Languages</h1>
            <p>Choose a language you want to learn. You can enroll in multiple languages and track progress for each.</p>
        </div>
    </div>

    <div class="stats-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
        @foreach($languages as $lang)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--primary-light);">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div class="logo-icon" style="background: var(--bg3); width: 48px; height: 48px; font-size: 24px;">
                            <i class='bx bx-world'></i>
                        </div>
                        <h2 style="font-size: 20px; font-weight: 700;">{{ $lang->name }}</h2>
                    </div>
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                        Enroll now to start learning {{ $lang->name }}. You will have separate XP and levels for this language.
                    </p>
                </div>

                @if(in_array($lang->id, $userLanguages))
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge badge-success">
                            <i class='bx bx-check'></i> Enrolled
                        </span>
                        <form action="{{ route('user.languages.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="language_id" value="{{ $lang->id }}">
                            <button type="submit" class="btn btn-ghost btn-sm">Switch to {{ $lang->name }}</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('user.languages.enroll') }}" method="POST">
                        @csrf
                        <input type="hidden" name="language_id" value="{{ $lang->id }}">
                        <button type="submit" class="btn btn-primary w-full" style="width: 100%;">
                            Enroll and Start Learning
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
