<?php

namespace App\Actions\Access;

use App\Models\Team;
use Victorbondaruk\Access\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     */
    public function delete(Team $team): void
    {
        $team->purge();
    }
}
