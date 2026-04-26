@extends('layouts.app')

@section('title', 'Challenge Room')
@section('page-title', 'Live Challenges')

@section('topbar-actions')
    <a href="{{ route($prefix . '.challenges.index') }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> Leave Room</a>
@endsection

@section('content')
<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 30px;">
    
    <!-- Room Information -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <i class='bx bxs-institution' style="font-size: 60px; color: var(--primary); margin-bottom: 15px;"></i>
            <h2 style="font-size: 24px; color: var(--text); margin-bottom: 10px;">{{ $challenge->title ?? 'Challenge' }}</h2>
            @if($challenge->description)
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px; line-height: 1.5;">{{ $challenge->description }}</p>
            @endif
            
            <h3 style="font-size: 16px; color: var(--text-muted); margin-bottom: 15px;">Join Code</h3>
            
            <div style="font-size: 42px; font-weight: 900; letter-spacing: 5px; color: var(--accent); background: rgba(16,185,129,0.1); padding: 20px; border-radius: 15px; margin-bottom: 20px; border: 2px dashed rgba(16,185,129,0.3); font-family: monospace;">
                {{ $challenge->code }}
            </div>

            <div style="text-align: left; margin-bottom: 20px; font-size: 14px; background: var(--bg2); padding: 15px; border-radius: 10px;">
                <div style="margin-bottom: 8px;"><i class='bx bx-book-bookmark' style="color:var(--primary);"></i> Topic: <strong>{{ $challenge->topic }}</strong></div>
                <div style="margin-bottom: 8px;"><i class='bx bx-list-ol' style="color:var(--primary);"></i> Questions: <strong>{{ $challenge->questions_count }}</strong></div>
                <div><i class='bx bx-category' style="color:var(--primary);"></i> Type: <strong>{{ $challenge->question_type }}</strong></div>
            </div>
            
            <div style="font-size: 14px; color: var(--text-muted); display:flex; justify-content:space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <span>Creator: <strong>{{ $challenge->creator->name ?? 'Unknown' }}</strong></span>
                <span>Participants: <strong>{{ $challenge->participants->count() }}</strong></span>
            </div>
        </div>

        @if(auth()->user()->isUser() && ! $challenge->participants()->where('user_id', auth()->user()->id)->exists())
        <div class="card" style="background: linear-gradient(135deg, var(--accent), #059669); color: white; border: none; text-align: center; padding: 25px;">
            <h3 style="margin-bottom: 15px;">Join the competition now!</h3>
            <form action="{{ route('user.challenges.join') }}" method="POST">
                @csrf
                <input type="hidden" name="code" value="{{ $challenge->code }}">
                <button type="submit" class="btn btn-light" style="width: 100%; padding: 12px; font-weight: 800; border-radius: 10px;">Enroll in Challenge <i class='bx bx-check-double'></i></button>
            </form>
        </div>
        @endif

        @if(auth()->user()->isUser() && $challenge->participants()->where('user_id', auth()->user()->id)->exists() && $challenge->quiz_id && $challenge->status === 'open')
        <div class="card" style="background: linear-gradient(135deg, var(--primary), #3b82f6); color: white; border: none; text-align: center; padding: 25px;">
            <h3 style="margin-bottom: 5px;">🔥 The Challenge is Open!</h3>
            <p style="margin-bottom: 20px; font-size: 14px; opacity: 0.9;">The first person to finish closes the challenge for everyone!</p>
            <a href="{{ route('user.challenges.take', $challenge->id) }}" class="btn btn-light" style="width: 100%; padding: 12px; font-weight: 800; border-radius: 10px; display: inline-block; text-decoration: none; box-shadow: 0 5px 15px rgba(255,255,255,0.2);">Enter Challenge Arena <i class='bx bx-bolt-circle'></i></a>
        </div>
        @elseif($challenge->status === 'completed')
        <div class="card" style="background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.2); text-align: center; padding: 20px;">
            <h3 style="color: #ef4444; margin: 0;"><i class='bx bx-lock-alt'></i> This Challenge is Closed</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 5px;">The competition has ended, you can review the results below.</p>
        </div>
        @endif
    </div>

    <!-- Leaderboard -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class='bx bxs-bar-chart-alt-2' style="color:var(--primary);"></i> Live Leaderboard</h2>
        </div>
        
        @if($challenge->participants->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($challenge->participants->sortByDesc('score') as $index => $participant)
                <div style="display:flex; align-items:center; padding: 15px 20px; border-radius: 12px; background: {{ $index === 0 ? 'rgba(251, 191, 36, 0.1)' : 'var(--bg2)' }}; border: 1px solid {{ $index === 0 ? 'rgba(251, 191, 36, 0.3)' : 'rgba(255,255,255,0.05)' }};">
                    
                    <div style="width: 40px; font-weight: 900; font-size: 18px; color: {{ $index === 0 ? '#fbbf24' : 'var(--text-muted)' }};">
                        {{ $index === 0 ? '🏆' : '#'.($index + 1) }}
                    </div>
                    
                    <div style="flex: 1; font-weight: 700; font-size: 16px;">
                        {{ $participant->user->name ?? 'Hidden Student' }}
                    </div>
                    
                    <div style="font-size: 18px; font-weight: 900; color: var(--accent);">
                        {{ (int)$participant->score }} Pts
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 40px 20px;">
                <i class='bx bx-user-circle' style="color:var(--text-muted); opacity: 0.3;"></i>
                <h3>No Competitors Yet</h3>
                <p>Share the code displayed on the left to allow students to join the room and compete.</p>
            </div>
        @endif
    </div>
</div>
@endsection
