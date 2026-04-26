<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'allowed_domains',
        'discount_percentage',
        'max_users',
        'subscription_plan',
    ];

    /**
     * Users belonging to this organization.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users', 'organization_id', 'user_id')
                    ->withPivot('role', 'joined_at');
    }

    /**
     * Codes belonging to this organization.
     */
    public function codes()
    {
        return $this->hasMany(OrganizationCode::class);
    }
}
