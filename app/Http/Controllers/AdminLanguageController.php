<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class AdminLanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::all();
        return view('admin.languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.languages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages,code',
        ]);

        Language::create($request->all());

        return redirect()->route('admin.languages.index')->with('success', 'Language created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Language $language)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages,code,' . $language->id,
        ]);

        $language->update($request->all());

        return redirect()->route('admin.languages.index')->with('success', 'Language updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language)
    {
        // Check if there's content tied to this language before deleting (optional but safe)
        if ($language->levels()->count() > 0 || $language->lessons()->count() > 0 || $language->quizzes()->count() > 0) {
            return redirect()->route('admin.languages.index')->with('error', 'Cannot delete language because it has levels, lessons, or quizzes associated with it.');
        }

        $language->delete();

        return redirect()->route('admin.languages.index')->with('success', 'Language deleted successfully.');
    }

    /**
     * Display detailed statistics for a specific language.
     */
    public function show(Language $language)
    {
        $levels = $language->levels()
            ->withCount(['lessons', 'quizzes'])
            ->orderBy('required_xp')
            ->get();

        return view('admin.languages.show', compact('language', 'levels'));
    }
}
