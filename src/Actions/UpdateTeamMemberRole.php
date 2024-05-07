<?php

namespace VictorBondaruk\Access\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use VictorBondaruk\Access\Events\TeamMemberUpdated;
use VictorBondaruk\Access\Access;
use VictorBondaruk\Access\Rules\Role;

class UpdateTeamMemberRole
{
    /**
     * Update the role for the given team member.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  int  $teamMemberId
     * @param  string  $role
     * @return void
     */
    public function update($user, $team, $teamMemberId, string $role)
    {
        Gate::forUser($user)->authorize('updateTeamMember', $team);

        Validator::make([
            'role' => $role,
        ], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $team->users()->updateExistingPivot($teamMemberId, [
            'role' => $role,
        ]);

        TeamMemberUpdated::dispatch($team->fresh(), Access::findUserByIdOrFail($teamMemberId));
    }
}
