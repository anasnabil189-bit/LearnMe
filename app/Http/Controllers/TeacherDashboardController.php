<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Quiz;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->type !== 'teacher') {
            return view('teacher.dashboard', ['teacher' => null, 'stats' => [], 'recentResults' => collect()]);
        }

        // 1. Assignments
        $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
            ->with(['grade', 'schoolLanguage'])
            ->get();

        // 2. Content Stats (via user_id)
        $lessonIds = \App\Models\Lesson::where('user_id', $user->id)->pluck('id');
        $quizIds   = \App\Models\Quiz::where('user_id', $user->id)->pluck('id');

        // 3. Performance Stats
        $avg_score = 0;
        $allCompletedAttempts = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
            ->where('status', 'completed')
            ->get();

        if ($allCompletedAttempts->count() > 0) {
            $totalEarned = $allCompletedAttempts->sum('score');
            $totalPossible = $allCompletedAttempts->sum('total_points');
            $avg_score = $totalPossible > 0 ? round(($totalEarned / $totalPossible) * 100, 1) : 0;
        }

        // 4. Student Count (Students who unlocked this teacher)
        $studentsCount = $user->studentsViaUnlock()->count();

        $stats = [
            'teaching_grades' => $teacherAssignments->count(),
            'students'        => $studentsCount,
            'lessons'         => $lessonIds->count(),
            'quizzes'         => $quizIds->count(),
            'avg_score'       => $avg_score,
        ];

        // 5. Teacher Leaderboard
        $teacherStudents = $user->studentsViaUnlock()->get();
        $studentIds = $teacherStudents->pluck('id');
        
        $studentScores = \App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)
            ->whereIn('user_id', $studentIds)
            ->where('status', 'completed')
            ->selectRaw('user_id, SUM(score) as total_score')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $leaderboard = $teacherStudents->map(function($student) use ($studentScores) {
            $student->total_teacher_xp = $studentScores->has($student->id) ? $studentScores[$student->id]->total_score : 0;
            return $student;
        })->filter(function($student) {
            return $student->total_teacher_xp > 0;
        })->sortByDesc('total_teacher_xp')->values();

        $topStudent = $leaderboard->first();

        return view('teacher.dashboard', compact('user', 'stats', 'teacherAssignments', 'leaderboard', 'topStudent'));
    }
}
