<?php

namespace App\Services;

use App\Models\User;
use App\Models\Language;
use App\Models\UserLanguage;
use Illuminate\Support\Facades\Session;

class LanguageService
{
    /**
     * Get the active language ID from session or default to the first language the user is enrolled in.
     */
    public function getActiveLanguageId(User $user)
    {
        if (Session::has('active_language_id')) {
            return Session::get('active_language_id');
        }

        $userLanguage = $user->userLanguages()->first();
        
        if ($userLanguage) {
            Session::put('active_language_id', $userLanguage->language_id);
            return $userLanguage->language_id;
        }

        // Fallback to English (id 1) if not enrolled in any
        $defaultLanguage = Language::where('code', 'en')->first();
        $id = $defaultLanguage ? $defaultLanguage->id : 1;
        Session::put('active_language_id', $id);
        return $id;
    }

    /**
     * Switch the active language in session.
     */
    public function setActiveLanguageId($languageId)
    {
        Session::put('active_language_id', $languageId);
    }

    /**
     * Get the UserLanguage record for the active/specific language.
     */
    public function getUserLanguage(User $user, $languageId = null)
    {
        $languageId = $languageId ?: $this->getActiveLanguageId($user);
        
        return UserLanguage::firstOrCreate(
            ['user_id' => $user->id, 'language_id' => $languageId],
            ['learning_xp' => 0]
        );
    }

    /**
     * Get XP for a specific language.
     */
    public function getUserXP(User $user, $languageId)
    {
        $ul = UserLanguage::where('user_id', $user->id)
            ->where('language_id', $languageId)
            ->first();

        return $ul ? $ul->learning_xp : 0;
    }

    /**
     * Enroll a user in a language if not already enrolled.
     */
    public function enrollUser(User $user, $languageId)
    {
        return UserLanguage::firstOrCreate(
            ['user_id' => $user->id, 'language_id' => $languageId],
            ['learning_xp' => 0]
        );
    }
}
