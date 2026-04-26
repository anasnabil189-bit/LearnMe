<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Language;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->has('language_id')) {
            $languages = Language::withCount(['levels', 'userLanguages' => function ($query) {
                $query->whereHas('user', function($q) {
                    $q->where('type', 'user')->whereNull('school_id');
                });
            }])->get();
            
            return view('admin.shared.languages', [
                'languages' => $languages,
                'pageTitle' => 'Self-Learning Levels System',
                'entityType' => 'Levels',
                'entityCountAttr' => 'levels_count',
                'manageRouteName' => 'admin.levels.index'
            ]);
        }

        $levels = Level::where('language_id', $request->language_id)
            ->with(['language'])
            ->withCount(['lessons', 'quizzes'])
            ->orderBy('required_xp')
            ->paginate(10);
            
        return view('admin.levels.index', compact('levels'));
    }

    public function create()
    {
        $languages = Language::all();
        return view('admin.levels.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'required_xp' => 'required|integer|min:0',
            'language_id' => 'required|exists:languages,id',
        ], [
            'name.required'        => 'Level name is required.',
            'required_xp.required' => 'Required XP is required.',
            'language_id.required'  => 'Please select a language.',
        ]);

        Level::create($request->only('name', 'required_xp', 'language_id'));

        return redirect()->route('admin.levels.index')->with('success', 'Level created successfully.');
    }

    public function show(Level $level)
    {
        $level->load(['lessons', 'quizzes']);
        return view('admin.levels.show', compact('level'));
    }

    public function edit(Level $level)
    {
        $languages = Language::all();
        return view('admin.levels.edit', compact('level', 'languages'));
    }

    public function update(Request $request, Level $level)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'required_xp' => 'required|integer|min:0',
            'language_id' => 'required|exists:languages,id',
        ]);

        $level->update($request->only('name', 'required_xp', 'language_id'));

        return redirect()->route('admin.levels.index')->with('success', 'Level updated successfully.');
    }

    public function destroy(Level $level)
    {
        $level->delete();
        return redirect()->route('admin.levels.index')->with('success', 'Level deleted successfully.');
    }

    /**
     * Get levels by language ID (for AJAX filtering)
     */
    public function getByLanguage(Language $language)
    {
        $levels = Level::where('language_id', $language->id)
            ->orderBy('required_xp')
            ->get(['id', 'name', 'required_xp']);
            
        return response()->json($levels);
    }
}
