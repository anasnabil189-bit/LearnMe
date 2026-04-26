<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyUserUsage extends Model
{
    use HasFactory;

    protected $table = 'daily_user_usages';

    protected $fillable = [
        'user_id',
        'usage_type', // 'lesson', 'comprehensive_quiz'
        'item_id',
        'usage_date',
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
