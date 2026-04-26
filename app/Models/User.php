<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->id)) {
                $user->id = self::generateUniqueCustomId();
            }
            if ($user->type === 'teacher' && empty($user->teacher_code)) {
                $user->teacher_code = 'TCH-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    private static function generateUniqueCustomId()
    {
        do {
            $letters = chr(rand(65, 90)) . chr(rand(65, 90));
            $numbers = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $letters . $numbers;
        } while (self::where('id', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'type',
        'teacher_code',
        'school_id',
        'challenge_xp',
        'subscription_tier',
        'subscription_expires_at',
        'trial_ends_at',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
        ];
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function isManager(): bool
    {
        return $this->type === 'manager';
    }

    public function isSchool(): bool
    {
        return $this->type === 'school';
    }

    public function isTeacher(): bool
    {
        return $this->type === 'teacher';
    }

    public function isUser(): bool
    {
        return $this->type === 'user';
    }

    public function isFree(): bool
    {
        // Only students (user type) can be free.
        if ($this->type !== 'user') return false;
        
        return !$this->hasPremiumAccess();
    }

    public function isPro(): bool
    {
        return $this->hasPremiumAccess();
    }

    public function hasPremiumAccess(): bool
    {
        // Non-student roles always have full content access
        if ($this->type !== 'user') return true;

        if (!is_null($this->school_id)) return true;

        // If subscription has expired, treat them as Free tier
        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return false;
        }

        if (in_array($this->subscription_tier, ['individual', 'family'])) return true;

        if ($this->trial_ends_at && $this->trial_ends_at->isFuture()) return true;

        return false;
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    
    /**
     * Organizations this user belongs to.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users', 'user_id', 'organization_id')
                    ->withPivot('role', 'joined_at');
    }

    /**
     * Helper to get the user's active/first organization.
     */
    public function activeOrganization()
    {
        return $this->organizations()->first();
    }
    public function gradesAsStudent()
    {
        return $this->belongsToMany(Grade::class, 'student_grades', 'student_id', 'grade_id');
    }

    public function classesAsStudent()
    {
        return $this->gradesAsStudent();
    }

    public function unlockedTeachers()
    {
        return $this->belongsToMany(User::class, 'student_teachers', 'student_id', 'teacher_id');
    }

    public function unlockedStudents()
    {
        return $this->belongsToMany(User::class, 'student_teachers', 'teacher_id', 'student_id');
    }

    public function studentsViaUnlock()
    {
        return $this->unlockedStudents();
    }



    // Relation xp() removed for cleanup.

    public function challengeParticipations()
    {
        return $this->hasMany(ChallengeParticipant::class, 'user_id');
    }



    public function challenges()
    {
        return $this->hasMany(Challenge::class, 'created_by');
    }



    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    public function quizSummaries()
    {
        return $this->hasMany(UserQuizSummary::class, 'user_id');
    }

    public function userLanguages()
    {
        return $this->hasMany(UserLanguage::class, 'user_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    public function languagesTaught()
    {
        return $this->belongsToMany(SchoolLanguage::class, 'school_language_teacher', 'teacher_id', 'school_language_id')
                    ->withTimestamps();
    }

    public function levels()
    {
        return $this->belongsToMany(Level::class, 'level_user', 'user_id', 'level_id')->withTimestamps();
    }

    public function completedLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user', 'user_id', 'lesson_id')
                    ->withPivot('passed')
                    ->withTimestamps();
    }

    public function dailyUsages()
    {
        return $this->hasMany(DailyUserUsage::class, 'user_id');
    }
}
