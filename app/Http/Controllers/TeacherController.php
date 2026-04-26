<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->type === 'admin') {
            $schools = School::withCount('teachers')->latest()->paginate(10);
            return view('teachers.index', compact('schools'));
        }

        $teacherQuery = User::where('type', 'teacher')->with(['school']);
        
        if ($user->type === 'school' && $user->school) {
            $teacherQuery->where('school_id', $user->school->id);
        }
        
        $teachers = $teacherQuery->latest()->paginate(10);
        return view('teachers.index', compact('teachers'));
    }

    public function schoolTeachers(School $school)
    {
        if (auth()->user()->type !== 'admin') {
            abort(403);
        }

        $teachers = User::where('type', 'teacher')
            ->where('school_id', $school->id)
            ->latest()
            ->paginate(15);

        return view('teachers.index', compact('teachers', 'school'));
    }

    public function create()
    {
        if (auth()->user()->type === 'admin') {
            abort(403, 'غير مصرح للإدارة بإنشاء حسابات معلمين بشكل مباشر.');
        }

        $schools = School::orderBy('name')->get();
        return view('teachers.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $userAuth = auth()->user();
        
        if ($userAuth->type === 'admin') {
            abort(403, 'غير مصرح للإدارة بإنشاء حسابات معلمين بشكل مباشر.');
        }

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
        ];

        $request->validate($rules, [
            'name.required'    => 'الاسم مطلوب.',
            'email.required'   => 'البريد الإلكتروني مطلوب.',
            'email.unique'     => 'البريد الإلكتروني مسجل مسبقًا.',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين.',
            'school_id.required' => 'يجب ربط المعلم بمدرسة.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'type'      => 'teacher',
            'school_id' => $userAuth->type === 'school' ? $userAuth->school_id : $request->school_id,
        ]);

        $routePrefix = $userAuth->type === 'admin' ? 'admin' : 'school';
        return redirect()->route($routePrefix . '.teachers.index')->with('success', 'تم إضافة المعلم بنجاح.');
    }

    public function show(User $teacher)
    {
        if ($teacher->type !== 'teacher') abort(404);
        $teacher->load(['school', 'teacherAssignments.grade', 'teacherAssignments.schoolLanguage']);
        return view('teachers.show', compact('teacher'));
    }

    public function edit(User $teacher)
    {
        if (auth()->user()->type === 'admin') abort(403, 'غير مصرح للإدارة بتعديل بيانات المعلمين.');
        if (auth()->user()->type === 'school' && $teacher->school_id !== auth()->user()->school_id) {
            abort(403, 'غير مصرح لك بتعديل بيانات معلم خارج مدرستك.');
        }
        if ($teacher->type !== 'teacher') abort(404);
        $schools = School::orderBy('name')->get();
        return view('teachers.edit', compact('teacher', 'schools'));
    }

    public function update(Request $request, User $teacher)
    {
        if (auth()->user()->type === 'admin') abort(403, 'غير مصرح للإدارة بتعديل بيانات المعلمين.');
        if (auth()->user()->type === 'school' && $teacher->school_id !== auth()->user()->school_id) {
            abort(403, 'غير مصرح لك بتعديل بيانات معلم خارج مدرستك.');
        }
        if ($teacher->type !== 'teacher') abort(404);
        $userAuth = auth()->user();
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $teacher->id,
        ];
        
        if ($userAuth->type === 'admin') {
            $rules['school_id'] = 'required|exists:schools,id';
        }

        $request->validate($rules, [
            'email.unique' => 'البريد الإلكتروني هذا مستخدم بالفعل لمعلم آخر.'
        ]);

        $teacher->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'school_id' => $userAuth->type === 'admin' ? $request->school_id : $teacher->school_id,
        ]);
        
        $routePrefix = $userAuth->type === 'admin' ? 'admin' : 'school';
        return redirect()->route($routePrefix . '.teachers.index')->with('success', 'تم تحديث المعلم بنجاح.');
    }

    public function destroy(User $teacher)
    {
        if (auth()->user()->type === 'admin') abort(403, 'غير مصرح للإدارة بحذف المعلمين.');
        if (auth()->user()->type === 'school' && $teacher->school_id !== auth()->user()->school_id) {
            abort(403, 'غير مصرح لك بحذف معلم خارج مدرستك.');
        }
        if ($teacher->type !== 'teacher') abort(404);
        $teacher->delete();
        $routePrefix = auth()->user()->type === 'admin' ? 'admin' : 'school';
        return redirect()->route($routePrefix . '.teachers.index')->with('success', 'تم حذف المعلم بنجاح.');
    }
}
