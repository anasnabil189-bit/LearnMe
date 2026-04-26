<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Level;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class CheckLevelAccess
{
    protected $languageService;

    public function __construct(\App\Services\LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Admins and teachers bypass these checks
        if (!$user || $user->type === 'admin' || $user->type === 'teacher') {
            return $next($request);
        }

        $level = null;

        // 1. Check if route has 'level' parameter
        if ($request->route('level')) {
            $level = $request->route('level');
            if (!$level instanceof Level) {
                $level = Level::find($level);
            }
        } 
        
        // 2. Check if route has 'lesson' parameter (protect lessons within levels)
        if (!$level && $request->route('lesson')) {
            $lesson = $request->route('lesson');
            if (!$lesson instanceof Lesson) {
                $lesson = Lesson::find($lesson);
            }
            if ($lesson && $lesson->level_id) {
                $level = $lesson->level;
            }
        }

        // 3. Check if route has 'quiz' parameter (protect quizzes within levels)
        if (!$level && $request->route('quiz')) {
            $quiz = $request->route('quiz');
            if (!$quiz instanceof Quiz) {
                $quiz = Quiz::find($quiz);
            }
            if ($quiz && $quiz->level_id) {
                $level = $quiz->level;
            }
        }

        // Enforce lock if XP is insufficient
        if ($level) {
            $languageId = $level->language_id ?: $this->languageService->getActiveLanguageId($user);
            $xp = $this->languageService->getUserXP($user, $languageId);

            if ($level->required_xp > $xp) {
                $msg = "This level is locked. Earn more XP in this language to unlock it. (Required: {$level->required_xp}, Current: {$xp})";
                
                if ($request->expectsJson()) {
                    return response()->json(['error' => $msg], 403);
                }
                
                return redirect()->route('user.dashboard')->with('error', $msg);
            }
        }

        return $next($request);
    }
}
