<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name',
        'code',
        'status',
        'plan_type',
        'subscription_start',
        'subscription_end',
        'annual_subscription_fee',
        'student_limit',
    ];

    protected $casts = [
        'subscription_start' => 'date',
        'subscription_end'   => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($school) {
            if (empty($school->code)) {
                $school->code = 'SCH-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    public function teachers()
    {
        return $this->hasMany(User::class)->where('type', 'teacher');
    }



    public function students()
    {
        return $this->hasMany(User::class)->where('type', 'user');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function schoolLanguages()
    {
        return $this->hasMany(SchoolLanguage::class);
    }
}
