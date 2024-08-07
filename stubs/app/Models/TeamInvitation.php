<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Victorbondaruk\Access\Access;
use Victorbondaruk\Access\TeamInvitation as AccessTeamInvitation;

class TeamInvitation extends AccessTeamInvitation
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Access::teamModel());
    }
}
