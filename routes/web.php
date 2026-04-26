<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SchoolDashboardController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\UserLevelController;
use App\Http\Controllers\CourseDashboardController;
use App\Http\Controllers\AIFeatureController;
use App\Http\Controllers\AdminLanguageController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\StudentContentController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PaymentController;
/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Role-Based Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // AI Feedback Route (available to all authenticated users who have access to the result)
    Route::post('/ai/generate-feedback', [AIFeatureController::class, 'generateFeedback'])->name('ai.generate-feedback');
    Route::post('/ai/generate-lesson-draft', [AIFeatureController::class, 'generateLessonDraft'])->name('ai.generate-lesson-draft');
    Route::post('/results/{result}/grade-essay', [QuizController::class, 'gradeEssay'])->name('teacher.results.grade-essay');
    /*
    |--- Admin & Manager Routes ---
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Shared
        Route::middleware('role:admin,manager')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('transactions');
            Route::get('/results', [ResultController::class, 'index'])->name('results.index');
            Route::get('/results/{result}', [ResultController::class, 'show'])->name('results.show');
            Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard');
            
            // Shared Students Route
            Route::get('/students/courses', [StudentController::class, 'courseStudents'])->name('students.courses');
        });

        // Admin Only
        Route::middleware('role:admin')->group(function () {
            // Schools management
            Route::post('/schools/{school}/approve', [SchoolController::class, 'approve'])->name('schools.approve');
            Route::post('/schools/{school}/reject', [SchoolController::class, 'reject'])->name('schools.reject');
            Route::get('/schools/{school}/teachers', [TeacherController::class, 'schoolTeachers'])->name('schools.teachers');
            Route::resource('schools', SchoolController::class)->except(['create', 'store']);
            Route::resource('staff', AdminUserController::class);
            
            // Organizations
            Route::resource('organizations', \App\Http\Controllers\OrganizationController::class);
            Route::post('/organizations/{organization}/generate-code', [\App\Http\Controllers\OrganizationController::class, 'generateCode'])->name('organizations.generate-code');
            Route::post('/organizations/codes/{code}/toggle-status', [\App\Http\Controllers\OrganizationController::class, 'toggleCodeStatus'])->name('organizations.toggle-code-status');
            Route::delete('/organizations/codes/{code}', [\App\Http\Controllers\OrganizationController::class, 'destroyCode'])->name('organizations.destroy-code');

            Route::resource('teachers', TeacherController::class);
            
            // New Student Management Routes for Admin
            Route::get('/students/schools', [StudentController::class, 'schoolStudents'])->name('students.schools');
        });

        // Manager Only
        Route::middleware('role:manager')->group(function () {
            Route::resource('students', StudentController::class)->only(['show', 'edit', 'update', 'destroy']);
            Route::resource('languages', AdminLanguageController::class);
            Route::resource('levels', LevelController::class);
            Route::resource('lessons', LessonController::class);
            Route::get('/lessons/grade/{grade}/{language}', [LessonController::class, 'byGrade'])->name('lessons.by_grade');
            Route::resource('quizzes', QuizController::class);
            Route::get('/quizzes/grade/{grade}/{language}', [QuizController::class, 'byGrade'])->name('quizzes.by_grade');
            Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
            Route::post('/quizzes/{quiz}/generate-ai', [AIFeatureController::class, 'generateQuestions'])->name('quizzes.generate-ai');
            Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
            Route::resource('challenges', ChallengeController::class);
            Route::get('/levels/by-language/{language}', [LevelController::class, 'getByLanguage'])->name('levels.by_language');
        });
    });

    /*
    |--- School Dashboard ---
    */
    Route::middleware(['role:school', 'school.approved'])->prefix('school')->name('school.')->group(function () {
        Route::get('/pending-approval', function () {
            return view('school.pending');
        })->name('pending')->withoutMiddleware(['school.approved']);

        Route::get('/dashboard', [SchoolDashboardController::class, 'index'])->name('dashboard');
        
        // School-specific CRUD
        Route::resource('teachers', TeacherController::class)->names(['index' => 'teachers.index']); 
        Route::resource('students', StudentController::class)->names(['index' => 'students.index']);
        
        // Academic Structure
        Route::resource('grades', \App\Http\Controllers\SchoolGradeController::class);
        Route::resource('school-languages', \App\Http\Controllers\SchoolLanguageController::class);
        Route::resource('teacher-assignments', \App\Http\Controllers\TeacherAssignmentController::class);
        
        // Aliases for backwards compatibility
        Route::get('/students-list', [StudentController::class, 'index'])->name('students');
        Route::get('/teachers-list', [TeacherController::class, 'index'])->name('teachers');
        
        Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard');
    });

    /*
    |--- Teacher Dashboard ---
    */
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        
        // Teacher content
        Route::resource('lessons', LessonController::class);
        Route::get('/lessons/grade/{grade}/{language}', [LessonController::class, 'byGrade'])->name('lessons.by_grade');
        Route::resource('quizzes', QuizController::class);
        Route::get('/quizzes/grade/{grade}/{language}', [QuizController::class, 'byGrade'])->name('quizzes.by_grade');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::post('/quizzes/{quiz}/generate-ai', [AIFeatureController::class, 'generateQuestions'])->name('quizzes.generate-ai');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::get('/results', [ResultController::class, 'index'])->name('results.index');
        Route::get('/results/grade/{grade}/{language}', [ResultController::class, 'managedResults'])->name('results.by_grade');
        Route::get('/results/{result}', [ResultController::class, 'show'])->name('results.show');
        Route::get('/leaderboard/grade/{grade}/{language}', [\App\Http\Controllers\LeaderboardController::class, 'teacherGradeRanking'])->name('leaderboard.by_grade');
    });

    Route::middleware(['role:user'])->prefix('user')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

        Route::post('/start-trial', [\App\Http\Controllers\UserDashboardController::class, 'startTrial'])->name('subscription.start-trial');
        Route::post('/organization/join', [\App\Http\Controllers\UserOrganizationController::class, 'join'])->name('user.organization.join');
        
        // Language Routes
        Route::get('/languages', [\App\Http\Controllers\LanguageController::class, 'index'])->name('user.languages.index');
        Route::post('/languages/switch', [\App\Http\Controllers\LanguageController::class, 'switch'])->name('user.languages.switch');
        Route::post('/languages/enroll', [\App\Http\Controllers\LanguageController::class, 'enroll'])->name('user.languages.enroll');

        // Enrollment Transitions
        Route::post('/join-grade', [StudentAssignmentController::class, 'joinGrade'])->name('user.join_grade');
        Route::post('/leave-grade', [StudentAssignmentController::class, 'leaveGrade'])->name('user.leave_grade');
        Route::post('/unlock-teacher', [StudentAssignmentController::class, 'unlockTeacher'])->name('user.unlock_teacher');
        
        // Content Consumption
        Route::get('/teacher/{teacher}/subject/{language}', [StudentContentController::class, 'showTeacherContent'])->name('user.teacher_content');
        Route::get('/class-leaderboard/{teacher}/{language}', [\App\Http\Controllers\LeaderboardController::class, 'classLeaderboard'])->name('user.class_leaderboard');

        Route::middleware(['level.access'])->group(function () {
            // Courses Progression Routes
            Route::get('/courses/lesson/{lesson}', [\App\Http\Controllers\CourseDashboardController::class, 'showLesson'])->name('courses.lesson');
            Route::post('/courses/lesson/{lesson}/submit', [\App\Http\Controllers\CourseDashboardController::class, 'submitLessonQuiz'])->name('courses.lesson.submit');
            
            // Contextual Content Views
            Route::get('/levels/{level}', [UserLevelController::class, 'show'])->name('user.levels.show');
            Route::post('/levels/{level}/enroll', [UserLevelController::class, 'enroll'])->name('user.levels.enroll');

            // Detailed Views
            Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('user.lessons.show');
            Route::get('/quizzes/{quiz}/take', [QuizController::class, 'take'])->name('user.quizzes.take');
            Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('user.quizzes.submit');
            
            Route::get('/my-results', [ResultController::class, 'index'])->name('user.results.index');
            Route::get('/results/{result}', [ResultController::class, 'show'])->name('user.results.show');
            
            Route::get('/challenges', [ChallengeController::class, 'index'])->name('user.challenges.index');
            Route::get('/challenges/create', [ChallengeController::class, 'create'])->name('user.challenges.create');
            Route::post('/challenges', [ChallengeController::class, 'store'])->name('user.challenges.store');
            Route::get('/challenges/{challenge}', [ChallengeController::class, 'show'])->name('user.challenges.show');
            Route::get('/challenges/{challenge}/take', [ChallengeController::class, 'take'])->name('user.challenges.take');
            Route::post('/challenges/{challenge}/submit', [ChallengeController::class, 'submitChallenge'])->name('user.challenges.submit');
            Route::get('/challenges/{challenge}/status', [ChallengeController::class, 'status'])->name('user.challenges.status');
            Route::post('/challenges/join', [ChallengeController::class, 'join'])->name('user.challenges.join');
            Route::delete('/challenges/{challenge}', [ChallengeController::class, 'destroy'])->name('user.challenges.destroy');
            
            // Global Leaderboard
            Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard');
        });
    });

    // Paymob Payment Routes
    Route::post('/payments/checkout', [PaymentController::class, 'initiatePayment'])->name('payments.checkout');
    Route::get('/payments/success', [PaymentController::class, 'handleSuccess'])->name('payments.success');
});

// Paymob Callback (Webhook) - Must be outside auth and CSRF
Route::post('/payments/callback', [PaymentController::class, 'handleCallback'])->name('payments.callback');
