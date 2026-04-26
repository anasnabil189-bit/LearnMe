<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function grades()
    {
        return $this->belongsToMany(Grade::class, 'grade_languages', 'school_language_id', 'grade_id')
                    ->withTimestamps();
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'school_language_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'school_language_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'school_language_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'school_language_teacher', 'school_language_id', 'teacher_id')
                    ->withTimestamps();
    }
}
