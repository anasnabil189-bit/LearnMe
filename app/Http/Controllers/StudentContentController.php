<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\SchoolLanguage;
use App\Models\Lesson;
use App\Models\Quiz;

class StudentContentController extends Controller
{
    /**
     * Display the unlocked teacher's content for the student's grade and subject.
     */
    public function showTeacherContent(User $teacher, SchoolLanguage $language)
    {
        $user = auth()->user();

        // 1. Security Check: Is teacher unlocked?
        if (!$user->unlockedTeachers()->where('teacher_id', $teacher->id)->exists()) {
            return redirect()->route('user.dashboard')->with('error', 'يجب فك قفل محتوى هذا المعلم أولاً باستخدام الكود الخاص به.');
        }

        // 2. Get student's current grade
        $myGrade = $user->gradesAsStudent()->first();
        if (!$myGrade) {
            return redirect()->route('user.dashboard')->with('error', 'يجب الانضمام لمرحلة دراسية أولاً.');
        }

        // 3. Fetch Lessons with their quizzes
        $lessons = Lesson::where('user_id', $teacher->id)
            ->where('grade_id', $myGrade->id)
            ->where('school_language_id', $language->id)
            ->with(['quizzes' => function($q) use ($teacher, $myGrade, $language) {
                $q->where('user_id', $teacher->id)
                  ->where('grade_id', $myGrade->id)
                  ->where('school_language_id', $language->id)
                  ->where('academic_type', 'lesson');
            }])
            ->orderBy('order')
            ->get();

        // 4. Fetch General Quizzes (not linked to a specific lesson)
        $generalQuizzes = Quiz::where('user_id', $teacher->id)
            ->where('grade_id', $myGrade->id)
            ->where('school_language_id', $language->id)
            ->where('academic_type', 'general')
            ->get();

        return view('user.teacher_content', [
            'teacher' => $teacher,
            'language' => $language,
            'grade' => $myGrade,
            'lessons' => $lessons,
            'generalQuizzes' => $generalQuizzes,
        ]);
    }
}
