<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Level;
use App\Models\Answer;
use App\Models\Xp;
use App\Models\Language;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    protected $quizService;

    public function __construct(QuizService $quizService)
    {
        $this->quizService = $quizService;
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $user->type;
        $prefix = in_array($type, ['admin', 'manager']) ? 'admin' : ($type === 'teacher' ? 'teacher' : 'user');

        if ($type === 'teacher') {
            $assignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->with(['grade', 'schoolLanguage'])
                ->get()
                ->map(function($assignment) use ($user) {
                    $assignment->quizzes_count = Quiz::where('user_id', $user->id)
                        ->where('grade_id', $assignment->grade_id)
                        ->where('school_language_id', $assignment->school_language_id)
                        ->count();
                    return $assignment;
                });
            return view('quizzes.index', compact('assignments', 'prefix'));
        } elseif ($type === 'user') {
            // Students see global quizzes OR those from unlocked teachers
            $unlockedTeacherIds = $user->unlockedTeachers()->pluck('teacher_id');
            
            $quizzes = Quiz::where('is_global', true)
                ->orWhere(function($query) use ($unlockedTeacherIds) {
                    $query->where('is_global', false)->whereIn('user_id', $unlockedTeacherIds);
                })
                ->with(['level', 'grade', 'schoolLanguage'])
                ->withCount('questions')
                ->latest()->paginate(10);
            return view('quizzes.index', compact('quizzes', 'prefix'));
        } else {
            // Admin sees global admin content grouped by language first
            if (!$request->has('language_id')) {
                $languages = Language::withCount(['quizzes' => function ($query) {
                    $query->where('is_global', true);
                }, 'userLanguages' => function ($query) {
                    $query->whereHas('user', function($q) {
                        $q->where('type', 'user')->whereNull('school_id');
                    });
                }])->get();
                
                return view('admin.shared.languages', [
                    'languages' => $languages,
                    'pageTitle' => 'Quiz Bank',
                    'entityType' => 'Quizzes',
                    'entityCountAttr' => 'quizzes_count',
                    'manageRouteName' => 'admin.quizzes.index'
                ]);
            }

            $quizzes = Quiz::where('is_global', true)
                ->where('language_id', $request->language_id)
                ->with(['level'])
                ->withCount('questions')
                ->latest()->paginate(10);
            return view('quizzes.index', compact('quizzes', 'prefix'));
        }
    }

    public function byGrade(\App\Models\Grade $grade, \App\Models\SchoolLanguage $language)
    {
        $user = Auth::user();
        $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
            ->where('grade_id', $grade->id)
            ->where('school_language_id', $language->id)
            ->exists();
            
        if (!$isAssigned && !in_array($user->type, ['admin', 'manager'])) abort(403);

        $quizzes = Quiz::where('user_id', $user->id)
            ->where('grade_id', $grade->id)
            ->where('school_language_id', $language->id)
            ->withCount('questions')
            ->latest()
            ->paginate(15);

        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return view('quizzes.by_grade', compact('quizzes', 'grade', 'language', 'prefix'));
    }

    public function create()
    {
        $user = Auth::user();
        $languages = Language::all();
        $lessons = collect([]);
        if ($user->type === 'teacher') {
            $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)->with(['grade', 'schoolLanguage'])->get();
            $lessons = \App\Models\Lesson::where('user_id', $user->id)->get();
            $classes = collect([]);
            $levels  = collect([]);
        } else {
            $teacherAssignments = collect([]);
            $classes = collect([]);
            $levels  = Level::orderBy('required_xp')->get();
            $lessons = \App\Models\Lesson::where('is_global', true)->get();
        }
        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return view('quizzes.create', compact('classes', 'levels', 'prefix', 'languages', 'teacherAssignments', 'lessons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'level_id'    => 'nullable|exists:levels,id',
            'language_id' => 'nullable|exists:languages,id',
            'academic_type'=> 'nullable|in:general,lesson',
            'lesson_id'   => 'nullable|exists:lessons,id',
        ], [
            'title.required' => 'Quiz title is required.',
            'language_id.required' => 'Please select a language.',
        ]);

        $user = auth()->user();
        $sourceType = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';

        if (in_array($user->type, ['admin', 'manager'])) {
            if (!$request->level_id) {
                return back()->withErrors(['level_id' => 'Level must be selected.'])->withInput();
            }
            if (!$request->language_id) {
                return back()->withErrors(['language_id' => 'Please select a language.'])->withInput();
            }
            $data = $request->only('title', 'level_id', 'language_id');
            $data['grade_id'] = null;
            $data['school_language_id'] = null;
            $data['academic_type'] = $request->input('academic_type', 'general');
            $data['lesson_id'] = $data['academic_type'] === 'lesson' ? $request->input('lesson_id') : null;
            $data['is_global'] = true;
        } else {
            if (!$request->grade_language) return back()->withErrors(['grade_language' => 'Grade and Language must be selected.'])->withInput();
            
            list($gradeId, $schoolLanguageId) = explode('|', $request->grade_language);
            
            // Validate teacher assignment
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('grade_id', $gradeId)
                ->where('school_language_id', $schoolLanguageId)
                ->exists();
                
            if (!$isAssigned) {
                return back()->withErrors(['grade_language' => 'You are not assigned to this class.'])->withInput();
            }

            $data = $request->only('title');
            $data['grade_id'] = $gradeId;
            $data['school_language_id'] = $schoolLanguageId;
            // Always set a default academic_type for teachers if they don't specify
            $data['academic_type'] = $request->input('academic_type', 'general');
            $data['lesson_id'] = $data['academic_type'] === 'lesson' ? $request->input('lesson_id') : null;
            $data['level_id'] = null;
            $data['is_global'] = false;
        }

        $data['source_type'] = $sourceType;
        $data['user_id']     = $user->id;

        $quiz = Quiz::create($data);

        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.quizzes.show', $quiz)->with('success', 'Quiz created successfully. You can now add questions.');
    }

    public function show(Quiz $quiz)
    {
        $user = Auth::user();
        if ($user->type === 'teacher') {
            if ($quiz->user_id !== $user->id && !$quiz->is_global) {
                abort(403);
            }
        }

        $quiz->load(['questions.answers', 'grade', 'level']);
        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : ($user->type === 'teacher' ? 'teacher' : 'user');
        return view('quizzes.show', compact('quiz', 'prefix'));
    }

    public function edit(Quiz $quiz)
    {
        $user = Auth::user();
        if ($user->type === 'teacher') {
            if ($quiz->is_global) abort(403);
            
            if ($quiz->grade_id && $quiz->school_language_id) {
                $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                    ->where('grade_id', $quiz->grade_id)
                    ->where('school_language_id', $quiz->school_language_id)
                    ->exists();
                if (!$isAssigned) abort(403);
            }
            
            $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)->with(['grade', 'schoolLanguage'])->get();
            $lessons = \App\Models\Lesson::where('user_id', $user->id)->get();
            $classes = collect([]);
            $levels  = collect([]);
        } else {
            $teacherAssignments = collect([]);
            $classes = collect([]);
            $levels  = Level::orderBy('required_xp')->get();
            $lessons = \App\Models\Lesson::where('is_global', true)->get();
        }

        $languages = Language::all();

        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return view('quizzes.edit', compact('quiz', 'classes', 'levels', 'prefix', 'languages', 'teacherAssignments', 'lessons'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'level_id'    => 'nullable|exists:levels,id',
            'language_id' => 'required|exists:languages,id',
            'academic_type'=> 'nullable|in:general,lesson',
            'lesson_id'   => 'nullable|exists:lessons,id',
        ]);

        $sourceType = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher';

        if ($sourceType === 'admin') {
            if (!$request->level_id) {
                return back()->withErrors(['level_id' => 'Level must be selected.'])->withInput();
            }
            $data = $request->only('title', 'level_id', 'language_id');
            $data['grade_id'] = null;
            $data['school_language_id'] = null;
            $data['academic_type'] = $request->input('academic_type', 'general');
            $data['lesson_id'] = $data['academic_type'] === 'lesson' ? $request->input('lesson_id') : null;
        } else {
            if (!$request->grade_language) return back()->withErrors(['grade_language' => 'Grade and Language must be selected.'])->withInput();
            
            list($gradeId, $schoolLanguageId) = explode('|', $request->grade_language);
            
            // Validate teacher assignment
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', auth()->id())
                ->where('grade_id', $gradeId)
                ->where('school_language_id', $schoolLanguageId)
                ->exists();
                
            if (!$isAssigned) {
                return back()->withErrors(['grade_language' => 'You are not assigned to this class.'])->withInput();
            }

            $data = $request->only('title');
            $data['grade_id'] = $gradeId;
            $data['school_language_id'] = $schoolLanguageId;
            $data['academic_type'] = $request->input('academic_type', 'general');
            $data['lesson_id'] = $data['academic_type'] === 'lesson' ? $request->input('lesson_id') : null;
            $data['level_id'] = null;
        }

        $data['source_type'] = $sourceType;

        $quiz->update($data);

        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.quizzes.show', $quiz)->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    public function take(Quiz $quiz)
    {
        $user = Auth::user();
        if ($user->type === 'user') {
            $canAccess = false;
            
            if ($quiz->is_global) {
                $canAccess = true;
            } elseif ($quiz->quiz_type === 'challenge') {
                $canAccess = true;
            } elseif ($user->unlockedTeachers()->where('teacher_id', $quiz->user_id)->exists()) {
                $canAccess = true;
            }
            
            if (!$canAccess) {
                return redirect()->route('user.dashboard')->with('error', 'يجب فك قفل محتوى هذا المعلم أولاً لمشاهدة هذا الاختبار.');
            }

            // Check free limits
            if ($user->isFree()) {
                $today = now()->toDateString();
                
                if ($quiz->academic_type === 'lesson') {
                    $alreadyAccessed = \App\Models\DailyUserUsage::where('user_id', $user->id)
                        ->where('usage_type', 'lesson')
                        ->where('item_id', $quiz->lesson_id)
                        ->where('usage_date', $today)
                        ->exists();

                    if (!$alreadyAccessed) {
                        $todaysLessonsCount = \App\Models\DailyUserUsage::where('user_id', $user->id)
                            ->where('usage_type', 'lesson')
                            ->where('usage_date', $today)
                            ->count();

                        if ($todaysLessonsCount >= 3) {
                            return back()->with('show_subscription_modal', true)->with('error', 'لقد استنفذت الحد اليومي المجاني للدروس واختباراتها (3). يرجى الترقية للمتابعة.');
                        }
                        
                        if ($quiz->lesson_id) {
                            \App\Models\DailyUserUsage::create([
                                'user_id' => $user->id,
                                'usage_type' => 'lesson',
                                'item_id' => $quiz->lesson_id,
                                'usage_date' => $today,
                            ]);
                        }
                    }
                } else {
                    $alreadyAccessed = \App\Models\DailyUserUsage::where('user_id', $user->id)
                        ->where('usage_type', 'comprehensive_quiz')
                        ->where('item_id', $quiz->id)
                        ->where('usage_date', $today)
                        ->exists();

                    if (!$alreadyAccessed) {
                        $todaysExamsCount = \App\Models\DailyUserUsage::where('user_id', $user->id)
                            ->where('usage_type', 'comprehensive_quiz')
                            ->where('usage_date', $today)
                            ->count();

                        if ($todaysExamsCount >= 1) {
                            return back()->with('show_subscription_modal', true)->with('error', 'لقد استنفذت الحد اليومي المجاني للاختبارات العامة (1). يرجى الترقية للمتابعة.');
                        }

                        \App\Models\DailyUserUsage::create([
                            'user_id' => $user->id,
                            'usage_type' => 'comprehensive_quiz',
                            'item_id' => $quiz->id,
                            'usage_date' => $today,
                        ]);
                    }
                }
            }
        }

        $quiz->load(['questions.answers']);
        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : ($user->type === 'teacher' ? 'teacher' : 'user');
        return view('quizzes.take', compact('quiz', 'prefix'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = Auth::user();
        if ($user->type !== 'user') {
            return redirect()->route('dashboard')->with('error', 'يجب أن تكون طالبًا لتقديم الاختبار.');
        }

        $quiz->load(['questions.answers']);
        $totalQuestions = $quiz->questions->count();
        $correct = 0;
        $totalPointsPossible = 0;
        $earnedPoints = 0;
        $needsGrading = false;

        $details = [];

        foreach ($quiz->questions as $question) {
            $isCorrect = false;
            $studentAnswerText = null;
            $correctAnswerText = null;
            $aiFeedback = null;
            $scoreForThisQuestion = 0;
            $questionPoints = $question->points ?? 1;
            $totalPointsPossible += $questionPoints;

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $selectedAnswerId = $request->input('answers.' . $question->id);
                $correctAnswerObj = $question->answers->where('is_correct', true)->first();
                $correctAnswerText = $correctAnswerObj ? $correctAnswerObj->answer : '';

                if ($selectedAnswerId) {
                    $answer = Answer::find($selectedAnswerId);
                    if ($answer) {
                        $studentAnswerText = $answer->answer;
                        if ($answer->is_correct) {
                            $isCorrect = true;
                            $earnedPoints += $questionPoints;
                            $correct++;
                            $scoreForThisQuestion = $questionPoints;
                        } else {
                            $aiFeedback = app(\App\Services\AIService::class)->generateQuestionFeedback($question->question, $correctAnswerText, $studentAnswerText);
                        }
                    }
                } else {
                    $studentAnswerText = "لم تتم الإجابة";
                    $aiFeedback = app(\App\Services\AIService::class)->generateQuestionFeedback($question->question, $correctAnswerText, "لم يقم الطالب بالإجابة.");
                }
            } elseif ($question->type === 'matching') {
                $matchingInputs = $request->input('matching.' . $question->id, []); // key=answer_id, value=matching_definition
                $correctPairsCount = 0;
                $totalPairsCount = $question->answers->count();
                
                $studentAnswersMapped = [];
                $correctAnswersMapped = [];

                foreach ($question->answers as $ans) {
                    [$term, $def] = explode('|||', $ans->answer);
                    $correctAnswersMapped[$ans->id] = $def;
                    $studentSelection = $matchingInputs[$ans->id] ?? null;
                    $studentAnswersMapped[$ans->id] = "{$term} -> " . ($studentSelection ?: "---");
                    
                    if ($studentSelection === $def) {
                        $correctPairsCount++;
                    }
                }

                $studentAnswerText = implode(', ', $studentAnswersMapped);
                $correctAnswerText = "All pairs must match correctly."; // simplify for summary
                
                if ($correctPairsCount === $totalPairsCount) {
                    $isCorrect = true;
                    $earnedPoints += $questionPoints;
                    $correct++;
                    $scoreForThisQuestion = $questionPoints;
                } else {
                    $aiFeedback = app(\App\Services\AIService::class)->generateQuestionFeedback($question->question, $correctAnswerText, $studentAnswerText);
                }
            } elseif ($question->type === 'essay') {
                $essayAnswer = $request->input('essay.' . $question->id);
                $studentAnswerText = $essayAnswer ?: "لم تتم الإجابة";
                
                $modelAnswerObj = $question->answers->first();
                $modelAnswerText = $modelAnswerObj ? $modelAnswerObj->answer : null;
                
                // AI Grading for both Self-Learning and School Systems
                if (!empty($essayAnswer)) {
                    $aiGrading = app(\App\Services\AIService::class)->gradeEssayAnswer($question->question, $essayAnswer, $modelAnswerText);
                    if ($aiGrading && isset($aiGrading['score'])) {
                        $scorePercentage = $aiGrading['score'] / 100;
                        $scoreForThisQuestion = $scorePercentage * $questionPoints;
                        $earnedPoints += $scoreForThisQuestion;
                        $aiFeedback = $aiGrading['feedback'] ?? null;
                        if ($scorePercentage >= 0.7) {
                            $isCorrect = true;
                        }
                    } else {
                        // Fallback to manual grading if AI evaluation fails
                        if (!$quiz->is_global && !$quiz->level_id) {
                            $needsGrading = true;
                            $aiFeedback = "تعذر التقييم عبر الذكاء الاصطناعي، سيتم إظهار الدرجة فور تصحيحها من المعلم.";
                        }
                    }
                } else {
                    $aiFeedback = "لم تقم بتقديم أي إجابة لهذا السؤال.";
                }
            }

            $details[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'student_answer' => $studentAnswerText,
                'correct_answer' => $correctAnswerText,
                'is_correct' => $isCorrect,
                'score' => $scoreForThisQuestion,
                'max_score' => $questionPoints,
                'ai_feedback' => $aiFeedback
            ];
        }

        $score = $totalPointsPossible > 0 ? round(($earnedPoints / $totalPointsPossible) * 100, 2) : 0;
        $status = $needsGrading ? 'pending_grading' : 'completed';

        // Result creation removed for cleanup. Using QuizAttempt only.

        // 2. New Quiz Attempts & XP Logic (Secure)
        $attempt = $this->quizService->processSubmission($user, $quiz, [
            'earned_points'         => $earnedPoints,
            'total_points_possible' => $totalPointsPossible,
            'status'                => $status,
            'details'               => $details,
        ]);

        $xpEarned = $attempt->xp_earned;

        $msg = $needsGrading 
            ? "تم تسليم الاختبار! حصلت حالياً على " . round($earnedPoints, 1) . " من أصل {$totalPointsPossible} (بانتظار تصحيح الجزء المقالي من المعلم)."
            : "أنهيت الاختبار! حصلت على " . round($earnedPoints, 1) . " من أصل {$totalPointsPossible} درجات وكسبت {$xpEarned} نقطة خبرة.";

        return redirect()->route('user.results.show', $attempt->id)->with('success', $msg);
    }

    /**
     * Manual grading for essays by teachers
     */
    public function gradeEssay(Request $request, QuizAttempt $attempt)
    {
        $user = Auth::user();
        if ($user->type !== 'teacher' && !in_array($user->type, ['admin', 'manager'])) {
            abort(403);
        }

        $request->validate([
            'question_id' => 'required',
            'score'       => 'required|numeric|min:0|max:100',
            'feedback'    => 'nullable|string'
        ]);

        $details = $attempt->details;
        $earnedPoints = 0;
        $totalPointsPossible = 0;

        foreach ($details as &$q) {
            $questionMaxPoints = $q['max_score'] ?? 1;
            if ($q['question_id'] == $request->question_id) {
                $scorePercentage = $request->score / 100;
                $q['score'] = $scorePercentage * $questionMaxPoints;
                $q['ai_feedback'] = $request->feedback;
                if ($scorePercentage >= 0.7) $q['is_correct'] = true;
            }
            $earnedPoints += $q['score'] ?? 0;
            $totalPointsPossible += $questionMaxPoints;
        }

        $newScore = $totalPointsPossible > 0 ? round(($earnedPoints / $totalPointsPossible) * 100, 2) : 0;
        
        // Check if all essays are graded
        $stillPending = false;
        foreach ($details as $q) {
            if (isset($q['type']) && $q['type'] === 'essay' && !isset($q['score'])) {
                $stillPending = true;
            }
        }

        $attempt->update([
            'details'           => $details,
            'score'             => (int) round($earnedPoints),
            'xp_earned'         => (int) round($earnedPoints * 10),
            'unused_percentage' => $newScore,
            'status'            => $stillPending ? 'pending_grading' : 'completed'
        ]);

        return back()->with('success', 'تم رصد درجة المقالي بنجاح.');
    }
}
