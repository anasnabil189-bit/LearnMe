<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentEnrollmentController extends Controller
{
    /**
     * Join a school using its school code.
     */
    public function joinSchool(Request $request)
    {
        $request->validate([
            'school_code' => 'required|string|exists:schools,code',
        ], [
            'school_code.required' => 'School code is required.',
            'school_code.exists'   => 'Incorrect school code.',
        ]);

        $user = Auth::user();
        if ($user->type !== 'user') {
            return back()->with('error', 'Sorry, your account must be a student type to join a school.');
        }

        $school = School::where('code', $request->school_code)->withCount('students')->first();
        
        // Check if school has reached its limit
        if ($school->students_count >= $school->student_limit) {
            return back()->with('error', 'Sorry, this school has reached its maximum student limit.');
        }

        // Check if already joined
        if ($user->school_id === $school->id) {
            return back()->with('info', 'You have already joined this school.');
        }

        $user->update(['school_id' => $school->id]);

        return back()->with('success', 'Joined school ' . $school->name . ' successfully!');
    }

    /**
     * Join a grade using its code.
     */
    public function joinClass(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string|exists:grades,code',
        ], [
            'class_code.required' => 'Grade code is required.',
            'class_code.exists'   => 'Incorrect grade code.',
        ]);

        $user = Auth::user();
        if ($user->type !== 'user') {
            return back()->with('error', 'Sorry, your account must be a student type to join a grade.');
        }

        $grade = Grade::where('code', $request->class_code)->first();

        // Check if already in grade
        if ($user->gradesAsStudent()->where('grade_id', $grade->id)->exists()) {
            return back()->with('info', 'You are already registered in this grade.');
        }

        $user->gradesAsStudent()->attach($grade->id);

        return back()->with('success', 'Joined grade ' . $grade->name . ' successfully!');
    }
}
