<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
    ];

    protected static function booted()
    {
        static::creating(function ($grade) {
            if (empty($grade->code)) {
                $grade->code = 'GRD-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_grades', 'grade_id', 'student_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolLanguages()
    {
        return $this->belongsToMany(SchoolLanguage::class, 'grade_languages', 'grade_id', 'school_language_id')
                    ->withTimestamps();
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
