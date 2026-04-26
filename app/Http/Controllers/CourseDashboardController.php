<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $languageService = app(\App\Services\LanguageService::class);
        $activeLanguageId = $languageService->getActiveLanguageId($user);
        $userXp = $languageService->getUserXP($user, $activeLanguageId);

        // Get levels for the active language
        $levels = Level::where('is_global', true)
            ->where('language_id', $activeLanguageId)
            ->orderBy('required_xp')
            ->with(['lessons' => function($q) {
                $q->orderBy('order');
            }])
            ->get();

        // A level is unlocked if user_xp >= required_xp
        $unlockedLevels = $levels->filter(fn($l) => $userXp >= $l->required_xp)->pluck('id')->toArray();

        return view('user.courses_dashboard', compact('levels', 'unlockedLevels', 'userXp'));
    }

    public function showLesson(Lesson $lesson)
    {
        $user = Auth::user();

        // Check if lesson's level is unlocked for the user
        if (!$user->levels()->where('level_id', $lesson->level_id)->exists()) {
            return redirect()->route('user.dashboard')->with('error', 'Level is locked.');
        }

        // Enforce required tier logic
        $req = $lesson->required_tier;
        $blocked = false;

        if ($req === 'pro' && !$user->isPro()) {
            $blocked = true;
        } elseif ($req === 'basic' && $user->isFree()) {
            $blocked = true;
        }

        if ($blocked) {
            $planName = ucfirst($req);
            return redirect()->route('user.dashboard')->with('error', "هذا الدرس متاح فقط لأصحاب باقة {$planName} فما فوق. للحصول على إمكانية الوصول، يرجى ترقية حسابك.");
        }

        // Sequential check removed. Users can access any lesson allowed by their tier.

        return view('user.lesson_view', compact('lesson'));
    }

    public function submitLessonQuiz(Request $request, Lesson $lesson)
    {
        $user = Auth::user();
        $quiz = $lesson->quiz;

        if (!$quiz) {
            // If no quiz, mark lesson as completed
            $user->completedLessons()->syncWithoutDetaching([$lesson->id => ['passed' => true]]);
            return $this->redirectAfterCompletion($lesson, $user, 'Lesson completed!');
        }

        $answers = $request->input('answers', []);
        $correctCount = 0;
        $questions = $quiz->questions;
        $totalQuestions = $questions->count();
        
        $details = [];

        foreach ($questions as $question) {
            $answerId = $answers[$question->id] ?? null;
            $isCorrect = false;
            $studentAnswerText = null;
            $correctAnswerText = null;

            $correctAnswerObj = $question->answers()->where('is_correct', true)->first();
            if ($correctAnswerObj) {
                $correctAnswerText = $correctAnswerObj->answer;
            }

            if ($answerId) {
                $studentAnswerObj = $question->answers()->find($answerId);
                if ($studentAnswerObj) {
                    $studentAnswerText = $studentAnswerObj->answer;
                    if ($studentAnswerObj->is_correct) {
                        $isCorrect = true;
                        $correctCount++;
                    }
                }
            }

            $details[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'student_answer' => $studentAnswerText,
                'correct_answer' => $correctAnswerText,
                'is_correct' => $isCorrect,
                'ai_feedback' => null
            ];
        }

        $score = $totalQuestions > 0 ? (int) (($correctCount / $totalQuestions) * 100) : 0;
        $passed = $score >= 70; // Hardcoded passing score

        \App\Models\QuizAttempt::create([
            'user_id'           => $user->id,
            'quiz_id'           => $quiz->id,
            'score'             => $correctCount,
            'total_points'      => $totalQuestions,
            'unused_percentage' => $score,
            'details'           => $details,
            'status'            => 'completed',
        ]);

        if ($passed) {
            $user->completedLessons()->syncWithoutDetaching([$lesson->id => ['passed' => true]]);
            
            // Check if this was the last lesson in the level
            $isLastLesson = !Lesson::where('level_id', $lesson->level_id)
                ->where('order', '>', $lesson->order)
                ->exists();

            if ($isLastLesson) {
                // Unlock the next level if exists
                $nextLevel = Level::where('is_global', true)
                    ->where('id', '>', $lesson->level_id)
                    ->orderBy('id', 'asc')
                    ->first();
                
                if ($nextLevel && !$user->levels()->where('level_id', $nextLevel->id)->exists()) {
                    $user->levels()->attach($nextLevel->id);
                }
            }

            return $this->redirectAfterCompletion($lesson, $user, 'Lesson passed! Ready for the next one.');
        }

        return redirect()->back()->with('error', "Quiz failed. Your score: {$score}%. Try again to unlock the next lesson.");
    }

    private function redirectAfterCompletion($lesson, $user, $message)
    {
        $nextLesson = Lesson::where('level_id', $lesson->level_id)
            ->where('order', '>', $lesson->order)
            ->orderBy('order', 'asc')
            ->first();

        // If there's no next lesson in same level, check if it was last lesson to unlock next level
        if (!$nextLesson) {
            $nextLevel = Level::where('is_global', true)
                ->where('id', '>', $lesson->level_id)
                ->orderBy('id', 'asc')
                ->first();
                
            if ($nextLevel) {
                // Return to dashboard to show newly unlocked level
                return redirect()->route('user.dashboard')->with('success', $message . ' لقد وصلت لمستوى جديد!');
            }
            return redirect()->route('user.dashboard')->with('success', $message . ' انتهت جميع المستويات!');
        }

        // Enforce required tier logic for the NEXT lesson
        $req = $nextLesson->required_tier;
        $blocked = false;

        if ($req === 'pro' && !$user->isPro()) {
            $blocked = true;
        } elseif ($req === 'basic' && $user->isFree()) {
            $blocked = true;
        }

        if ($blocked) {
            $planName = ucfirst($req);
            return redirect()->route('user.dashboard')->with('success', $message . " الدرس التالي متاح فقط لأصحاب باقة {$planName} فما فوق. يرجى ترقية حسابك للمتابعة.");
        }

        return redirect()->route('courses.lesson', $nextLesson->id)->with('success', $message . ' جاري تحويلك للدرس التالي...');
    }
}
