<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'title',
        'video_url',
        'source_type',
        'is_global',
        'level_id',
        'order',
        'language_id',
        'grade_id',
        'school_language_id',
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

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }



    public function blocks()
    {
        return $this->hasMany(LessonBlock::class)->orderBy('order');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($lesson) {
            foreach ($lesson->blocks()->where('type', 'image')->get() as $block) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($block->path);
            }
        });
    }
}
