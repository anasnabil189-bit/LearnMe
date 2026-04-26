<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Level;
use App\Models\Question;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isManager()) {
            $stats = [
                'total_lessons' => Lesson::count(),
                'total_quizzes' => Quiz::count(),
                'total_questions' => Question::count(),
                'total_levels' => Level::count(),
            ];

            $languages = \App\Models\Language::withCount([
                'levels',
                'userLanguages as courses_users_count' => function($q) {
                    $q->whereHas('user', function($u) {
                        $u->whereNull('school_id');
                    });
                }
            ])->get();

            return view('admin.dashboard_manager', compact('stats', 'languages'));
        }

        // Admin view logic
        $schoolAnnualIncome = School::where('status', 'approved')->sum('annual_subscription_fee');
        
        $coursesTotalIncome = Transaction::where('status', 'success')->sum('amount');
        $coursesMonthlyIncome = Transaction::where('status', 'success')
                                           ->whereMonth('created_at', Carbon::now()->month)
                                           ->whereYear('created_at', Carbon::now()->year)
                                           ->sum('amount');

        $stats = [
            'school_annual_income'   => $schoolAnnualIncome, 
            'courses_total_income'   => $coursesTotalIncome,
            'courses_monthly_income' => $coursesMonthlyIncome,
            'total_schools'          => School::count(),
            'total_organizations'    => \App\Models\Organization::count(),
            'individual_students'    => User::where('type', 'user')->whereNull('school_id')->count(),
            'school_students'        => User::where('type', 'user')->whereNotNull('school_id')->count(),
            'total_users'            => User::count(),
        ];

        // Breakdown by Language for both Courses (Individual) and Schools
        $languagesStats = \App\Models\Language::withCount([
            'userLanguages as courses_count' => function($q) {
                $q->whereHas('user', function($u) {
                    $u->whereNull('school_id');
                });
            },
            'userLanguages as school_count' => function($q) {
                $q->whereHas('user', function($u) {
                    $u->whereNotNull('school_id');
                });
            }
        ])->get();

        return view('admin.dashboard', compact('stats', 'languagesStats'));
    }

    public function transactions()
    {
        $transactions = Transaction::latest()->paginate(20);
        return view('admin.transactions', compact('transactions'));
    }
}
