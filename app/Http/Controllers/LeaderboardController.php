<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    protected $languageService;

    public function __construct(\App\Services\LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSchoolContext = false;
        
        $query = \App\Models\User::where('type', 'user')
            ->with(['school', 'userLanguages.language']);

        if ($user->isAdmin() || $user->isManager()) {
            $query->whereNull('school_id');
            $title = "Global Leaderboards";
        } else {
            // School or Student in a school
            $schoolId = $user->school_id;
            if (!$schoolId) {
                $query->whereNull('school_id');
                $title = "Global Leaderboards";
            } else {
                $isSchoolContext = true;
                $query->where('school_id', $schoolId);
                
                // GRADE ISOLATION: If student, only show their grade
                if ($user->type === 'user') {
                    $myGrade = $user->gradesAsStudent()->first();
                    if ($myGrade) {
                        $query->whereHas('gradesAsStudent', function($q) use ($myGrade) {
                            $q->where('grade_id', $myGrade->id);
                        });
                        $title = "Grade Leaderboard: " . $myGrade->name;
                    } else {
                        $title = "School Leaderboard";
                    }
                } else {
                    $title = "School Leaderboard";
                }
            }
        }

        $allStudents = $query->get();

        // Group by Language
        $leaderboardGroups = [];
        
        // --- 1. Overall Ranking (Total XP) ---
        // Only show Overall for Global context (non-school)
        if (!$isSchoolContext) {
            $overallList = $allStudents->map(function($student) {
                $cloned = clone $student;
                $cloned->display_xp = $student->userLanguages->sum('learning_xp');
                return $cloned;
            })->filter(fn($s) => $s->display_xp > 0)->sortByDesc('display_xp')->values();

            if ($overallList->count() > 0) {
                $leaderboardGroups['Overall (Total XP)'] = $overallList;
            }
        }

        // --- 2. Per Language Ranking ---
        foreach ($allStudents as $student) {
            foreach ($student->userLanguages as $ul) {
                if ($ul->learning_xp > 0) {
                    $langName = $ul->language->name ?? 'Other';
                    
                    // Special rule: if it's school context and user said "remove English"
                    // We interpret this as: don't show specific language tabs if they are just the primary one
                    // But if there's only one language, we still need to store it to display it.
                    // Actually, the user wants a flat list for the school.
                    // If it's school context, we'll store everything in a unified list IF preferred,
                    // but I'll stick to logic: if school context, we can just name the tab "Ranking" 
                    // or remove the tab header in the view.
                    
                    if (!isset($leaderboardGroups[$langName])) {
                        $leaderboardGroups[$langName] = collect();
                    }
                    $clonedStudent = clone $student;
                    $clonedStudent->display_xp = $ul->learning_xp;
                    $leaderboardGroups[$langName]->push($clonedStudent);
                }
            }
        }

        // --- 3. Final Cleanup for School Context ---
        if ($isSchoolContext) {
            // Remove 'English' if it exists as per user request to simplify
            if (isset($leaderboardGroups['English'])) {
                // We'll rename it to something generic like 'Scoreboard' 
                // or just leave it as the only group.
                // The view will hide tabs if there's only one.
                $ranking = $leaderboardGroups['English'];
                unset($leaderboardGroups['English']);
                $leaderboardGroups['Students Ranking'] = $ranking;
            }
        }

        // Sort each language group
        foreach ($leaderboardGroups as $lang => $studentsGroup) {
            if ($lang !== 'Overall (Total XP)') {
                $leaderboardGroups[$lang] = $studentsGroup->sortByDesc('display_xp')->values();
            }
        }

        // Limit for Free Self-Learning Users
        if ($user->type === 'user' && $user->isFree()) {
            foreach ($leaderboardGroups as $lang => $studentsGroup) {
                $leaderboardGroups[$lang] = $studentsGroup->take(3);
            }
            if (!session()->has('error')) {
                session()->now('error', 'الخطة المجانية تتيح لك رؤية أول 3 طلاب فقط في لوحة الشرف. قم بالترقية للوصول الكامل.');
            }
        }

        $schools = collect(); 
        $schoolId = null;

        return view('leaderboard.index', compact('leaderboardGroups', 'schoolId', 'schools', 'title'));
    }

    /**
     * AJAX endpoint for Class-specific leaderboard modal
     */
    public function classLeaderboard(\App\Models\User $teacher, \App\Models\SchoolLanguage $language)
    {
        $user = auth()->user();
        $myGrade = $user->gradesAsStudent()->first();
        
        if (!$myGrade) return response()->json(['error' => 'No grade assigned'], 403);

        // Fetch all students in this grade
        $studentsInGrade = $myGrade->students()->get();

        // Get all quizzes by this teacher in this language/grade
        $quizIds = \App\Models\Quiz::where('user_id', $teacher->id)
            ->where('grade_id', $myGrade->id)
            ->where('school_language_id', $language->id)
            ->pluck('id');

        // Sum points for these quizzes per student
        $rankingsData = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
            ->where('status', 'completed')
            ->selectRaw('user_id, SUM(score) as total_score')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $leaderboard = $studentsInGrade->map(function($student) use ($rankingsData) {
            return [
                'name' => $student->name,
                'score' => isset($rankingsData[$student->id]) ? (float)$rankingsData[$student->id]->total_score * 10 : 0,
                'avatar' => mb_substr($student->name, 0, 1)
            ];
        })->filter(fn($s) => $s['score'] > 0)->sortByDesc('score')->values();

        return response()->json([
            'teacher' => $teacher->name,
            'subject' => $language->name,
            'leaderboard' => $leaderboard
        ]);
    }
    /**
     * Display a full-page ranking for a specific grade/language from a teacher's perspective.
     */
    public function teacherGradeRanking(\App\Models\Grade $grade, \App\Models\SchoolLanguage $language)
    {
        $user = auth()->user();
        if (!in_array($user->type, ['teacher', 'admin', 'manager'])) abort(403);

        // Fetch all students in this grade
        $studentsInGrade = $grade->students()->get();

        // Get all quizzes in this language/grade
        $quizIds = \App\Models\Quiz::where('grade_id', $grade->id)
            ->where('school_language_id', $language->id)
            ->pluck('id');

        // Sum points for these quizzes per student
        $rankingsData = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
            ->where('status', 'completed')
            ->selectRaw('user_id, SUM(score) as total_score')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $leaderboard = $studentsInGrade->map(function($student) use ($rankingsData) {
            $score = isset($rankingsData[$student->id]) ? (float)$rankingsData[$student->id]->total_score * 10 : 0;
            $student->display_xp = $score;
            return $student;
        })->filter(fn($s) => $s->display_xp > 0)->sortByDesc('display_xp')->values();

        // Map to standard leaderboard format for the view if needed, 
        // but we'll use a specific view for this.
        $teacherName = $user->type === 'teacher' ? $user->name : (in_array($user->type, ['admin', 'manager']) ? "Administrator View" : "View");
        $title = "Leaderboard: " . $grade->name . " - " . $language->name;

        return view('leaderboard.teacher_grade', compact('leaderboard', 'grade', 'language', 'title', 'teacherName'));
    }
}
