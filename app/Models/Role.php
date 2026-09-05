<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * These are PLATFORM-level roles for staff who operate the SaaS itself
     * (e.g. you, support staff) — not for regular customers. A customer's
     * access within a business is entirely governed by TeamMember
     * (owner/manager/agent), not by this model. See docs/ARCHITECTURE.md.
     */
    public const SUPER_ADMIN = 'super_admin';
    public const SUPPORT = 'support';

    protected $fillable = [
        'name',
        'label',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
