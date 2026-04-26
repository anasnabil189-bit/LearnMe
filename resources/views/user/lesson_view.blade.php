@extends('layouts.app')

@section('content')
<div class="lesson-workspace animate-fade-in">
    <div class="workspace-container">
        <!-- Modern Header Navigation -->
        <div class="workspace-header">
            <a href="{{ route('user.dashboard') }}" class="back-link ripple">
                <i class='bx bx-arrow-back'></i>
                <span>Back to Learning Map</span>
            </a>
            <div class="lesson-context-pill">
                <span class="l-name">{{ $lesson->level->name }}</span>
                <span class="divider"></span>
                <span class="l-order">Lesson {{ $lesson->order }}</span>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="lesson-main-card">
            <!-- Learning Media Area -->
            @if($lesson->video_url)
            <div class="video-container shadow-premium">
                <iframe 
                    src="{{ str_replace('watch?v=', 'embed/', $lesson->video_url) }}" 
                    title="Lesson Video" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            </div>
            @else
            <div class="no-video-placeholder">
                <div class="icon-orb"><i class='bx bxs-file-doc'></i></div>
                <p>Curated Text & Visual Guide</p>
            </div>
            @endif

            <div class="lesson-content-body">
                <h1 class="lesson-headline">{{ $lesson->title }}</h1>
                
                <!-- Educational Content -->
                <div class="content-rich-text">
                    {!! nl2br(e($lesson->content)) !!}
                    
                    @foreach($lesson->blocks as $block)
                        @if($block->type === 'text')
                            <div class="text-block">
                                {!! nl2br(e($block->content)) !!}
                            </div>
                        @elseif($block->type === 'image')
                            <div class="image-block shadow-sm">
                                <img src="{{ Storage::url($block->path) }}" alt="Lesson Visual">
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Interactive Assessment Area -->
                @if($lesson->quiz)
                <div class="assessment-area">
                    <div class="assessment-header">
                        <span class="badge-accent shadow-accent">Knowledge Check</span>
                        <h2>Ready for the final step?</h2>
                        <p>Complete the assessment below to certify your mastery of this lesson.</p>
                    </div>

                    <form action="{{ route('courses.lesson.submit', $lesson->id) }}" method="POST" class="quiz-form">
                        @csrf
                        @foreach($lesson->quiz->questions as $index => $question)
                        <div class="question-unit animate-slide-up" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="q-header">
                                <span class="q-number">{{ $index + 1 }}</span>
                                <h3>{{ $question->question }}</h3>
                            </div>
                            <div class="answer-grid">
                                @foreach($question->answers as $answer)
                                <label class="answer-option">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" required>
                                    <div class="option-box">
                                        <div class="selection-indicator"></div>
                                        <span>{{ $answer->answer }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="submit-action-area">
                            <button type="submit" class="btn btn-primary btn-lg ripple shadow-glow">
                                Complete Lesson & Continue <i class='bx bx-check-double'></i>
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="completion-area-simple">
                    <form action="{{ route('courses.lesson.submit', $lesson->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg ripple shadow-glow">
                            Mark Lesson as Mastered <i class='bx bxs-badge-check'></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* -------------------------------------------------------------------------- */
/*  Lesson View: Elite Light UI                                               */
/* -------------------------------------------------------------------------- */
.lesson-workspace { background: #f8fafc; min-height: 100vh; padding: 40px 20px; }
.workspace-container { max-width: 900px; margin: 0 auto; }

/* Navigation Header */
.workspace-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.back-link { 
    display: flex; align-items: center; gap: 10px; color: var(--text-muted); 
    font-weight: 700; font-size: 0.95rem; text-decoration: none; padding: 10px 15px; border-radius: 12px;
}
.back-link:hover { background: #fff; color: var(--primary); }
.back-link i { font-size: 1.2rem; }

.lesson-context-pill { 
    background: #fff; border: 1px solid var(--border); padding: 8px 20px; border-radius: 50px; 
    display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 0.85rem;
}
.lesson-context-pill .l-name { color: var(--primary); }
.lesson-context-pill .divider { width: 4px; height: 4px; background: #cbd5e1; border-radius: 50%; }
.lesson-context-pill .l-order { color: var(--text-muted); }

/* Main Card */
.lesson-main-card { 
    background: #fff; border-radius: 40px; border: 1px solid var(--border); 
    box-shadow: 0 15px 40px rgba(0,0,0,0.03); overflow: hidden;
}

/* Video Section */
.video-container { aspect-ratio: 16/9; background: #000; position: relative; }
.video-container iframe { width: 100%; height: 100%; }

.no-video-placeholder { 
    aspect-ratio: 21/9; background: linear-gradient(135deg, #f1f5f9, #e2e8f0); 
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px;
}
.icon-orb { width: 64px; height: 64px; background: #fff; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.no-video-placeholder p { font-weight: 800; color: var(--text-muted); font-size: 1rem; }

/* Content Body */
.lesson-content-body { padding: 50px; }
.lesson-headline { font-size: 2.8rem; font-weight: 950; color: var(--text); letter-spacing: -1.5px; margin-bottom: 30px; }

.content-rich-text { font-size: 1.15rem; color: #475569; line-height: 1.8; font-weight: 500; }
.text-block { margin: 30px 0; }
.image-block { margin: 40px 0; border-radius: 24px; overflow: hidden; border: 1px solid var(--border); }
.image-block img { width: 100%; display: block; }

/* Assessment System */
.assessment-area { border-top: 1px solid var(--border); margin-top: 60px; padding-top: 60px; }
.assessment-header { text-align: center; margin-bottom: 50px; }
.badge-accent { 
    display: inline-block; background: var(--accent); color: #fff; padding: 6px 18px; 
    border-radius: 50px; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;
}
.assessment-header h2 { font-size: 2rem; font-weight: 900; color: var(--text); margin-bottom: 10px; }
.assessment-header p { color: var(--text-muted); font-weight: 600; font-size: 1.1rem; }

.quiz-form { display: flex; flex-direction: column; gap: 40px; }
.q-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
.q-number { 
    width: 32px; height: 32px; background: var(--primary); color: #fff; font-weight: 900; 
    display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 0.9rem;
}
.q-header h3 { font-size: 1.35rem; font-weight: 800; color: var(--text); }

.answer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 600px) { .answer-grid { grid-template-columns: 1fr; } }

.answer-option input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
.option-box { 
    background: #f8fafc; border: 2px solid #e2e8f0; padding: 20px; border-radius: 18px; 
    display: flex; align-items: center; gap: 15px; transition: 0.3s; cursor: pointer;
}
.answer-option:hover .option-box { border-color: var(--primary); background: rgba(20, 184, 166, 0.02); }

.selection-indicator { width: 18px; height: 18px; border: 2px solid #cbd5e1; border-radius: 50%; position: relative; transition: 0.3s; }
.answer-option input:checked + .option-box { border-color: var(--primary); background: rgba(20, 184, 166, 0.05); }
.answer-option input:checked + .option-box .selection-indicator { border-color: var(--primary); }
.answer-option input:checked + .option-box .selection-indicator::after { 
    content: ''; position: absolute; inset: 3px; background: var(--primary); border-radius: 50%;
}
.answer-option .option-box span { font-weight: 700; color: var(--text); font-size: 1.05rem; }

.submit-action-area { text-align: center; margin-top: 40px; padding-top: 40px; border-top: 1px solid var(--border); }
.completion-area-simple { text-align: center; margin-top: 50px; }

/* Buttons */
.btn-lg { padding: 20px 50px; font-size: 1.2rem; font-weight: 900; border-radius: 24px; }
.shadow-glow { box-shadow: 0 10px 30px rgba(20, 184, 166, 0.3); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.8s ease; }
.animate-slide-up { animation: slideUp 0.6s ease; }
</style>
@endsection
