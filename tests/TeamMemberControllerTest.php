<?php

namespace Victorbondaruk\Access\Tests;

use App\Actions\Access\CreateTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Gate;
use Victorbondaruk\Access\Access;
use Victorbondaruk\Access\Tests\Fixtures\TeamPolicy;
use Victorbondaruk\Access\Tests\Fixtures\User;
use Laravel\Sanctum\TransientToken;

class TeamMemberControllerTest extends OrchestraTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('access.stack', 'inertia');
        $app['config']->set('access.features', ['teams']);

        Gate::policy(Team::class, TeamPolicy::class);
        Access::useUserModel(User::class);
    }

    public function test_team_member_permissions_can_be_updated()
    {
        Access::role('admin', 'Admin', ['foo', 'bar']);
        Access::role('editor', 'Editor', ['baz', 'qux']);

        $team = $this->createTeam();

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $team->users()->attach($adam, ['role' => 'admin']);

        $response = $this->actingAs($team->owner)->put('/teams/' . $team->id . '/members/' . $adam->id, [
            'role' => 'editor',
        ]);

        $response->assertRedirect();

        $adam = $adam->fresh();

        $adam->withAccessToken(new TransientToken);

        $this->assertTrue($adam->hasTeamPermission($team, 'baz'));
        $this->assertTrue($adam->hasTeamPermission($team, 'qux'));
    }

    public function test_team_member_permissions_cant_be_updated_if_not_authorized()
    {
        $team = $this->createTeam();

        $adam = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $team->users()->attach($adam, ['role' => 'admin']);

        $response = $this->actingAs($adam)->put('/teams/' . $team->id . '/members/' . $adam->id, [
            'role' => 'admin',
        ]);

        $response->assertStatus(403);
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
