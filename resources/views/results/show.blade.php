@extends('layouts.app')

@section('title', 'Result Analysis | Learnme')
@section('page-title', 'Evaluation & Analysis')

@section('topbar-actions')
    <a href="{{ route($prefix . '.results.index') }}" class="btn btn-ghost ripple"><i class='bx bx-history'></i> Results History</a>
@endsection

@php
    $earned = $result->score ?? 0;
    $total  = $result->total_points ?? 0;
    $dispScore = $total > 0 ? ($earned / $total) * 100 : 0;
@endphp

@section('content')
<div class="results-workspace animate-fade-in">
    <!-- Premium Result Hero -->
    <div class="result-hero-card shadow-premium {{ $dispScore >= 80 ? 'is-excellent' : ($dispScore >= 50 ? 'is-good' : 'is-improving') }}">
        <div class="hero-inner">
            <div class="hero-main">
                <div class="status-orb animate-pulse-soft">
                    @if($dispScore >= 80)
                        <i class='bx bxs-trophy'></i>
                    @elseif($dispScore >= 50)
                        <i class='bx bxs-award'></i>
                    @else
                        <i class='bx bxs-rocket'></i>
                    @endif
                </div>
                <div class="hero-text">
                    <span class="student-pill">{{ $result->user->name ?? 'Student' }}</span>
                    <h1 class="headline">
                        @if($dispScore >= 80) Exceptional Work! @elseif($dispScore >= 50) Great Progress! @else Keep Pushing! @endif
                    </h1>
                    <p class="quiz-ref">Assessment: <strong>{{ $result->quiz->title ?? 'General Quiz' }}</strong></p>
                </div>
            </div>

            <div class="score-display">
                <div class="score-ring-container">
                    <svg class="modern-ring" viewBox="0 0 100 100">
                        <circle class="ring-track" cx="50" cy="50" r="42"></circle>
                        <circle class="ring-progress" cx="50" cy="50" r="42" style="stroke-dasharray: {{ ($dispScore / 100) * 264 }}, 264;"></circle>
                    </svg>
                    <div class="score-content">
                        <span class="points">{{ (int)$earned }}</span>
                        <span class="total">/ {{ (int)$total }}</span>
                    </div>
                </div>
                <div class="score-label">Points Earned</div>
            </div>
        </div>

        <div class="hero-footer">
            @if(is_null(auth()->user()->school_id))
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary ripple shadow-primary">Continue Your Journey <i class='bx bx-right-arrow-alt'></i></a>
            @else
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary ripple shadow-primary">Back to Classroom <i class='bx bxs-school'></i></a>
            @endif
        </div>
    </div>

    <!-- Detailed Insights -->
    <div class="analysis-grid animate-slide-up">
        <div class="section-title">
            <div class="icon-box"><i class='bx bx-list-check'></i></div>
            <div>
                <h2>Answer Breakdown</h2>
                <p>Detailed analysis of your performance across all questions.</p>
            </div>
        </div>

        <div class="questions-stack">
            @php
                $details = collect($result->details ?? []);
                $questions = $result->quiz->questions->sortBy(function($question) use ($details) {
                    $qDetail = $details->where('question_id', $question->id)->first();
                    return $qDetail ? (int)$qDetail['is_correct'] : 1;
                })->values();
            @endphp

            @foreach($questions as $index => $question)
                @php
                    $qDetail = $details->where('question_id', $question->id)->first();
                    $isCorrect = $qDetail ? $qDetail['is_correct'] : false;
                    $qType = $qDetail['type'] ?? 'multiple_choice';
                    $scorePoints = $qDetail['score'] ?? ($isCorrect ? 1 : 0);
                    $maxPoints = floatval($qDetail['max_score'] ?? 1);
                @endphp

                <div class="analysis-card {{ $isCorrect ? 'is-correct' : 'is-wrong' }} {{ $qType === 'essay' ? 'is-essay' : '' }}">
                    <div class="card-side-tag"></div>
                    <div class="card-content">
                        <div class="content-header">
                            <div class="q-meta">
                                <span class="q-index">Question {{ $index + 1 }}</span>
                                <span class="q-type">{{ strtoupper(str_replace('_', ' ', $qType)) }}</span>
                            </div>
                            <div class="q-points">
                                <span class="earned {{ $scorePoints > 0 ? 'text-primary' : 'text-danger' }}">{{ $scorePoints }}</span>
                                <span class="total">/ {{ $maxPoints }}</span>
                            </div>
                        </div>

                        <h3 class="question-text">{{ $question->question }}</h3>

                        <!-- Answer Comparison -->
                        <div class="answer-review">
                            @if($qType === 'multiple_choice' || $qType === 'true_false')
                                <div class="options-review">
                                    @foreach($question->answers as $answer)
                                        @php $isStudentChoice = $qDetail && $qDetail['student_answer'] === $answer->answer; @endphp
                                        <div class="option-row {{ $answer->is_correct ? 'correct-opt' : 'normal-opt' }} {{ $isStudentChoice ? 'student-opt' : '' }}">
                                            <div class="opt-status">
                                                @if($answer->is_correct) <i class='bx bxs-check-circle'></i> @elseif($isStudentChoice) <i class='bx bxs-x-circle'></i> @else <i class='bx bx-circle'></i> @endif
                                            </div>
                                            <div class="opt-label">{{ $answer->answer }}</div>
                                            @if($isStudentChoice) <span class="badge-pill">Your Pick</span> @endif
                                            @if($answer->is_correct) <span class="badge-pill success">Correct</span> @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($qType === 'essay')
                                <div class="essay-review">
                                    <div class="review-label">Your Response:</div>
                                    <div class="essay-box">{{ $qDetail['student_answer'] ?: 'No response provided.' }}</div>
                                </div>
                            @endif
                        </div>

                        <!-- AI Smart Explainer -->
                        @if($qDetail && (!empty($qDetail['ai_feedback']) || $qType !== 'essay'))
                            <div class="smart-insight shadow-sm">
                                <div class="insight-header">
                                    <i class='bx bxs-magic-wand'></i>
                                    <span>Smart AI Explanation</span>
                                </div>
                                <div class="insight-body ai-content">
                                    @if(!empty($qDetail['ai_feedback']))
                                        <p class="ai-result-text">{{ $qDetail['ai_feedback'] }}</p>
                                        <button type="button" class="btn btn-sm btn-insight get-ai-feedback-btn" data-question-id="{{ $question->id }}" data-attempt-id="{{ $result->id }}" data-retry="true">
                                            <i class='bx bx-refresh'></i> Need more details?
                                        </button>
                                    @else
                                        <p class="placeholder-text">Understand why this answer was correct or incorrect with AI.</p>
                                        <button type="button" class="btn btn-sm btn-insight get-ai-feedback-btn" data-question-id="{{ $question->id }}" data-attempt-id="{{ $result->id }}">
                                            <i class='bx bx-brain'></i> Explain Strategy
                                        </button>
                                    @endif
                                    
                                    <div class="ai-loading" style="display:none;">
                                        <div class="spinner-sm"></div> AI is analyzing...
                                    </div>
                                    <div class="ai-error" style="display:none;"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Result Analysis: Premium Elite Light Theme                                */
