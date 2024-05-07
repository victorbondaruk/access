<?php

namespace Victorbondaruk\Access\Tests\Fixtures;

use App\Models\User as BaseUser;
use Victorbondaruk\Access\HasProfilePhoto;
use Victorbondaruk\Access\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens, HasTeams, HasProfilePhoto;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
