@extends('layouts.app')

@section('title', 'Live Challenge Arena')

@section('content')
<div id="challenge-arena" data-challenge-id="{{ $challenge->id }}" data-status-url="{{ route('user.challenges.status', $challenge->id) }}">
    
    <!-- Header -->
    <div class="card" style="margin-bottom: 30px; position: sticky; top: 10px; z-index: 100; border: 2px solid var(--primary); box-shadow: 0 10px 30px rgba(59,130,246,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 25px;">
            <div>
                <h2 style="font-size: 20px; margin:0;"><i class='bx bx-trophy' style="color:var(--primary);"></i> {{ $challenge->title }}</h2>
                <span style="font-size: 13px; color: var(--text-muted);">Topic: {{ $challenge->topic }}</span>
            </div>
            <div id="challenge-status-badge" style="background: rgba(16,185,129,0.1); color: #10b981; padding: 5px 15px; border-radius: 20px; font-weight: 800; font-size: 14px;">
                <i class='bx bx-loader-alt bx-spin'></i> Challenge in progress...
            </div>
            <div>
                <button type="button" onclick="submitChallenge()" class="btn btn-primary" id="submit-btn" style="padding: 10px 30px; font-weight: 800;">
                    End Challenge for Everyone <i class='bx bx-power-off'></i>
                </button>
            </div>
        </div>
    </div>

    <form id="challenge-form">
        @csrf
        <div style="display: flex; flex-direction: column; gap: 25px;">
            @foreach($quiz->questions as $index => $question)
            <div class="card question-card" id="q-{{ $question->id }}">
                <div style="margin-bottom: 20px; display:flex; gap: 15px; align-items: flex-start;">
                    <span style="background: var(--primary); color: white; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-weight: 900; flex-shrink: 0;">{{ $index + 1 }}</span>
                    <h3 style="font-size: 18px; line-height: 1.6; margin: 0;">{{ $question->question }}</h3>
                </div>

                @if($question->type === 'multiple_choice' || $question->type === 'true_false')
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        @foreach($question->answers as $answer)
                        <label class="answer-option" style="cursor: pointer; position: relative;">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" style="display: none;">
                            <div class="option-box" style="padding: 15px 20px; border: 2px solid rgba(255,255,255,0.05); border-radius: 12px; transition: all 0.2s;">
                                {{ $answer->answer }}
                            </div>
                        </label>
                        @endforeach
                    </div>

                @elseif($question->type === 'matching')
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        @php
                            $shuffledDefs = $question->answers->pluck('answer')->map(fn($a) => explode('|||', $a)[1])->shuffle();
                        @endphp
                        @foreach($question->answers as $answer)
                            @php [$term, $def] = explode('|||', $answer->answer); @endphp
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: center;">
                                <div style="background: var(--bg2); padding: 12px 20px; border-radius: 10px; border-right: 4px solid var(--primary);">{{ $term }}</div>
                                <select name="matching[{{ $question->id }}][{{ $answer->id }}]" class="form-control">
                                    <option value="">Select the matching answer...</option>
                                    @foreach($shuffledDefs as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                @elseif($question->type === 'essay')
                    <textarea name="essay[{{ $question->id }}]" class="form-control" rows="4" placeholder="Write your answer clearly here..." style="width: 100%; font-size: 16px; padding: 15px;"></textarea>
                @endif
            </div>
            @endforeach
        </div>

        <div style="text-align: center; margin-top: 40px; padding-bottom: 40px;">
            <button type="button" onclick="submitChallenge()" class="btn btn-primary" style="font-size: 22px; padding: 15px 60px; border-radius: 20px; box-shadow: 0 15px 40px rgba(59,130,246,0.4);">
                Submit Challenge & Show Results <i class='bx bx-check-double'></i>
            </button>
        </div>
    </form>
</div>

<style>
    .answer-option input:checked + .option-box {
        background: rgba(59, 130, 246, 0.1);
        border-color: var(--primary);
        color: var(--primary);
        font-weight: 800;
    }
    .question-card {
        transition: transform 0.3s ease;
    }
    .question-card:hover {
        transform: translateY(-5px);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const challengeId = "{{ $challenge->id }}";
    const statusUrl = "{{ route('user.challenges.status', $challenge->id) }}";
    const submitUrl = "{{ route('user.challenges.submit', $challenge->id) }}";
    let isSubmitting = false;

    // Polling function
    const checkStatus = setInterval(async () => {
        if (isSubmitting) return;

        try {
            const response = await fetch(statusUrl);
            const data = await response.json();

            if (data.status === 'completed') {
                clearInterval(checkStatus);
                Swal.fire({
                    title: 'Challenge Ended!',
                    text: 'Someone has completed the challenge. Your current answers will be submitted automatically.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then(() => {
                    submitChallenge(true); // Forced submission
                });
            }
        } catch (error) {
            console.error('Status check failed', error);
        }
    }, 5000);

    async function submitChallenge(forced = false) {
        if (isSubmitting) return;
        
        if (!forced) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "By submitting, the challenge will be closed for everyone else as well!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel'
            });
            if (!result.isConfirmed) return;
        }

        isSubmitting = true;
        clearInterval(checkStatus);

        Swal.fire({
            title: 'Submitting and Grading...',
            text: 'Please wait a moment while AI grades your answers.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(document.getElementById('challenge-form'));
        
        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (data.status === 'success' || data.status === 'already_completed') {
                Swal.fire({
                    title: 'Done!',
                    text: data.message || 'Your results have been recorded successfully.',
                    icon: 'success',
                }).then(() => {
                    window.location.href = data.redirect;
                });
            } else {
                Swal.fire('Error', 'An error occurred while submitting. Try again.', 'error');
                isSubmitting = false;
            }
        } catch (error) {
            console.error('Submission failed', error);
            Swal.fire('Error', 'Connection to server failed.', 'error');
            isSubmitting = false;
        }
    }
</script>
@endsection
