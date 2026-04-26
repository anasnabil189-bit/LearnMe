<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'code',
        'topic',
        'questions_count',
        'question_type',
        'quiz_id',
        'status',
        'ended_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
