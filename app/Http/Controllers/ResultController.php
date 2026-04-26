<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $user->type;
        $prefix = in_array($type, ['admin', 'manager']) ? 'admin' : ($type === 'teacher' ? 'teacher' : 'user');

        if ($type === 'user') {
            $isSchoolStudent = !empty($user->school_id);
            $unlockedTeachers = $isSchoolStudent ? $user->unlockedTeachers : collect();
            $selectedTeacherId = $request->query('teacher_id');

            if ($isSchoolStudent) {
                // School Learning Results: Only show quizzes from teachers they've unlocked
                $query = \App\Models\QuizAttempt::where('user_id', $user->id)
                    ->whereHas('quiz', function($q) use ($selectedTeacherId, $unlockedTeachers) {
                        if ($selectedTeacherId) {
                            $q->where('user_id', $selectedTeacherId);
                        } else {
                            $q->whereIn('user_id', $unlockedTeachers->pluck('id'));
                        }
                        // Quizzes created by school teachers shouldn't have self-learning language_id
                        $q->whereNull('language_id');
                    });
            } else {
                // Self-Learning Results: Filter by active language
                $activeLanguageId = app(\App\Services\LanguageService::class)->getActiveLanguageId($user);
                $query = \App\Models\QuizAttempt::where('user_id', $user->id)
                    ->whereHas('quiz', function($q) use ($activeLanguageId) {
                        $q->where('language_id', $activeLanguageId);
                    });
            }

            $results = $query->with(['quiz.grade', 'quiz.level', 'quiz.schoolLanguage', 'quiz.user', 'quiz.lesson'])
                ->latest()->paginate(10);
            
            // Get best attempt IDs for this user to show badges
            // Apply same context filtering to bestAttemptIds
            $summaryQuery = \App\Models\UserQuizSummary::where('user_id', $user->id);
            if ($isSchoolStudent) {
                $summaryQuery->whereHas('quiz', function($q) use ($selectedTeacherId, $unlockedTeachers) {
                    if ($selectedTeacherId) {
                        $q->where('user_id', $selectedTeacherId);
                    } else {
                        $q->whereIn('user_id', $unlockedTeachers->pluck('id'));
                    }
                    $q->whereNull('language_id');
                });
            } else {
                $activeLanguageId = app(\App\Services\LanguageService::class)->getActiveLanguageId($user);
                $summaryQuery->whereHas('quiz', function($q) use ($activeLanguageId) {
                    $q->where('language_id', $activeLanguageId);
                });
            }

            $bestAttemptIds = $summaryQuery->get()
                ->map(function($summary) use ($user) {
                    $best = \App\Models\QuizAttempt::where('user_id', $user->id)
                        ->where('quiz_id', $summary->quiz_id)
                        ->where('xp_earned', $summary->best_xp)
                        ->first();
                    return $best ? $best->id : null;
                })->filter()->toArray();

            return view('results.index', compact('results', 'prefix', 'bestAttemptIds', 'isSchoolStudent', 'unlockedTeachers', 'selectedTeacherId'));
        } elseif ($type === 'teacher') {
            $assignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->with(['grade', 'schoolLanguage'])
                ->get()
                ->map(function($assignment) use ($user) {
                    $quizIds = \App\Models\Quiz::where('grade_id', $assignment->grade_id)
                        ->where('school_language_id', $assignment->school_language_id)
                        ->pluck('id');
                    
                    $assignment->results_count = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
                        ->count();
                    return $assignment;
                });
            return view('results.index', compact('assignments', 'prefix'));
        } else {
            $results = \App\Models\QuizAttempt::with(['user', 'quiz'])->latest()->paginate(10);
        }

        return view('results.index', compact('results', 'prefix'));
    }

    public function managedResults(\App\Models\Grade $grade, \App\Models\SchoolLanguage $language)
    {
        $user = auth()->user();
        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        
        $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
            ->where('grade_id', $grade->id)
            ->where('school_language_id', $language->id)
            ->exists();
        if (!$isAssigned && !in_array($user->type, ['admin', 'manager'])) abort(403);

        $quizIds = \App\Models\Quiz::where('grade_id', $grade->id)
            ->where('school_language_id', $language->id)
            ->pluck('id');

        $results = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
            ->with(['user', 'quiz'])
            ->latest()
            ->paginate(15);

        return view('results.by_grade', compact('results', 'grade', 'language', 'prefix'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : ($user->type === 'teacher' ? 'teacher' : 'user');
        
        // Find in QuizAttempt (Results table is legacy and will be dropped)
        $result = \App\Models\QuizAttempt::with(['user', 'quiz.questions.answers'])->findOrFail($id);

        return view('results.show', compact('result', 'prefix'));
    }
}