/* -------------------------------------------------------------------------- */
.results-workspace { max-width: 1000px; margin: 0 auto; padding-bottom: 60px; }

/* Hero Card */
.result-hero-card {
    background: #ffffff; border-radius: 40px; padding: 50px; 
    border: 1px solid var(--border); box-shadow: 0 20px 50px rgba(0,0,0,0.03);
    margin-bottom: 50px; overflow: hidden; position: relative;
}
.result-hero-card::before {
    content: ''; position: absolute; top:0; left:0; width:100%; height:8px;
}
.is-excellent::before { background: linear-gradient(to right, #fbbf24, #f59e0b); }
.is-good::before { background: linear-gradient(to right, var(--primary), #0d9488); }
.is-improving::before { background: linear-gradient(to right, #f87171, #ef4444); }

.hero-inner { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 40px; margin-bottom: 40px; }

.hero-main { display: flex; align-items: center; gap: 30px; }
.status-orb {
    width: 100px; height: 100px; border-radius: 30px; display: flex; align-items: center; justify-content: center;
    font-size: 4rem; position: relative; z-index: 2;
}
.is-excellent .status-orb { background: rgba(251, 191, 36, 0.1); color: #f59e0b; }
.is-good .status-orb { background: rgba(20, 184, 166, 0.1); color: var(--primary); }
.is-improving .status-orb { background: rgba(248, 113, 113, 0.1); color: #ef4444; }

.hero-text .student-pill { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--primary); letter-spacing: 1px; margin-bottom: 10px; display: block; }
.hero-text .headline { font-size: 2.5rem; font-weight: 950; color: var(--text); letter-spacing: -1.5px; }
.hero-text .quiz-ref { font-size: 1.1rem; color: var(--text-muted); font-weight: 600; margin-top: 5px; }

/* Modern Score Ring */
.score-display { text-align: center; }
.score-ring-container { position: relative; width: 160px; height: 160px; margin: 0 auto 10px; }
.modern-ring { transform: rotate(-90deg); width: 100%; height: 100%; }
.ring-track { fill: none; stroke: #f1f5f9; stroke-width: 8; }
.ring-progress { fill: none; stroke: var(--primary); stroke-width: 8; stroke-linecap: round; transition: 1s ease-out; }

.is-excellent .ring-progress { stroke: #f59e0b; }
.is-improving .ring-progress { stroke: #ef4444; }

.score-content { position: absolute; top:50%; left:50%; transform: translate(-50%, -50%); display: flex; align-items: baseline; gap: 2px; }
.score-content .points { font-size: 2.5rem; font-weight: 900; color: var(--text); }
.score-content .total { font-size: 1.2rem; font-weight: 700; color: var(--text-muted); }
.score-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); }

.hero-footer { text-align: center; border-top: 1px solid #f1f5f9; padding-top: 40px; }

/* Analysis Cards */
.section-title { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
.section-title .icon-box { width: 44px; height: 44px; border-radius: 12px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; }
.section-title h2 { font-size: 1.5rem; font-weight: 900; color: var(--text); }

.analysis-card { background: #fff; border: 1px solid var(--border); border-radius: 30px; margin-bottom: 25px; display: flex; overflow: hidden; transition: 0.3s; }
.analysis-card:hover { border-color: var(--primary); transform: translateX(5px); box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
.card-side-tag { width: 8px; flex-shrink: 0; }
.is-correct .card-side-tag { background: var(--success); }
.is-wrong .card-side-tag { background: #ef4444; }

.card-content { padding: 30px; width: 100%; }
.content-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.q-meta { display: flex; align-items: center; gap: 10px; }
.q-index { font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; background: #f8fafc; padding: 4px 10px; border-radius: 6px; }
.q-type { font-size: 10px; font-weight: 800; color: var(--primary); background: rgba(20, 184, 166, 0.05); padding: 4px 10px; border-radius: 6px; }

.q-points { font-weight: 900; font-size: 1.1rem; }
.q-points .earned { color: var(--text); }
.q-points .total { color: #cbd5e1; }

.question-text { font-size: 1.3rem; font-weight: 800; color: var(--text); line-height: 1.5; margin-bottom: 25px; }

/* Options Review */
.options-review { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
@media (max-width: 768px) { .options-review { grid-template-columns: 1fr; } }

.option-row { 
    display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 16px; 
    border: 1px solid #f1f5f9; position: relative; font-weight: 600;
}
.opt-status { font-size: 1.2rem; color: #cbd5e1; }
.correct-opt { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.correct-opt .opt-status { color: var(--success); }
.student-opt:not(.correct-opt) { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
.student-opt:not(.correct-opt) .opt-status { color: #ef4444; }

.badge-pill { font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background: var(--text-muted); color: #fff; margin-left: auto; }
.badge-pill.success { background: var(--success); }

/* essay */
.essay-box { padding: 20px; background: #f8fafc; border-radius: 16px; font-style: italic; color: var(--text); line-height: 1.6; }

/* Smart Insight */
.smart-insight { background: #fafafa; border: 1px solid #f1f5f9; border-radius: 20px; padding: 25px; margin-top: 30px; }
.insight-header { display: flex; align-items: center; gap: 10px; color: #9333ea; font-weight: 800; font-size: 0.95rem; margin-bottom: 12px; }
.insight-body .ai-result-text { line-height: 1.7; color: #4b5563; font-weight: 500; }
.btn-insight { background: #fff; border: 1px solid #e9d5ff; color: #9333ea; border-radius: 10px; font-weight: 700; padding: 8px 16px; margin-top: 15px; transition: 0.3s; }
.btn-insight:hover { background: #f5f3ff; transform: translateY(-2px); }

.placeholder-text { color: #94a3b8; font-size: 0.9rem; margin-bottom: 12px; }
.spinner-sm { width: 16px; height: 16px; border: 2px solid #e9d5ff; border-top-color: #9333ea; border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; margin-right: 8px; }

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes pulse-soft { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.03); opacity: 0.9; } }
.animate-pulse-soft { animation: pulse-soft 3s infinite ease-in-out; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const aiButtons = document.querySelectorAll('.get-ai-feedback-btn');
    
    aiButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            const attemptId = this.dataset.attemptId;
            const questionId = this.dataset.questionId;
            const isRetry = this.dataset.retry === 'true';
            const container = this.closest('.ai-content');
            const loading = container.querySelector('.ai-loading');
            const errorDiv = container.querySelector('.ai-error');
            const resultText = container.querySelector('.ai-result-text') || document.createElement('p');
            
            if (!container.querySelector('.ai-result-text')) {
                resultText.className = 'ai-result-text';
                container.prepend(resultText);
            }

            const placeholder = container.querySelector('.placeholder-text');
            
            // UI States
            this.style.display = 'none';
            if (placeholder) placeholder.style.display = 'none';
            loading.style.display = 'flex';
            errorDiv.style.display = 'none';
            
            try {
                const response = await fetch("{{ route('ai.generate-feedback') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                    body: JSON.stringify({ attempt_id: attemptId, question_id: questionId, retry: isRetry })
                });
                
                const data = await response.json();
                if (response.ok && data.feedback) {
                    loading.style.display = 'none';
                    resultText.innerHTML = data.feedback.replace(/\n/g, '<br>');
                    resultText.style.display = 'block';
                    setTimeout(() => { this.style.display = 'inline-block'; this.innerHTML = '<i class="bx bx-refresh"></i> Rethink Explanation'; }, 500);
                } else if (response.status === 403 && data.show_modal) {
                    loading.style.display = 'none';
                    this.style.display = 'inline-block';
                    if (placeholder) placeholder.style.display = 'block';
                    document.getElementById('subscriptionModal').style.display = 'flex';
                } else {
                    throw new Error(data.error || 'Connection to AI failed.');
                }
            } catch (err) {
                loading.style.display = 'none';
                errorDiv.innerText = err.message;
                errorDiv.style.display = 'block';
                this.style.display = 'inline-block';
            }
        });
    });
});
</script>
@endsection
