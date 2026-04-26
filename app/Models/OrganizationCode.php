<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'organization_id',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The organization that owns this code.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
