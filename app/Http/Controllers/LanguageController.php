<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Display a list of languages for the user to choose from.
     */
    public function index()
    {
        if (auth()->user() && auth()->user()->school_id) {
            return redirect()->route('user.dashboard')->with('error', 'Language switching is not available for school-enrolled students.');
        }

        $languages = Language::all();
        $userLanguages = auth()->user()->userLanguages->pluck('language_id')->toArray();

        return view('languages.index', compact('languages', 'userLanguages'));
    }

    /**
     * Switch the active language.
     */
    public function switch(Request $request)
    {
        if (auth()->user() && auth()->user()->school_id) {
            return redirect()->route('user.dashboard')->with('error', 'Language switching is not available for school-enrolled students.');
        }

        $request->validate([
            'language_id' => 'required|exists:languages,id'
        ]);

        $this->languageService->setActiveLanguageId($request->language_id);
        
        // Enroll if not already
        $this->languageService->enrollUser(auth()->user(), $request->language_id);

        return redirect()->back()->with('success', 'Language switched successfully.');
    }

    /**
     * Enroll in a new language.
     */
    public function enroll(Request $request)
    {
        if (auth()->user() && auth()->user()->school_id) {
            return redirect()->route('user.dashboard')->with('error', 'Language switching is not available for school-enrolled students.');
        }

        $request->validate([
            'language_id' => 'required|exists:languages,id'
        ]);

        $this->languageService->enrollUser(auth()->user(), $request->language_id);
        $this->languageService->setActiveLanguageId($request->language_id);

        return redirect()->route('user.dashboard')->with('success', 'You have enrolled in a new language!');
    }
}
