<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchoolDashboardController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;

        if (!$school) {
            return redirect()->route('home')->with('error', 'No school data associated with this account.');
        }

        // 1. Teachers Stats
        $teachersData = [];
        $allTeachers = $school->teachers()->get();
        
        foreach ($allTeachers as $teacher) {
            // Count distinct grades instead of total assignments
            $assignmentsCount = \App\Models\TeacherAssignment::where('teacher_id', $teacher->id)->distinct('grade_id')->count('grade_id');
            
            // Count students who unlocked this teacher
            $uniqueStudentsCount = $teacher->studentsViaUnlock()->count();

            // Get taught languages
            $languages = $teacher->languagesTaught()->pluck('name')->toArray();

            $teachersData[] = [
                'id'                => $teacher->id,
                'name'              => $teacher->name,
                'languages'         => !empty($languages) ? implode(', ', $languages) : 'Unassigned',
                'assignments_count' => $assignmentsCount,
                'students_count'    => $uniqueStudentsCount,
            ];
        }

        // 2. Global School Stats
        $totalStudentsCount = $school->students()->count();
        $totalTeachersCount = $allTeachers->count();

        $stats = [
            'students_count'  => $totalStudentsCount,
            'teachers_count'  => $totalTeachersCount,
        ];

        return view('school.dashboard', compact('school', 'stats', 'teachersData'));
    }

    public function leaderboard()
    {
        $school = auth()->user()->school;
        
        if (!$school) {
            return redirect()->route('home')->with('error', 'No school data associated with this account.');
        }

        // Sort students by XP points (Sum of all language-specific XP + Challenge XP)
        $students = $school->students()
            ->with('userLanguages')
            ->get()
            ->sortByDesc(function($student) {
                $totalLearningXp = $student->userLanguages->sum('learning_xp');
                return $totalLearningXp + ($student->challenge_xp ?? 0);
            });

        return view('school.leaderboard', compact('school', 'students'));
    }
}
