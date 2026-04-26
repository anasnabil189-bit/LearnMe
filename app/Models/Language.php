<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'code'];

    public function userLanguages()
    {
        return $this->hasMany(UserLanguage::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
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
