@extends('layouts.app')

@section('title', 'Quiz Content')
@section('page-title', $quiz->title)

@section('topbar-actions')
    <div style="display:flex; gap:10px;">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('aiGenerateModal').style.display='flex';" style="background: linear-gradient(135deg, #a855f7, #6366f1); border:none;">
            <i class='bx bxs-magic-wand'></i> Generate AI Questions
        </button>
        <a href="{{ route($prefix . '.quizzes.index') }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> Back to Bank</a>
    </div>
@endsection

@section('content')
<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 30px;">
    <!-- Questions -->
    <div style="display:flex; flex-direction:column; gap:20px;">
        @if($quiz->questions->count() > 0)
            @foreach($quiz->questions as $index => $question)
            <div class="card" style="padding: 20px; border-radius: 12px; border: 2px solid rgba(255,255,255,0.05); position: relative;">
                <form action="{{ route($prefix . '.questions.destroy', $question->id) }}" method="POST" style="position: absolute; left: 20px; top: 20px;" onsubmit="return confirm('Delete question permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background:var(--danger); color:white; padding:5px; border-radius:5px;"><i class='bx bx-trash'></i></button>
                </form>
                
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <span class="badge" style="background:var(--primary); color:white;">{{ strtoupper(str_replace('_', ' ', $question->type)) }}</span>
                    <span class="badge" style="background:var(--accent); color:white;">{{ $question->points ?? 1 }} {{ $question->points > 1 ? 'Degrees' : 'Degree' }}</span>
                    <h3 style="font-size:18px; color:var(--text); margin:0;">Question {{ $index + 1 }}: <span style="font-weight:500;">{{ $question->question }}</span></h3>
                </div>
                
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @if($question->type === 'multiple_choice' || $question->type === 'true_false')
                        @foreach($question->answers as $answer)
                            <div style="padding: 10px 15px; border-radius: 8px; background: {{ $answer->is_correct ? 'rgba(16,185,129,0.1)' : 'var(--bg)' }}; border: 1px solid {{ $answer->is_correct ? 'var(--accent)' : 'rgba(255,255,255,0.05)' }}; display:flex; align-items:center; gap:10px;">
                                <i class='bx {{ $answer->is_correct ? "bxs-check-circle" : "bx-circle" }}' style="color: {{ $answer->is_correct ? 'var(--accent)' : 'var(--text-muted)' }}; font-size: 20px;"></i>
                                <span style="color: {{ $answer->is_correct ? 'var(--accent)' : 'var(--text-muted)' }}">{{ $answer->answer }}</span>
                            </div>
                        @endforeach
                    @elseif($question->type === 'matching')
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                            @foreach($question->answers as $answer)
                                @php [$term, $def] = explode('|||', $answer->answer); @endphp
                                <div style="padding:10px; background:var(--bg2); border-radius:8px; border:1px solid var(--border); text-align:center;">{{ $term }}</div>
                                <div style="padding:10px; background:rgba(16,185,129,0.1); border-radius:8px; border:1px solid var(--accent); text-align:center;">{{ $def }}</div>
                            @endforeach
                        </div>
                    @elseif($question->type === 'essay')
                        <div style="padding:15px; background:var(--bg2); border-radius:8px; border:1px dashed var(--border); color:var(--text-muted); font-style:italic;">
                            Essay Question: Free response from the student.
                            @if($question->answers->count() > 0)
                                <div style="margin-top:10px; color:var(--text); font-style:normal;"><strong>Model Answer:</strong> {{ $question->answers->first()->answer }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state" style="padding:60px 20px;">
                <i class='bx bxs-inbox' style="font-size:60px; color:var(--text-muted); opacity:0.3; margin-bottom:15px;"></i>
                <h3>No questions yet</h3>
                <p>Add questions to make them available to students.</p>
            </div>
        @endif
    </div>

    <!-- Form for adding a new question -->
    <div>
        <div class="card" style="position: sticky; top: 100px;">
            <div class="card-header" style="border-bottom:none; margin-bottom:0; padding-bottom:10px;">
                <h3 class="card-title" style="font-size:18px;"><i class='bx bx-plus-circle' style="color:var(--primary);"></i> Add New Question</h3>
            </div>
            
            <form action="{{ route($prefix . '.questions.store', $quiz->id) }}" method="POST" id="manualQuestionForm">
                @csrf
                
                @if ($errors->any())
                    <div style="margin-bottom: 15px; padding: 15px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                        <ul style="margin: 0; padding-right: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Question Type</label>
                    <select name="type" id="questionTypeSelector" style="width:100%;">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True or False</option>
                        <option value="matching">Matching</option>
                        <option value="essay">Essay Question</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Question Points *</label>
                    <input type="number" name="points" value="1" min="1" required style="width:100%;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Question Text *</label>
                    <textarea name="question" required rows="3" placeholder="Write the question text here..."></textarea>
                </div>
                
                <!-- Multiple Choice Container -->
                <div id="mcqContainer" class="type-container">
                    <label>Options (select the correct answer) *</label>
                    <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                        @for($i=0; $i<4; $i++)
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="radio" name="correct_answer" value="{{ $i }}" {{ $i==0 ? 'checked':'' }} style="width:20px; height:20px; accent-color:var(--accent);">
                            <input type="text" name="answers[]" placeholder="Option {{ $i+1 }} Text" style="margin:0;">
                        </div>
                        @endfor
                    </div>
                </div>

                <!-- True/False Container -->
                <div id="tfContainer" class="type-container" style="display:none;">
                    <label>Correct Answer *</label>
                    <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                        <label style="display:flex; align-items:center; gap:10px;">
                            <input type="radio" name="correct_answer" value="0" checked style="width:20px; height:20px;">
                            <span>True</span>
                            <input type="hidden" name="answers[]" value="True">
                        </label>
                        <label style="display:flex; align-items:center; gap:10px;">
                            <input type="radio" name="correct_answer" value="1" style="width:20px; height:20px;">
                            <span>False</span>
                            <input type="hidden" name="answers[]" value="False">
                        </label>
                    </div>
                </div>

                <!-- Matching Container -->
                <div id="matchingContainer" class="type-container" style="display:none;">
                    <label>Enter matching pairs (Word ||| Definition) *</label>
                    <div style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
                        @for($i=0; $i<4; $i++)
                        <div style="display:flex; gap:5px;">
                            <input type="text" name="matching_pairs[]" placeholder="Word {{ $i+1 }}" style="flex:1; margin:0;">
                            <input type="text" name="answers[]" placeholder="Definition {{ $i+1 }}" style="flex:1; margin:0;">
                        </div>
                        @endfor
                    </div>
                </div>

                <!-- Essay Container -->
                <div id="essayContainer" class="type-container" style="display:none;">
                    <label>Model Answer (Optional)</label>
                    <textarea name="model_answer" rows="3" placeholder="Write the model answer here to help you with grading later..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:20px;">Save Question</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal for AI Generation -->
<div id="aiGenerateModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
    <div class="card" style="width: 400px; padding: 30px; position:relative; box-shadow:0 20px 50px rgba(168,85,247,0.2); border:1px solid rgba(168,85,247,0.3);">
        <button type="button" onclick="document.getElementById('aiGenerateModal').style.display='none';" style="position:absolute; top:20px; left:20px; background:none; border:none; font-size:24px; color:var(--text-muted); cursor:pointer;"><i class='bx bx-x'></i></button>
        
        <h3 style="display:flex; align-items:center; gap:10px; color:#a855f7; font-size:1.4rem; margin-bottom:20px; font-weight:800;">
            <i class='bx bxs-magic-wand'></i> Smart Question Generator
        </h3>
        
        <form id="aiGenerateForm">
            <div class="form-group" style="margin-bottom:15px;">
                <label>Requested Question Type</label>
                <select id="aiType" required style="width:100%;">
                    <option value="multiple_choice" selected>Multiple Choice</option>
                    <option value="true_false">True or False</option>
                    <option value="matching">Matching</option>
                    <option value="essay">Essay Question</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label>Quiz Topic</label>
                <input type="text" id="aiTopic" required placeholder="Example: Present Simple, Newton's Laws..." style="width:100%;">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label>Difficulty Level</label>
                <select id="aiDifficulty" required style="width:100%;">
                    <option value="easy">Easy (Beginner)</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard (Advanced)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:25px;">
                <label>Number of Questions (1-10)</label>
                <input type="number" id="aiCount" required min="1" max="10" value="3" style="width:100%;">
            </div>

            <div class="form-group" style="margin-bottom:25px;">
                <label>Points per Question</label>
                <input type="number" id="aiPoints" required min="1" value="1" style="width:100%;">
            </div>
            
            <button type="submit" id="aiGenerateBtn" class="btn btn-primary" style="width:100%; background: linear-gradient(135deg, #a855f7, #6366f1); border:none; justify-content:center; height:45px;">
                <i class='bx bx-bot'></i> Start Generating
            </button>
        </form>
        
        <div id="aiLoadingIndicator" style="display:none; text-align:center; padding:20px 0;">
            <i class='bx bx-loader-alt bx-spin' style="font-size:40px; color:#a855f7;"></i>
            <p style="margin-top:15px; color:var(--text-muted); font-weight:500;">Crafting questions accurately... this may take a few seconds</p>
        </div>
        <div id="aiErrorMsg" style="display:none; margin-top:15px; color:var(--danger); font-size:0.9rem; text-align:center;"></div>
    </div>
</div>

<script>
// Manual question form logic
document.getElementById('questionTypeSelector').addEventListener('change', function() {
    const type = this.value;
    document.querySelectorAll('.type-container').forEach(c => {
        c.style.display = 'none';
        c.querySelectorAll('input, select, textarea').forEach(i => i.disabled = true);
    });
    
    // reset requirements logic (simplified)
    document.querySelectorAll('.type-container input').forEach(i => i.required = false);

    let activeContainerId = '';
    if (type === 'multiple_choice') {
        activeContainerId = 'mcqContainer';
        document.querySelectorAll('#mcqContainer input[type="text"]').forEach(i => i.required = true);
    } else if (type === 'true_false') {
        activeContainerId = 'tfContainer';
    } else if (type === 'matching') {
        activeContainerId = 'matchingContainer';
        document.querySelectorAll('#matchingContainer input').forEach(i => i.required = true);
    } else if (type === 'essay') {
        activeContainerId = 'essayContainer';
    }

    if (activeContainerId) {
        let activeContainer = document.getElementById(activeContainerId);
        activeContainer.style.display = 'block';
        activeContainer.querySelectorAll('input, select, textarea').forEach(i => i.disabled = false);
    }
});

// Trigger change event on page load to initialize the form correctly
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('questionTypeSelector').dispatchEvent(new Event('change'));
});

// AI form logic
document.getElementById('aiGenerateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('aiGenerateBtn');
    const loading = document.getElementById('aiLoadingIndicator');
    const errorMsg = document.getElementById('aiErrorMsg');
    const form = this;
    
    form.style.display = 'none';
    loading.style.display = 'block';
    errorMsg.style.display = 'none';
    
    try {
        const response = await fetch("{{ route($prefix . '.quizzes.generate-ai', $quiz->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                topic: document.getElementById('aiTopic').value,
                difficulty: document.getElementById('aiDifficulty').value,
                count: document.getElementById('aiCount').value,
                type: document.getElementById('aiType').value,
                points: document.getElementById('aiPoints').value
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            window.location.reload();
        } else {
            throw new Error(data.error || 'Addition failed. Please try again.');
        }
    } catch(err) {
        form.style.display = 'block';
        loading.style.display = 'none';
        errorMsg.innerText = err.message;
        errorMsg.style.display = 'block';
    }
});
</script>
@endsection
