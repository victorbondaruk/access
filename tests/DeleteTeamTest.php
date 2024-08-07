<?php

namespace Victorbondaruk\Access\Tests;

use App\Actions\Access\CreateTeam;
use App\Actions\Access\DeleteTeam;
use App\Models\Team;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Victorbondaruk\Access\Actions\ValidateTeamDeletion;
use Victorbondaruk\Access\Access;
use Victorbondaruk\Access\Tests\Fixtures\TeamPolicy;
use Victorbondaruk\Access\Tests\Fixtures\User;

class DeleteTeamTest extends OrchestraTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Gate::policy(Team::class, TeamPolicy::class);
        Access::useUserModel(User::class);
    }

    public function test_team_can_be_deleted()
    {
        $team = $this->createTeam();

        $action = new DeleteTeam;

        $action->delete($team);

        $this->assertNull($team->fresh());
    }

    public function test_team_deletion_can_be_validated()
    {
        Access::useUserModel(User::class);

        $team = $this->createTeam();

        $action = new ValidateTeamDeletion;

        $action->validate($team->owner, $team);

        $this->assertTrue(true);
    }

    public function test_personal_team_cant_be_deleted()
    {
        $this->expectException(ValidationException::class);

        Access::useUserModel(User::class);

        $team = $this->createTeam();

        $team->forceFill(['personal_team' => true])->save();

        $action = new ValidateTeamDeletion;

        $action->validate($team->owner, $team);
    }

    public function test_non_owner_cant_delete_team()
    {
        $this->expectException(AuthorizationException::class);

        Access::useUserModel(User::class);

        $team = $this->createTeam();

        $action = new ValidateTeamDeletion;

        $action->validate(User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]), $team);
    }

    protected function createTeam()
    {
        $action = new CreateTeam;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        return $action->create($user, ['name' => 'Test Team']);
    }
}
