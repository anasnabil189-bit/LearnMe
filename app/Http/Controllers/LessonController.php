<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Level;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->type === 'teacher') {
            $assignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->with(['grade', 'schoolLanguage'])
                ->get()
                ->map(function($assignment) use ($user) {
                    $assignment->lessons_count = Lesson::where('user_id', $user->id)
                        ->where('grade_id', $assignment->grade_id)
                        ->where('school_language_id', $assignment->school_language_id)
                        ->count();
                    return $assignment;
                });
            return view('lessons.index', compact('assignments'));
        } else {
            // Admin sees global admin content grouped by language first
            if (!$request->has('language_id')) {
                $languages = Language::withCount(['lessons' => function ($query) {
                    $query->where('is_global', true);
                }, 'userLanguages' => function ($query) {
                    $query->whereHas('user', function($q) {
                        $q->where('type', 'user')->whereNull('school_id');
                    });
                }])->get();
                
                return view('admin.shared.languages', [
                    'languages' => $languages,
                    'pageTitle' => 'Lesson Content Library',
                    'entityType' => 'Lessons',
                    'entityCountAttr' => 'lessons_count',
                    'manageRouteName' => 'admin.lessons.index'
                ]);
            }

            $lessons = Lesson::where('is_global', true)
                ->where('language_id', $request->language_id)
                ->with(['level'])
                ->latest()->paginate(10);
            return view('lessons.index', compact('lessons'));
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

        $lessons = Lesson::where('user_id', $user->id)
            ->where('grade_id', $grade->id)
            ->where('school_language_id', $language->id)
            ->latest()
            ->paginate(15);

        return view('lessons.by_grade', compact('lessons', 'grade', 'language'));
    }

    public function create()
    {
        $user = Auth::user();
        $languages = Language::all();
        if ($user->type === 'teacher') {
            $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)->with(['grade', 'schoolLanguage'])->get();
            $classes = collect([]); // Legacy array for view compatibility if needed, or we adapt the view to use assignments
            $levels  = collect([]); 
        } else {
            $teacherAssignments = collect([]);
            $classes = collect([]); 
            $levels  = Level::orderBy('required_xp')->get();
        }

        return view('lessons.create', compact('classes', 'levels', 'languages', 'teacherAssignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'video_url'   => 'nullable|url',
            'level_id'    => 'nullable|exists:levels,id',
            'is_global'   => 'nullable|boolean',
            'language_id' => 'nullable|exists:languages,id',
        ], [
            'title.required' => 'Lesson title is required.',
            'video_url.url'  => 'Video URL is invalid.',
            'language_id.required'  => 'Please select a language.',
        ]);

        $user = auth()->user();
        $sourceType = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';

        if (in_array($user->type, ['admin', 'manager'])) {
            if (!$request->level_id) return back()->withErrors(['level_id' => 'Level must be selected.'])->withInput();
            if (!$request->language_id) return back()->withErrors(['language_id' => 'Please select a language.'])->withInput();
            $data = $request->only('title', 'video_url', 'level_id', 'language_id');
            $data['grade_id'] = null;
            $data['school_language_id'] = null;
            $data['is_global'] = true;
        } else {
            // Teacher validation for assignment
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

            $data = $request->only('title', 'video_url');
            $data['grade_id'] = $gradeId;
            $data['school_language_id'] = $schoolLanguageId;
            $data['level_id'] = null;
            $data['is_global'] = false;
        }

        $data['source_type'] = $sourceType;
        $data['user_id']     = $user->id;
        $data['content']     = null; // content now lives in blocks
        $lesson = Lesson::create($data);

        $this->saveBlocks($lesson, $request);

        $prefix = in_array($user->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.lessons.index')->with('success', 'Lesson created successfully.');
    }

    public function show(Lesson $lesson, \App\Services\LanguageService $languageService)
    {
        $user = Auth::user();
        if ($user->type === 'user') {
            $canAccess = false;
            
            // 1. If global lesson
            if ($lesson->is_global) {
                // Self-learning path (no school) allows all global lessons
                if (!$user->school_id) {
                    $canAccess = true;
                } 
                // In school mode, allow global lessons for stability
                $canAccess = true;
            } 
            // 2. If teacher-specific (School System)
            else {
                // Check if student has unlocked this teacher
                if ($user->unlockedTeachers()->where('teacher_id', $lesson->user_id)->exists()) {
                    $canAccess = true;
                }
            }

            if (!$canAccess) {
                return redirect()->route('user.dashboard')->with('error', 'يجب فك قفل محتوى هذا المعلم أولاً لمشاهدة هذا الدرس.');
            }

            if ($user->isFree()) {
                $today = now()->toDateString();
                
                $alreadyAccessed = \App\Models\DailyUserUsage::where('user_id', $user->id)
                    ->where('usage_type', 'lesson')
                    ->where('item_id', $lesson->id)
                    ->where('usage_date', $today)
                    ->exists();

                if (!$alreadyAccessed) {
                    $todaysLessonsCount = \App\Models\DailyUserUsage::where('user_id', $user->id)
                        ->where('usage_type', 'lesson')
                        ->where('usage_date', $today)
                        ->count();

                    if ($todaysLessonsCount >= 3) {
                        return back()->with('show_subscription_modal', true)
                            ->with('error', 'لقد استنفذت الحد اليومي المجاني (3 دروس). يرجى الترقية أو بدء النسخة التجريبية للمتابعة.');
                    }

                    \App\Models\DailyUserUsage::create([
                        'user_id' => $user->id,
                        'usage_type' => 'lesson',
                        'item_id' => $lesson->id,
                        'usage_date' => $today,
                    ]);
                }
            }
        }

        $lesson->load(['grade', 'schoolLanguage', 'level', 'blocks']);

        // Award XP for Global Lessons (Language-Specific)
        if ($user->type === 'user' && $lesson->is_global) {
            // Award 5 XP for completing a global lesson in its specific language
            $ul = $languageService->getUserLanguage($user, $lesson->language_id);
            $ul->increment('learning_xp', 5);
        }

        return view('lessons.show', compact('lesson'));
    }

    public function edit(Lesson $lesson)
    {
        $user = Auth::user();
        
        // Security check for teachers to only edit their own lessons
        if ($user->type === 'teacher') {
            if ($lesson->is_global) {
                abort(403); // Teachers cannot edit global lessons
            }
            
            // Validate they still own the assignment mapping
            if ($lesson->grade_id && $lesson->school_language_id) {
                $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                    ->where('grade_id', $lesson->grade_id)
                    ->where('school_language_id', $lesson->school_language_id)
                    ->exists();
                if (!$isAssigned) abort(403);
            }
            
            $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)->with(['grade', 'schoolLanguage'])->get();
            $classes = collect([]);
            $levels  = collect([]);
        } else {
            $teacherAssignments = collect([]);
            $classes = collect([]); 
            $levels  = Level::orderBy('required_xp')->get();
        }

        $languages = Language::all();

        return view('lessons.edit', compact('lesson', 'classes', 'levels', 'languages', 'teacherAssignments'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'video_url'   => 'nullable|url',
            'level_id'    => 'nullable|exists:levels,id',
            'language_id' => 'required|exists:languages,id',
        ]);

        $userAuth = auth()->user();
        $sourceType = in_array($userAuth->type, ['admin', 'manager']) ? 'admin' : 'teacher';

        if (in_array($userAuth->type, ['admin', 'manager'])) {
            if (!$request->level_id) return back()->withErrors(['level_id' => 'Level must be selected.'])->withInput();
            $data = $request->only('title', 'video_url', 'level_id', 'language_id');
            $data['grade_id'] = null;
            $data['school_language_id'] = null;
            $data['is_global'] = true;
        } else {
            if (!$request->grade_language) return back()->withErrors(['grade_language' => 'Grade and Language must be selected.'])->withInput();
            
            list($gradeId, $schoolLanguageId) = explode('|', $request->grade_language);
            
            // Validate teacher assignment
            $isAssigned = \App\Models\TeacherAssignment::where('teacher_id', $userAuth->id)
                ->where('grade_id', $gradeId)
                ->where('school_language_id', $schoolLanguageId)
                ->exists();
                
            if (!$isAssigned) {
                return back()->withErrors(['grade_language' => 'You are not assigned to this class.'])->withInput();
            }

            $data = $request->only('title', 'video_url');
            $data['grade_id'] = $gradeId;
            $data['school_language_id'] = $schoolLanguageId;
            $data['level_id'] = null;
            $data['is_global'] = false;
        }

        $data['source_type'] = $sourceType;
        $lesson->update($data);

        $this->saveBlocks($lesson, $request, true);

        $prefix = in_array($userAuth->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.lessons.index')->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Lesson $lesson)
    {
        $user = Auth::user();
        if ($user->type === 'teacher') {
            if ($lesson->is_global) {
                abort(403);
            }
            // Additional check: ownership is already inferred by teacher's inability to see others' lessons in index
        }
        $lesson->delete();
        $prefix = in_array(auth()->user()->type, ['admin', 'manager']) ? 'admin' : 'teacher';
        return redirect()->route($prefix . '.lessons.index')->with('success', 'Lesson deleted successfully.');
    }

    /**
     * Parse blocks_json + block_images[] and save as LessonBlock records.
     */
    private function saveBlocks(Lesson $lesson, $request, bool $replacing = false): void
    {
        if ($replacing) {
            // Delete old image files from storage before wiping blocks
            foreach ($lesson->blocks()->where('type', 'image')->get() as $old) {
                // Keep existing paths that are being re-submitted as existing blocks
                // We'll handle this via kept_paths
            }
        }

        $blocksJson = $request->input('blocks_json', '[]');
        $blocks     = json_decode($blocksJson, true) ?: [];
        $uploadedImages = $request->file('block_images', []);

        // Collect paths of kept existing blocks (for update case)
        $keptPaths = [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'image' && !empty($block['existing_path'])) {
                $keptPaths[] = $block['existing_path'];
            }
        }

        if ($replacing) {
            // Delete image files no longer kept
            foreach ($lesson->blocks()->where('type', 'image')->get() as $old) {
                if (!in_array($old->path, $keptPaths)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($old->path);
                }
            }
            // Wipe all old blocks
            $lesson->blocks()->delete();
        }

        foreach ($blocks as $order => $block) {
            $type = $block['type'] ?? 'text';

            if ($type === 'text') {
                \App\Models\LessonBlock::create([
                    'lesson_id' => $lesson->id,
                    'type'      => 'text',
                    'content'   => $block['content'] ?? '',
                    'path'      => null,
                    'order'     => $order,
                ]);
            } elseif ($type === 'image') {
                $path = null;
                $fileIndex = $block['file_index'] ?? null;

                if ($fileIndex !== null && isset($uploadedImages[$fileIndex])) {
                    $path = $uploadedImages[$fileIndex]->store('lessons', 'public');
                } elseif (!empty($block['existing_path'])) {
                    $path = $block['existing_path'];
                }

                if ($path) {
                    \App\Models\LessonBlock::create([
                        'lesson_id' => $lesson->id,
                        'type'      => 'image',
                        'content'   => null,
                        'path'      => $path,
                        'order'     => $order,
                    ]);
                }
            }
        }
    }
}
