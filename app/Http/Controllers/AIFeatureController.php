<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIFeatureController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate feedback for a specific incorrect question.
     * Expects: result_id, question_id
     */
    public function generateFeedback(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:quiz_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'retry' => 'nullable|boolean'
        ]);

        $attempt = QuizAttempt::findOrFail($request->attempt_id);

        $user = Auth::user();
        if ($user->type === 'user' && $attempt->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Daily usage limit for AI feedback for Free users
        if ($user->type === 'user' && $user->isFree()) {
            $today = now()->toDateString();
            $feedbackUsageCount = \App\Models\DailyUserUsage::where('user_id', $user->id)
                ->where('usage_type', 'ai_feedback')
                ->where('usage_date', $today)
                ->count();

            if ($feedbackUsageCount >= 3) {
                return response()->json([
                    'error' => 'Daily AI feedback limit reached (3). Upgrade to premium for unlimited explanations.',
                    'show_modal' => true
                ], 403);
            }
        }

        $details = $attempt->details ?? [];
        $feedbackFound = false;
        
        $questionDetails = null;
        $questionIndex = -1;

        foreach ($details as $index => $q) {
            if ($q['question_id'] == $request->question_id) {
                $questionDetails = $q;
                $questionIndex = $index;
                break;
            }
        }

        if (!$questionDetails) {
            return response()->json(['error' => 'Question details not found in test result.'], 404);
        }

        $isRetry = $request->input('retry', false);

        // If it already has feedback and not a retry, return it
        if (!empty($questionDetails['ai_feedback']) && !$isRetry) {
            return response()->json(['feedback' => $questionDetails['ai_feedback']]);
        }

        // Must be incorrect and have valid selections
        if ($questionDetails['is_correct']) {
            return response()->json(['error' => 'Question was answered correctly.'], 400);
        }

        $studentAnswer = $questionDetails['student_answer'] ?? 'Not answered';
        $correctAnswer = $questionDetails['correct_answer'] ?? '';
        $questionText = $questionDetails['question'] ?? '';

        $feedback = $this->aiService->generateQuestionFeedback($questionText, $correctAnswer, $studentAnswer);

        if ($feedback && strpos($feedback, 'حدث خطأ') === false) {
            $details[$questionIndex]['ai_feedback'] = $feedback;
            $attempt->details = $details;
            $attempt->save();

            // Track usage for Free users
            if ($user->type === 'user' && $user->isFree()) {
                \App\Models\DailyUserUsage::create([
                    'user_id' => $user->id,
                    'usage_type' => 'ai_feedback',
                    'item_id' => $request->question_id,
                    'usage_date' => now()->toDateString(),
                ]);
            }
        }

        return response()->json(['feedback' => $feedback]);
    }

    /**
     * Generate quiz questions via AI and attach them to a quiz.
     */
    public function generateQuestions(Request $request, Quiz $quiz)
    {
        $user = Auth::user();
        if (!in_array($user->type, ['admin', 'teacher'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if teacher has access to the quiz (either creator or assigned to the grade)
        if ($user->type === 'teacher') {
            $isCreator = $quiz->user_id === $user->id;
            $isAssigned = false;
            
            if ($quiz->grade_id && $quiz->school_language_id) {
                $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                    ->where('grade_id', $quiz->grade_id)
                    ->where('school_language_id', $quiz->school_language_id)
                    ->exists();
            }

            if (!$isCreator && !$isAssigned) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $request->validate([
            'topic' => 'required|string|max:255',
            'difficulty' => 'required|in:easy,medium,hard',
            'count' => 'required|integer|min:1|max:10',
            'type' => 'required|in:multiple_choice,true_false,matching,essay',
            'points' => 'required|integer|min:1'
        ]);

        $type = $request->input('type', 'multiple_choice');
        $pointsPerQuestion = $request->input('points', 1);

        $questionsData = $this->aiService->generateQuizQuestions(
            $request->topic, 
            $request->difficulty, 
            $request->count,
            $type,
            $pointsPerQuestion
        );

        if (!$questionsData || !is_array($questionsData)) {
            return response()->json(['error' => 'فشلت عملية التوليد أو لم يتم التعرف على الرد من الذكاء الاصطناعي.'], 500);
        }

        $createdCount = 0;
        foreach ($questionsData as $qData) {
            $questionPoints = $qData['points'] ?? $pointsPerQuestion;
            
            if ($type === 'multiple_choice' || $type === 'true_false') {
                if (isset($qData['question'], $qData['options'], $qData['correct_answer_index']) && is_array($qData['options']) && count($qData['options']) >= 2) {
                    $question = Question::create([
                        'quiz_id' => $quiz->id,
                        'question' => $qData['question'],
                        'type' => $type,
                        'points' => $questionPoints
                    ]);
                    foreach ($qData['options'] as $index => $optionText) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer' => $optionText,
                            'is_correct' => ($index === (int)$qData['correct_answer_index'])
                        ]);
                    }
                    $createdCount++;
                }
            } elseif ($type === 'matching') {
                if (isset($qData['question'], $qData['pairs']) && is_array($qData['pairs'])) {
                    $question = Question::create([
                        'quiz_id' => $quiz->id,
                        'question' => $qData['question'],
                        'type' => 'matching',
                        'points' => $questionPoints
                    ]);
                    foreach ($qData['pairs'] as $term => $definition) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer' => $term . '|||' . $definition,
                            'is_correct' => true
                        ]);
                    }
                    $createdCount++;
                }
            } elseif ($type === 'essay') {
                if (isset($qData['question'])) {
                    Question::create([
                        'quiz_id' => $quiz->id,
                        'question' => $qData['question'],
                        'type' => 'essay',
                        'points' => $questionPoints
                    ]);
                    $createdCount++;
                }
            }
        }

        if ($createdCount === 0) {
            return response()->json(['error' => 'لم يتم توليد أي أسئلة صالحة. يرجى المحاولة مرة أخرى.'], 500);
        }

        return response()->json([
            'success' => true, 
            'message' => "تم توليد {$createdCount} سؤال بنجاح وإضافتها للاختبار.",
            'redirect' => route(auth()->user()->type . '.quizzes.show', $quiz->id)
        ]);
    }

    /**
     * Generate a lesson draft via AI.
     */
    public function generateLessonDraft(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->type, ['admin', 'teacher'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'topic' => 'required|string|max:255',
            'level' => 'nullable|string|in:Beginner,Intermediate,Advanced',
            'length' => 'nullable|string|in:short,medium,long'
        ]);

        $level = $request->input('level', 'Beginner');
        $length = $request->input('length', 'medium');
        $topic = $request->topic;

        $draftData = $this->aiService->generateLessonDraft($topic, $level, $length);

        if (!$draftData || !is_array($draftData) || !isset($draftData['blocks'])) {
            return response()->json(['error' => 'تعذر توليد الدرس. يرجى المحاولة مرة أخرى.'], 500);
        }

        return response()->json([
            'success' => true,
            'draft' => $draftData
        ]);
    }
}
