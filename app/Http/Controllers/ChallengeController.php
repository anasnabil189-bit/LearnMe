<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Services\AIService;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    protected $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }
    public function index()
    {
        $user = auth()->user();
        $query = Challenge::with(['creator', 'participants.user'])
            ->withCount('participants');

        if ($user->type === 'user') {
            // Filter by school: same school or both no school
            $query->whereHas('creator', function($q) use ($user) {
                if ($user->school_id) {
                    $q->where('school_id', $user->school_id);
                } else {
                    $q->whereNull('school_id');
                }
            });
        }

        $challenges = $query->latest()->paginate(10);
        
        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'user';
        return view('challenges.index', compact('challenges', 'prefix'));
    }

    public function create()
    {
        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'user';
        return view('challenges.create', compact('prefix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'topic'           => 'required|string|max:255',
            'questions_count' => 'required|integer|min:10|max:30',
            'question_type'  => 'required|string|in:multiple_choice,true_false,matching,essay',
        ], [
            'topic.required'           => 'يجب تحديد موضوع التحدي.',
            'questions_count.required' => 'يجب تحديد عدد الأسئلة.',
            'questions_count.min'      => 'أقل عدد أسئلة هو 10.',
            'questions_count.max'      => 'أقصى عدد أسئلة هو 30.',
            'question_type.required'   => 'يجب اختيار نوع الأسئلة.',
        ]);

        return DB::transaction(function() use ($request) {
            // 1. Create Challenge
            $challenge = Challenge::create([
                'title'           => $request->title ?? 'تحدي جديد #' . strtoupper(Str::random(4)),
                'description'     => $request->description,
                'topic'           => $request->topic,
                'questions_count' => $request->questions_count,
                'question_type'  => $request->question_type,
                'created_by'      => Auth::id(),
                'code'            => strtoupper(Str::random(6)),
            ]);

            // 2. Generate AI Questions
            $aiService = app(AIService::class);
            $generatedQuestions = $aiService->generateQuizQuestions(
                $request->topic, 
                'medium', 
                $request->questions_count, 
                $request->question_type
            );

            if (!$generatedQuestions || !is_array($generatedQuestions)) {
                throw new \Exception("فشل الذكاء الاصطناعي في توليد الأسئلة. حاول مرة أخرى بموضوع آخر.");
            }

            // 3. Create Quiz
            $quiz = Quiz::create([
                'title'       => "اختبار تحدي: " . ($request->title ?? $request->topic),
                'quiz_type'   => 'challenge',
                'source_type' => in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'user',
                'is_global'   => false,
            ]);

            // 4. Create Questions and Answers
            foreach ($generatedQuestions as $gq) {
                $question = Question::create([
                    'quiz_id'  => $quiz->id,
                    'question' => $gq['question'],
                    'type'     => $request->question_type,
                ]);

                if ($request->question_type === 'multiple_choice' || $request->question_type === 'true_false') {
                    foreach ($gq['options'] as $index => $option) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer'      => $option,
                            'is_correct'  => $index == $gq['correct_answer_index'],
                        ]);
                    }
                } elseif ($request->question_type === 'matching') {
                    foreach ($gq['pairs'] as $term => $def) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer'      => "{$term}|||{$def}",
                            'is_correct'  => true,
                        ]);
                    }
                } elseif ($request->question_type === 'essay') {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer'      => $gq['model_answer'],
                        'is_correct'  => true,
                    ]);
                }
            }

            $challenge->update(['quiz_id' => $quiz->id]);

            $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'user';
            return redirect()->route($prefix . '.challenges.show', $challenge->id)
                ->with('success', 'تم إنشاء التحدي وتوليد الأسئلة بنجاح! كود الغرفة: ' . $challenge->code);
        });
    }

    public function show(Challenge $challenge)
    {
        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'user';
        return view('challenges.show', compact('challenge', 'prefix'));
    }

    public function take(Challenge $challenge)
    {
        $user = Auth::user();
        if ($challenge->status === 'completed') {
            return redirect()->route('user.challenges.show', $challenge->id)->with('info', 'انتهى هذا التحدي بالفعل.');
        }

        if (!$challenge->participants()->where('user_id', $user->id)->exists()) {
            return redirect()->route('user.challenges.show', $challenge->id)->with('error', 'يجب الانضمام للتحدي أولاً.');
        }

        $quiz = $challenge->quiz;
        if (!$quiz) {
            return redirect()->route('user.challenges.show', $challenge->id)->with('error', 'لا يوجد اختبار مرتبط بهذا التحدي.');
        }

        $quiz->load(['questions.answers']);
        return view('challenges.take', compact('challenge', 'quiz'));
    }

    public function status(Challenge $challenge)
    {
        return response()->json([
            'status' => $challenge->status,
        ]);
    }

    public function submitChallenge(Request $request, Challenge $challenge)
    {
        $user = Auth::user();
        $quiz = $challenge->quiz;
        
        if ($challenge->status === 'completed') {
            return response()->json(['status' => 'already_completed', 'redirect' => route('user.challenges.show', $challenge->id)]);
        }

        // 1. Mark as completed (first one wins)
        $challenge->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        // 2. Grade the current user
        $this->gradeChallengeParticipant($user, $challenge, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنهاء التحدي للجميع! جاري عرض النتائج...',
            'redirect' => route('user.challenges.show', $challenge->id)
        ]);
    }

    protected function gradeChallengeParticipant($user, $challenge, $data)
    {
        $quiz = $challenge->quiz;
        $quiz->load(['questions.answers']);
        
        $earnedPoints = 0;
        $totalPointsPossible = $quiz->questions->count();
        $details = [];

        foreach ($quiz->questions as $question) {
            $isCorrect = false;
            $studentAnswerText = null;
            $correctAnswerText = null;
            $aiFeedback = null;
            $scoreForThisQuestion = 0;

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $selectedAnswerId = $data['answers'][$question->id] ?? null;
                $correctAnswerObj = $question->answers->where('is_correct', true)->first();
                $correctAnswerText = $correctAnswerObj ? $correctAnswerObj->answer : '';

                if ($selectedAnswerId) {
                    $answer = Answer::find($selectedAnswerId);
                    if ($answer) {
                        $studentAnswerText = $answer->answer;
                        if ($answer->is_correct) {
                            $isCorrect = true;
                            $scoreForThisQuestion = 1;
                        } else {
                            $aiFeedback = app(AIService::class)->generateQuestionFeedback($question->question, $correctAnswerText, $studentAnswerText);
                        }
                    }
                } else {
                    $studentAnswerText = "لم تتم الإجابة";
                    $aiFeedback = app(AIService::class)->generateQuestionFeedback($question->question, $correctAnswerText, "لم يقم الطالب بالإجابة.");
                }
            } elseif ($question->type === 'matching') {
                $matchingInputs = $data['matching'][$question->id] ?? [];
                $correctPairsCount = 0;
                $totalPairsCount = $question->answers->count();
                foreach ($question->answers as $ans) {
                    [$term, $def] = explode('|||', $ans->answer);
                    if (($matchingInputs[$ans->id] ?? null) === $def) {
                        $correctPairsCount++;
                    }
                }
                
                if ($correctPairsCount === $totalPairsCount) {
                    $isCorrect = true;
                    $scoreForThisQuestion = 1;
                } else {
                    $studentAnswerText = implode(', ', $matchingInputs);
                    $aiFeedback = app(AIService::class)->generateQuestionFeedback($question->question, "All pairs must match correctly.", $studentAnswerText);
                }
            } elseif ($question->type === 'essay') {
                $essayAnswer = $data['essay'][$question->id] ?? '';
                $studentAnswerText = $essayAnswer ?: "لم تتم الإجابة";
                
                $modelAnswerObj = $question->answers->first();
                $modelAnswerText = $modelAnswerObj ? $modelAnswerObj->answer : null;
                
                if (!empty($essayAnswer)) {
                    // AI Auto-grading with model answer
                    $aiGrading = app(AIService::class)->gradeEssayAnswer($question->question, $essayAnswer, $modelAnswerText);
                    if ($aiGrading && isset($aiGrading['score'])) {
                        $scoreForThisQuestion = $aiGrading['score'] / 100;
                        $aiFeedback = $aiGrading['feedback'] ?? null;
                        if ($scoreForThisQuestion >= 0.7) $isCorrect = true;
                    }
                } else {
                    $aiFeedback = "لم تقم بتقديم أي إجابة لهذا السؤال.";
                }
            }

            $earnedPoints += $scoreForThisQuestion;
            $details[] = [
                'question_id'    => $question->id,
                'question'       => $question->question,
                'type'           => $question->type,
                'student_answer' => $studentAnswerText,
                'correct_answer' => $correctAnswerText,
                'is_correct'     => $isCorrect,
                'score'          => $scoreForThisQuestion,
                'ai_feedback'    => $aiFeedback
            ];
        }

        $finalScore = $totalPointsPossible > 0 ? ($earnedPoints / $totalPointsPossible) * 100 : 0;
        
        // Result creation removed for cleanup.

        // 2. New Quiz Attempts & XP Logic (Secure)
        $attempt = $this->quizService->processSubmission($user, $quiz, [
            'earned_points'         => $earnedPoints,
            'total_points_possible' => $totalPointsPossible,
            'status'                => 'completed',
            'details'               => $details,
        ]);

        // 3. Update challenge participant score (Isolated room leaderboard)
        $participant = ChallengeParticipant::where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant) {
            $participant->increment('score', $attempt->xp_earned);
        }
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:challenges,code',
        ], [
            'code.required' => 'كود التحدي مطلوب.',
            'code.exists'   => 'كود التحدي غير صحيح.',
        ]);

        $user = Auth::user();
        if ($user->type !== 'user') {
            return back()->withErrors(['code' => 'يجب أن تكون طالبًا للمشاركة في التحدي.']);
        }

        $challenge = Challenge::where('code', $request->code)->firstOrFail();

        // Ecosystem isolation check
        $creator = $challenge->creator;
        if (($user->school_id !== $creator->school_id)) {
            return back()->withErrors(['code' => 'لا يمكنك الانضمام لتحدي خارج مدرستك / مسار تعليمك.']);
        }

        if ($challenge->participants()->where('user_id', $user->id)->exists()) {
            return back()->with('info', 'أنت مشارك في هذا التحدي مسبقًا.');
        }

        ChallengeParticipant::create([
            'challenge_id' => $challenge->id,
            'user_id'      => $user->id,
            'score'        => 0,
        ]);

        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'user';
        return redirect()->route($prefix . '.challenges.show', $challenge->id)->with('success', 'تم الانضمام إلى التحدي بنجاح!');
    }

    public function destroy(Challenge $challenge)
    {
        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'user';
        return redirect()->route($prefix . '.challenges.index')->with('success', 'تم حذف التحدي بنجاح.');
    }
}
