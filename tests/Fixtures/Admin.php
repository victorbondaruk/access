<?php

namespace Victorbondaruk\Access\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Victorbondaruk\Access\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasProfilePhoto;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
