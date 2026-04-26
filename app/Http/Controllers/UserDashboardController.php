<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    protected $languageService;

    public function __construct(\App\Services\LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function index()
    {
        $user = auth()->user();
        $activeLanguageId = $this->languageService->getActiveLanguageId($user);
        $userLanguage = $this->languageService->getUserLanguage($user, $activeLanguageId);
        
        $learningXp = $userLanguage->learning_xp ?? 0;
        $challengeXp = $user->challenge_xp ?? 0;

        $myGrade = null;
        $gradeLanguages = collect();
        $unlockedTeacherIds = [];

        // If School-based
        if ($user->school_id) {
            $myGrade = $user->gradesAsStudent()->first();
            
            if ($myGrade) {
                // Get languages specifically linked to this grade
                $gradeLanguages = $myGrade->schoolLanguages()->with(['teachers'])->get();
                $unlockedTeacherIds = $user->unlockedTeachers()->pluck('teacher_id')->toArray();
            }

            $levels = collect(); 
            $unlockedLevelIds = [];
        } 
        // If Self-Learning
        else {
            $levels = \App\Models\Level::where('is_global', true)
                ->where('language_id', $activeLanguageId)
                ->withCount(['lessons' => function($q){
                    $q->where('is_global', true);
                }, 'quizzes' => function($q){
                    $q->where('is_global', true);
                }])->orderBy('required_xp')->get();
            
            $unlockedLevelIds = $levels->filter(function($lvl) use ($learningXp) {
                return $learningXp >= ($lvl->required_xp ?? 0);
            })->pluck('id')->toArray();
        }

        return view('user.dashboard', [
            'student'          => $user, 
            'activeLanguageId' => $activeLanguageId,
            'xp'               => $learningXp, 
            'learning_xp'      => $learningXp,
            'challenge_xp'     => $challengeXp,
            'myGrade'          => $myGrade,
            'gradeLanguages'   => $gradeLanguages,
            'unlockedTeacherIds' => $unlockedTeacherIds,
            'courseLevels'     => $levels ?? collect(),
            'unlockedLevels'   => $unlockedLevelIds ?? [],
            'allLanguages'     => \App\Models\Language::all(),
        ]);
    }


    public function startTrial()
    {
        $user = auth()->user();
        
        if ($user->school_id) {
            return redirect()->back()->with('error', 'Trials are not available for school accounts.');
        }

        if ($user->trial_ends_at) {
            return redirect()->back()->with('error', 'You have already used your free trial.');
        }

        $user->update([
            'trial_ends_at' => now()->addHours(48)
        ]);

        return redirect()->back()->with('success', 'Your 48-hour free trial has started! Enjoy premium features.');
    }
}
