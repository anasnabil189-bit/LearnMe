<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLevelController extends Controller
{
    public function show(Level $level, \App\Services\LanguageService $languageService)
    {
        $user = Auth::user();

        $isEnrolled = $user->levels()->where('level_id', $level->id)->exists();
        
        if ($isEnrolled) {
            // Load content
            $level->load(['lessons', 'quizzes' => function($q) {
                $q->withCount('questions');
            }]);
        }
        
        $xp = $languageService->getUserXP($user, $level->language_id);
        $canEnroll = $xp >= $level->required_xp;

        return view('user.levels.show', compact('level', 'isEnrolled', 'canEnroll', 'xp'));
    }

    public function enroll(Request $request, Level $level, \App\Services\LanguageService $languageService)
    {
        $user = Auth::user();

        $xp = $languageService->getUserXP($user, $level->language_id);
        
        if ($xp < $level->required_xp) {
            return back()->with('error', 'عذراً! لا تملك نقاط خبرة (XP) كافية للتسجيل في هذا المستوى.');
        }

        if (!$user->levels()->where('level_id', $level->id)->exists()) {
            $user->levels()->attach($level->id);
            return back()->with('success', 'مبروك! تم فتح المستوى بنجاح.');
        }

        return back()->with('info', 'أنت مسجل بالفعل في هذا المستوى.');
    }
}
