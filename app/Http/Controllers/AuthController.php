<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use App\Models\Organization;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.email'       => 'البريد الإلكتروني غير صالح.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min'      => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectBasedOnRole();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:6|confirmed',
            'reg_type'          => 'required|in:self,school,school_owner',
            'student_limit'     => 'required_if:reg_type,school_owner|nullable|integer|min:1',
            'phone_number'      => 'required_if:reg_type,school_owner|nullable|string|regex:/^01[0125][0-9]{8}$/',
            'payment_method'    => 'required_if:reg_type,school_owner|nullable|in:card,wallet,fawry',
            'school_code'       => 'required_if:reg_type,school|nullable|string|exists:schools,code',
            'grade_code'        => 'required_if:reg_type,school|nullable|string|exists:grades,code',
        ], [
            'name.required'             => 'الاسم مطلوب.',
            'email.required'            => 'البريد الإلكتروني مطلوب.',
            'email.unique'              => 'هذا البريد الإلكتروني مسجل بالفعل.',
            'password.confirmed'        => 'كلمتا المرور غير متطابقتين.',
            'student_limit.required_if' => 'يرجى تحديد عدد الطلاب للمدرسة.',
            'student_limit.min'         => 'يجب أن يكون عدد الطلاب 1 على الأقل.',
            'school_code.required_if'   => 'كود المدرسة مطلوب عند اختيار التعلم مع مدرسة.',
            'school_code.exists'        => 'كود المدرسة المدخل غير صحيح.',
            'grade_code.required_if'    => 'كود المرحلة الدراسية مطلوب.',
            'grade_code.exists'         => 'كود المرحلة الدراسية المدخل غير صحيح.',
        ]);

        $userType = $request->reg_type === 'school_owner' ? 'school' : 'user';
        $schoolId = null;
        $gradeId = null;

        if ($request->reg_type === 'school') {
            $school = \App\Models\School::where('code', $request->school_code)->first();
            if ($school) {
                if ($school->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'school_code' => 'هذه المدرسة غير نشطة حالياً.',
                    ]);
                }
                
                if ($school->students()->count() >= $school->student_limit) {
                    throw ValidationException::withMessages([
                        'school_code' => 'عذراً، هذه المدرسة وصلت للحد الأقصى من الطلاب المسموح بهم.',
                    ]);
                }
                $schoolId = $school->id;
                
                $grade = \App\Models\Grade::where('code', $request->grade_code)->where('school_id', $schoolId)->first();
                if (!$grade) {
                    throw ValidationException::withMessages([
                        'grade_code' => 'كود المرحلة الدراسية المدخل لا يتبع لهذه المدرسة.',
                    ]);
                }
                $gradeId = $grade->id;
            }
        } elseif ($request->reg_type === 'school_owner') {
            $studentLimit = (int) $request->student_limit;
            $pricePerStudent = 20;
            $annualFee = $studentLimit * $pricePerStudent;

            $school = \App\Models\School::create([
                'name' => $request->name,
                'status' => 'pending',
                'plan_type' => 'dynamic',
                'student_limit' => $studentLimit,
                'annual_subscription_fee' => $annualFee,
                'subscription_start' => now(),
                'subscription_end' => now()->addYear(),
            ]);
            $schoolId = $school->id;
        }

        // Create new user
        $user = User::create([
            'name'      => $request->name . ($userType === 'school' ? ' (Admin)' : ''),
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'type'      => $userType,
            'school_id' => $schoolId,
            'phone_number' => $request->phone_number,
            'subscription_tier' => 'free', // Always start as free until payment
        ]);

        if ($gradeId) {
            $user->gradesAsStudent()->attach($gradeId);
        }

        Auth::login($user);

        // Immediate Payment Redirection for School Owner
        if ($request->reg_type === 'school_owner' && $request->filled('student_limit')) {
            try {
                $paymob = app(PaymobService::class);
                $price = $request->student_limit * 20;

                $paymentData = $paymob->initiatePayment(
                    $price,
                    $user,
                    'school',
                    $request->payment_method,
                    null
                );

                if (isset($paymentData['fawry_code'])) {
                    return $this->redirectBasedOnRole()
                        ->with('fawry_code', $paymentData['fawry_code'])
                        ->with('info', 'تم إنشاء طلب الدفع الخاص بالمدرسة! يرجى الدفع عند أي منفذ فوري باستخدام الكود أدناه لتفعيل المدرسة.');
                }

                if (isset($paymentData['redirect_url'])) {
                    return redirect($paymentData['redirect_url']);
                }
                
                Log::error('Paymob redirect_url missing in paymentData for school', ['data' => $paymentData]);
            } catch (\Exception $e) {
                Log::error('Paymob Initialization Exception (School): ' . $e->getMessage(), [
                    'user_id' => $user->id,
                ]);
                return $this->redirectBasedOnRole()->with('error', 'تم إنشاء حساب المدرسة بنجاح، ولكن حدث خطأ في تهيئة الدفع: ' . $e->getMessage());
            }
        }

        return $this->redirectBasedOnRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function redirectBasedOnRole()
    {
        $user = Auth::user();

        if ($user->type === 'school') {
            $school = $user->school;
            if (!$school || $school->status !== 'approved') {
                return redirect()->route('school.pending');
            }
            return redirect()->route('school.dashboard')->with('success', 'مرحبًا بك، إدارة المدرسة!');
        }

        return match ($user->type) {
            'admin'   => redirect()->route('admin.dashboard')->with('success', 'مرحبًا بك، مدير النظام!'),
            'manager' => redirect()->route('admin.dashboard')->with('success', 'مرحبًا بك، مدير المحتوى!'),
            'teacher' => redirect()->route('teacher.dashboard')->with('success', 'مرحبًا بك، معلم!'),
            'user'    => redirect()->route('user.dashboard')->with('success', 'مرحبًا بك، الطالب!'),
            default   => redirect('/'),
        };
    }
}
