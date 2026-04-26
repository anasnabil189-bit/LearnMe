<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuizSummary extends Model
{
    protected $table = 'user_quiz_summary';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'best_score',
        'best_total_points',
        'best_xp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
