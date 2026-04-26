<?php

namespace App\Http\Controllers;

use App\Models\SchoolLanguage;
use App\Models\User;
use Illuminate\Http\Request;

class SchoolLanguageController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $languages = SchoolLanguage::where('school_id', $schoolId)->get();
        return view('school.languages.index', compact('languages'));
    }

    public function create()
    {
        $schoolId = auth()->user()->school_id;
        $teachers = User::where('school_id', $schoolId)->where('type', 'teacher')->get();
        return view('school.languages.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'teachers' => 'nullable|array',
            'teachers.*' => 'exists:users,id',
        ]);

        $schoolLanguage = SchoolLanguage::create([
            'school_id' => auth()->user()->school_id,
            'name' => $request->name,
        ]);

        if ($request->has('teachers')) {
            $schoolLanguage->teachers()->sync($request->teachers);
        }

        return redirect()->route('school.school-languages.index')->with('success', 'Language added successfully.');
    }

    public function edit(SchoolLanguage $schoolLanguage)
    {
        if ($schoolLanguage->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        $schoolId = auth()->user()->school_id;
        $teachers = User::where('school_id', $schoolId)->where('type', 'teacher')->get();
        return view('school.languages.edit', compact('schoolLanguage', 'teachers'));
    }

    public function update(Request $request, SchoolLanguage $schoolLanguage)
    {
        if ($schoolLanguage->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'teachers' => 'nullable|array',
            'teachers.*' => 'exists:users,id',
        ]);

        $schoolLanguage->update(['name' => $request->name]);

        if ($request->has('teachers')) {
            $schoolLanguage->teachers()->sync($request->teachers);
        } else {
            $schoolLanguage->teachers()->detach();
        }

        return redirect()->route('school.school-languages.index')->with('success', 'Language updated successfully.');
    }

    public function destroy(SchoolLanguage $schoolLanguage)
    {
        if ($schoolLanguage->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        $schoolLanguage->delete();
        return redirect()->route('school.school-languages.index')->with('success', 'Language deleted successfully.');
    }
}
