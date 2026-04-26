<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'grade_id',
        'school_language_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function schoolLanguage()
    {
        return $this->belongsTo(SchoolLanguage::class, 'school_language_id');
    }
}
