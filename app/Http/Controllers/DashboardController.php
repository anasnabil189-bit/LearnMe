<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Result;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $languageService;

    public function __construct(\App\Services\LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function index()
    {
        $user = Auth::user();

        $data = match ($user->type) {
            'admin' => [
                'stats' => [
                    'schools'           => School::count(),
                    'teachers'          => \App\Models\User::where('type', 'teacher')->count(),
                    'students_school'   => \App\Models\User::where('type', 'user')->whereNotNull('school_id')->count(),
                    'students_individual'     => \App\Models\User::where('type', 'user')->whereNull('school_id')->count(),
                    'global_levels'     => Level::where('is_global', true)->count(),
                    'global_lessons'    => Lesson::where('is_global', true)->count(),
                ],
                'recentSchools'  => School::latest()->take(5)->get(),
                'recentTeachers' => \App\Models\User::where('type', 'teacher')->with('school')->latest()->take(5)->get(),
            ],
            'school' => [
                'stats' => [
                    'grades'  => Grade::where('school_id', $user->school_id ?? $user->id)->count(),
                    'teachers' => \App\Models\User::where('type', 'teacher')->where('school_id', $user->school_id ?? $user->id)->count(),
                    'students' => \App\Models\User::where('type', 'user')->where('school_id', $user->school_id ?? $user->id)->count(),
                ],
                'teachersData' => \App\Models\User::where('type', 'teacher')
                    ->where('school_id', $user->school_id ?? $user->id)
                    ->with(['languagesTaught', 'teacherAssignments', 'unlockedStudents'])
                    ->latest()->take(5)->get(),
            ],
            'teacher' => [
                'teacher' => $user,
                'grades' => Grade::whereHas('teacherAssignments', function($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                })->get(),
                'stats' => [
                    'grades' => \App\Models\TeacherAssignment::where('teacher_id', $user->id)->distinct('grade_id')->count('grade_id'),
                    'lessons' => Lesson::where('user_id', $user->id)->count(),
                    'quizzes' => Quiz::where('user_id', $user->id)->count(),
                ],
            ],
            'user' => [
                'student'  => $user,
                'learning_xp' => $this->languageService->getUserXP($user, $this->languageService->getActiveLanguageId($user)),
                'challenge_xp' => $user->challenge_xp,
                'results'  => \App\Models\QuizAttempt::where('user_id', $user->id)->with('quiz')->latest()->take(5)->get(),
                'mySchool' => $user->school_id ? [
                    'classes' => $user->gradesAsStudent()->with(['teacher', 'lessons', 'quizzes'])->get(),
                ] : null,
                'coursesLevels' => !$user->school_id ? [
                    'levels' => Level::where('is_global', true)
                        ->where('language_id', $this->languageService->getActiveLanguageId($user))
                        ->with(['lessons' => function($q){
                            $q->where('is_global', true);
                        }, 'quizzes' => function($q){
                            $q->where('is_global', true);
                        }])->orderBy('required_xp')->get(),
                    'currentLevel' => $user->levels()->latest()->first(),
                ] : null,
                'stats' => [
                    'results'    => \App\Models\QuizAttempt::where('user_id', $user->id)->count(),
                    'challenges' => \App\Models\ChallengeParticipant::where('user_id', $user->id)->count(),
                    'learning_xp'  => $this->languageService->getUserXP($user, $this->languageService->getActiveLanguageId($user)),
                ],
            ],
            default => [],
        };

        return view('dashboard.index', compact('user', 'data'));
    }
}
