<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    protected $fillable = [
        'user_id',
        'language_id',
        'learning_xp',
        'current_level_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function currentLevel()
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }
}
