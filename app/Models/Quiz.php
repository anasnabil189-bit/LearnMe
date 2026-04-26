<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'source_type',
        'is_global',
        'level_id',
        'quiz_type',
        'lesson_id',
        'language_id',
        'grade_id',
        'school_language_id',
        'academic_type',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function schoolLanguage()
    {
        return $this->belongsTo(SchoolLanguage::class, 'school_language_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function scopeAdmin($query)
    {
        return $query->where('source_type', 'admin');
    }

    public function scopeTeacher($query)
    {
        return $query->where('source_type', 'teacher');
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Relation results() removed for cleanup.
}
