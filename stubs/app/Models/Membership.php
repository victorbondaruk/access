<?php

namespace App\Models;

use Victorbondaruk\Access\Membership as AccessMembership;

class Membership extends AccessMembership
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
