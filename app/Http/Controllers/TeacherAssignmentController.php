<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\SchoolLanguage;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        // Get all teachers in this school with their assignments
        $teachers = User::where('school_id', $schoolId)
                    ->where('type', 'teacher')
                    ->with(['teacherAssignments.grade', 'teacherAssignments.schoolLanguage'])
                    ->get();

        return view('school.teacher_assignments.index', compact('teachers'));
    }

    public function create()
    {
        $schoolId = auth()->user()->school_id;
        $teachers = User::where('school_id', $schoolId)->where('type', 'teacher')->get();
        $grades = Grade::where('school_id', $schoolId)->with('schoolLanguages')->get();
        
        return view('school.teacher_assignments.create', compact('teachers', 'grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'grade_language' => 'required|array',
            'grade_language.*' => 'string' // Format: grade_id|language_id
        ]);

        $teacher = User::where('id', $request->teacher_id)->where('school_id', auth()->user()->school_id)->firstOrFail();

        // Clear existing assignments for this teacher
        TeacherAssignment::where('teacher_id', $teacher->id)->delete();

        foreach ($request->grade_language as $gl) {
            list($gradeId, $langId) = explode('|', $gl);
            
            // Validate that grade and language belong to this school
            $grade = Grade::where('id', $gradeId)->where('school_id', auth()->user()->school_id)->first();
            $lang = SchoolLanguage::where('id', $langId)->where('school_id', auth()->user()->school_id)->first();

            if ($grade && $lang) {
                TeacherAssignment::create([
                    'teacher_id' => $teacher->id,
                    'grade_id' => $grade->id,
                    'school_language_id' => $lang->id,
                ]);
            }
        }

        return redirect()->route('school.teacher-assignments.index')->with('success', 'Teacher assignments updated successfully.');
    }
}
