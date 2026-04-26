<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $userAuth = auth()->user();
        
        if ($userAuth->isAdmin()) {
            return $this->courseStudents();
        }

        if ($userAuth->isManager()) {
            return $this->courseStudents();
        }

        $query = User::where('type', 'user')->with(['school', 'gradesAsStudent', 'userLanguages']);
        
        if ($userAuth->isSchool() && $userAuth->school_id) {
            $query->where('school_id', $userAuth->school_id);
        }
        
        $students = $query->latest()->paginate(10);
        return view('students.index', compact('students'));
    }

    public function schoolStudents()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Only admins can view the school students master list.');
        }

        $students = User::where('type', 'user')
            ->whereNotNull('school_id')
            ->with(['school', 'gradesAsStudent', 'userLanguages'])
            ->latest()
            ->paginate(15);

        $viewTitle = 'School Students';
        $viewType = 'school';

        return view('students.index', compact('students', 'viewTitle', 'viewType'));
    }

    public function courseStudents()
    {
        $userAuth = auth()->user();
        if (!$userAuth->isAdmin() && !$userAuth->isManager()) {
            abort(403);
        }

        $students = User::where('type', 'user')
            ->whereNull('school_id')
            ->with(['gradesAsStudent', 'userLanguages'])
            ->latest()
            ->paginate(15);

        $viewTitle = 'Course Learners';
        $viewType = 'course';

        return view('students.index', compact('students', 'viewTitle', 'viewType'));
    }

    public function create()
    {
        if (auth()->user()->type === 'admin') {
            abort(403, 'غير مصرح للآدمن بإنشاء الطلاب، هذه مهمة مدير المحتوى.');
        }

        return view('students.create');
    }

    public function store(Request $request)
    {
        $userAuth = auth()->user();
        if ($userAuth->type === 'admin') {
            abort(403, 'غير مصرح للآدمن بإنشاء الطلاب، هذه مهمة مدير المحتوى.');
        }
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'الاسم مطلوب.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.unique'       => 'البريد الإلكتروني مسجل مسبقًا.',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'type'      => 'user',
            'school_id' => auth()->user()->isSchool() ? auth()->user()->school_id : null,
        ]);


        $routePrefix = in_array($userAuth->type, ['admin', 'manager']) ? 'admin' : 'school';
        return redirect()->route($routePrefix . '.students.index')->with('success', 'تم إضافة الطالب بنجاح.');
    }

    public function show(User $student)
    {
        if ($student->type !== 'user') abort(404);
        $student->load(['gradesAsStudent.school', 'quizAttempts.quiz']);
        return view('students.show', compact('student'));
    }

    public function edit(User $student)
    {
        if ($student->type !== 'user') abort(404);
        if (auth()->user()->type === 'admin') {
            abort(403, 'غير مصرح للإدارة بتعديل بيانات الطلاب.');
        }
        if (auth()->user()->type === 'school') {
            abort(403, 'غير مصرح للمدرسة بتعديل بيانات الطالب.');
        }
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        if ($student->type !== 'user') abort(404);
        if (auth()->user()->type === 'admin') {
            abort(403, 'غير مصرح للآدمن بتعديل الطلاب، هذه مهمة مدير المحتوى.');
        }
        if (auth()->user()->type === 'school') {
            abort(403, 'غير مصرح للمدرسة بتعديل بيانات الطالب.');
        }
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
        ], [
            'email.unique' => 'البريد الإلكتروني هذا مستخدم بالفعل لحساب آخر.'
        ]);

        $student->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        $userAuth = auth()->user();
        $routePrefix = in_array($userAuth->type, ['admin', 'manager']) ? 'admin' : 'school';
        return redirect()->route($routePrefix . '.students.index')->with('success', 'تم التحديث بنجاح.');
    }

    public function destroy(User $student)
    {
        if ($student->type !== 'user') abort(404);
        if (auth()->user()->type === 'admin' && $student->school_id !== null) {
            abort(403, 'غير مصرح للإدارة بحذف طلاب المدارس.');
        }
        if (auth()->user()->type === 'school' && $student->school_id !== auth()->user()->school_id) {
            abort(403, 'غير مصرح لك بحذف طالب خارج مدرستك.');
        }
        $student->delete();
        $routePrefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'school';
        return redirect()->route($routePrefix . '.students.index')->with('success', 'تم حذف الطالب بنجاح.');
    }
}
