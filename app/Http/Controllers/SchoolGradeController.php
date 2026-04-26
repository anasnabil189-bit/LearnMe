<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;

class SchoolGradeController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $grades = Grade::where('school_id', $schoolId)->with('schoolLanguages')->get();
        return view('school.grades.index', compact('grades'));
    }

    public function create()
    {
        $schoolLanguages = \App\Models\SchoolLanguage::where('school_id', auth()->user()->school_id)->get();
        return view('school.grades.create', compact('schoolLanguages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'languages' => 'nullable|array',
            'languages.*' => 'exists:school_languages,id',
        ]);

        $schoolId = auth()->user()->school_id;
        $grade = Grade::create([
            'school_id' => $schoolId,
            'name' => $request->name,
        ]);

        if ($request->has('languages')) {
            $grade->schoolLanguages()->sync($request->languages);
        }

        return redirect()->route('school.grades.index')->with('success', 'Grade created successfully.');
    }

    public function edit(Grade $grade)
    {
        if ($grade->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        $schoolLanguages = \App\Models\SchoolLanguage::where('school_id', auth()->user()->school_id)->get();
        return view('school.grades.edit', compact('grade', 'schoolLanguages'));
    }

    public function update(Request $request, Grade $grade)
    {
        if ($grade->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'languages' => 'nullable|array',
            'languages.*' => 'exists:school_languages,id',
        ]);

        $grade->update([
            'name' => $request->name,
        ]);

        if ($request->has('languages')) {
            $grade->schoolLanguages()->sync($request->languages);
        } else {
            $grade->schoolLanguages()->detach();
        }

        return redirect()->route('school.grades.index')->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        if ($grade->school_id !== auth()->user()->school_id) {
            abort(403);
        }
        $grade->delete();
        return redirect()->route('school.grades.index')->with('success', 'Grade deleted successfully.');
    }
}
