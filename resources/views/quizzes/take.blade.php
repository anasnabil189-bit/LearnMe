@extends('layouts.app')

@section('title', 'Take Quiz')
@section('page-title', 'Knowledge Challenge!')

@section('topbar-actions')
    <div style="font-weight: 700; color: var(--text-muted); padding: 8px 15px; border-radius: 8px; background: rgba(255,255,255,0.05);">
        Total Questions: {{ $quiz->questions->count() }}
    </div>
@endsection

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding-bottom: 100px;">
    
    <div style="text-align:center; margin-bottom:50px;">
        <h1 style="font-size:36px; color:var(--primary); font-weight:900; margin-bottom:10px;">{{ $quiz->title }}</h1>
        <p style="color:var(--text-muted); font-size:18px;">Potential Reward: <span style="color:var(--accent); font-weight:700;"><i class='bx bxs-diamond'></i> Experience points for each correct answer!</span></p>
    </div>

    @if($quiz->questions->count() > 0)
        <form action="{{ route('user.quizzes.submit', $quiz->id) }}" method="POST">
            @csrf
            
            <div style="display:flex; flex-direction:column; gap:40px;">
                @foreach($quiz->questions as $i => $question)
                <div class="card" style="box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 20px; overflow:hidden;">
                    <div style="background: rgba(14,165,233,0.05); padding: 30px; border-bottom: 1px solid rgba(255,255,255,0.02);">
                        <h3 style="font-size:24px; color:var(--text); line-height:1.5; display: flex; align-items: center; gap: 10px;">
                            <span style="color:var(--primary); font-weight:900; opacity:0.5; font-size:18px;">{{ sprintf("%02d", $i+1) }}</span>
                            <span style="flex: 1;">{{ $question->question }}</span>
                            <span class="badge" style="background: var(--accent); color: white; font-size: 14px; padding: 5px 10px; border-radius: 6px;">{{ $question->points ?? 1 }} {{ ($question->points ?? 1) > 1 ? 'Degrees' : 'Degree' }}</span>
                        </h3>
                    </div>
                    
                    <div style="padding: 30px; display:flex; flex-direction:column; gap:15px;">
                        @if($question->type === 'multiple_choice' || $question->type === 'true_false')
                            @foreach($question->answers as $answer)
                                <label style="display:flex; align-items:center; gap:15px; padding:15px 20px; background:var(--bg); border:2px solid rgba(255,255,255,0.05); border-radius:12px; cursor:pointer; transition:0.3s;" class="quiz-option">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" required style="width:20px; height:20px; accent-color:var(--primary);">
                                    <span style="font-size:18px;">{{ $answer->answer }}</span>
                                </label>
                            @endforeach
                        @elseif($question->type === 'matching')
                            @php
                                $definitions = $question->answers->map(function($a) {
                                    return explode('|||', $a->answer)[1];
                                })->shuffle();
                            @endphp
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:10px;">Match the word with the correct definition:</p>
                                @foreach($question->answers as $ans)
                                    @php $term = explode('|||', $ans->answer)[0]; @endphp
                                    <div style="display:flex; align-items:center; gap:15px; background:var(--bg2); padding:10px 15px; border-radius:10px; border:1px solid var(--border);">
                                        <span style="flex:1; font-weight:700;">{{ $term }}</span>
                                        <select name="matching[{{ $question->id }}][{{ $ans->id }}]" required style="flex:2; padding:8px; border-radius:8px; background:var(--bg); border:1px solid var(--border); color:var(--text);">
                                            <option value="">Choose the correct definition...</option>
                                            @foreach($definitions as $def)
                                                <option value="{{ $def }}">{{ $def }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($question->type === 'essay')
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <label style="color:var(--text-muted);">Write your detailed answer below:</label>
                                <textarea name="essay[{{ $question->id }}]" required rows="6" placeholder="Start writing here..." style="width:100%; padding:20px; border-radius:15px; background:var(--bg); border:2px solid var(--border); color:var(--text); font-size:18px; line-height:1.6;"></textarea>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Submit button -->
            <div style="margin-top: 50px; text-align: center; border-top: 1px solid var(--border); padding-top: 30px;">
                <button type="submit" class="btn btn-primary" style="padding: 18px 80px; font-size:22px; box-shadow:0 12px 35px rgba(14,165,233,0.35); border-radius:18px;" onclick="return confirm('Are you sure you want to submit your answers and finish the quiz?');">
                    Submit Answers & Finish Quiz <i class='bx bx-check-double'></i>
                </button>
            </div>
        </form>
    @else
        <div class="empty-state">
            <i class='bx bx-error' style="color:var(--danger)"></i>
            <h3>Content Not Available</h3>
            <p>No questions have been added to this quiz yet. Please contact your teacher regarding this.</p>
            <a href="{{ route('user.dashboard') }}" class="btn btn-primary" style="margin-top:20px;">Back to Dashboard</a>
        </div>
    @endif
</div>

<style>
    .quiz-option:hover { border-color: var(--primary) !important; background: rgba(14,165,233,0.05) !important; }
</style>
@endsection
