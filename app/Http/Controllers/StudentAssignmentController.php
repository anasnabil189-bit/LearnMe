<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentAssignmentController extends Controller
{
    /**
     * Join a grade using a Grade Code.
     */
    public function joinGrade(Request $request)
    {
        $request->validate([
            'grade_code' => 'required|string|exists:grades,code',
        ], [
            'grade_code.exists' => 'كود المرحلة الدراسية غير صحيح.',
        ]);

        $user = auth()->user();
        $grade = Grade::where('code', $request->grade_code)->firstOrFail();

        // Check if grade belongs to student's school
        if ($user->school_id && $grade->school_id !== $user->school_id) {
            return back()->with('error', 'هذه المرحلة الدراسية لا تتبع لمدرستك.');
        }

        // If not in school, link them to the school of the grade
        if (!$user->school_id) {
            $user->update(['school_id' => $grade->school_id]);
        }

        // Check if already in a grade (user said only one grade at a time)
        if ($user->gradesAsStudent()->count() > 0) {
            return back()->with('error', 'أنت مسجل بالفعل في مرحلة دراسية. يجب الخروج منها أولاً لتسجيل في مرحلة أخرى.');
        }

        $user->gradesAsStudent()->attach($grade->id);

        return back()->with('success', 'تم الانضمام للمرحلة الدراسية ' . $grade->name . ' بنجاح.');
    }

    /**
     * Leave the current grade to join another one.
     */
    public function leaveGrade()
    {
        $user = auth()->user();
        $user->gradesAsStudent()->detach();
        
        // Also clear unlocked teachers for that school/grade context? 
        // User said: "عند الانتهاء من المرحلة الدراسية... يريد ان يسجل في المرحلة الدراسية التالية يكون هناك امكانية الخروج"
        // Usually clearing teachers makes sense if they are grade-specific.
        $user->unlockedTeachers()->detach();

        return back()->with('success', 'تم الخروج من المرحلة الدراسية الحالية. يمكنك الآن التسجيل في مرحلة جديدة.');
    }

    /**
     * Unlock a teacher's content using a Teacher Code.
     */
    public function unlockTeacher(Request $request)
    {
        $request->validate([
            'teacher_code' => 'required|string|exists:users,teacher_code',
        ], [
            'teacher_code.exists' => 'كود المعلم غير صحيح.',
        ]);

        $user = auth()->user();
        $teacher = User::where('teacher_code', $request->teacher_code)->where('type', 'teacher')->firstOrFail();

        // Check if teacher belongs to same school
        if ($user->school_id && $teacher->school_id !== $user->school_id) {
            return back()->with('error', 'هذا المعلم لا يتبع لمدرستك.');
        }

        // Check if already unlocked
        if ($user->unlockedTeachers()->where('teacher_id', $teacher->id)->exists()) {
            return back()->with('info', 'لقد قمت بالفعل بفك قفل محتوى هذا المعلم.');
        }

        $user->unlockedTeachers()->attach($teacher->id);

        return back()->with('success', 'تم فك قفل محتوى المعلم ' . $teacher->name . ' بنجاح.');
    }
}
