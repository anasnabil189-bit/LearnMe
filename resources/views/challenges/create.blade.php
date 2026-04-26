@extends('layouts.app')

@section('title', 'Create New Challenge')
@section('page-title', 'Challenges & Competitions')

@section('topbar-actions')
    <a href="{{ route($prefix . '.challenges.index') }}" class="btn btn-ghost"><i class='bx bx-arrow-back'></i> View Challenges</a>
@endsection

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="padding: 40px 20px;">
        <i class='bx bx-trophy' style="font-size: 80px; color: var(--accent); margin-bottom: 20px;"></i>
        <h2 style="font-size: 28px; margin-bottom: 15px;">Ready to start a new challenge?</h2>
        <p style="color: var(--text-muted); font-size: 16px; margin-bottom: 40px; line-height: 1.6;">
            Once created, the system will generate a <strong>6-character secret code</strong>. 
            Share this code with your students or peers to invite them to join and compete in real-time on the leaderboard.
        </p>

        <form action="{{ route($prefix . '.challenges.store') }}" method="POST" style="text-align: left;">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="title">Challenge Title (Optional)</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Daily Genius Challenge" style="width: 100%;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="topic">Challenge Topic (AI will generate questions based on this) <span style="color:red;">*</span></label>
                <input type="text" name="topic" id="topic" class="form-control" placeholder="e.g. Present Simple, Fruits, Sport..." required style="width: 100%;">
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="questions_count">Number of Questions (10 - 30)</label>
                    <input type="number" name="questions_count" id="questions_count" class="form-control" value="10" min="10" max="30" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label for="question_type">Question Type</label>
                    <select name="question_type" id="question_type" class="form-control" required style="width: 100%;">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="matching">Matching</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label for="description">Additional Description (Optional)</label>
                <textarea name="description" id="description" class="form-control" rows="2" placeholder="Describe the goal of this challenge..." style="width: 100%;"></textarea>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary" style="font-size: 20px; padding: 15px 40px; box-shadow: 0 10px 30px rgba(14,165,233,0.3); border-radius: 15px; width: 100%;">
                    Create & Generate Questions <i class='bx bx-rocket'></i>
                </button>
                <p style="margin-top: 15px; font-size: 13px; color: var(--text-muted);">AI question generation might take a few seconds...</p>
            </div>
        </form>
    </div>
</div>
@endsection
