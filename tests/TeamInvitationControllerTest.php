<?php

namespace Victorbondaruk\Access\Tests;

use App\Actions\Access\CreateTeam;
use App\Models\Team;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Victorbondaruk\Access\Contracts\AddsTeamMembers;
use Victorbondaruk\Access\Access;
use Victorbondaruk\Access\Tests\Fixtures\TeamPolicy;
use Victorbondaruk\Access\Tests\Fixtures\User;

class TeamInvitationControllerTest extends OrchestraTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('access.stack', 'inertia');
        $app['config']->set('access.features', ['teams']);

        Gate::policy(Team::class, TeamPolicy::class);
        Access::useUserModel(User::class);
    }

    public function test_team_invitations_can_be_accepted()
    {
        $this->mock(AddsTeamMembers::class)->shouldReceive('add')->once();

        Access::role('admin', 'Admin', ['foo', 'bar']);
        Access::role('editor', 'Editor', ['baz', 'qux']);

        $team = $this->createTeam();

        $invitation = $team->teamInvitations()->create(['email' => 'adam@laravel.com', 'role' => 'admin']);

        $url = URL::signedRoute('team-invitations.accept', ['invitation' => $invitation]);

        $response = $this->actingAs($team->owner)->get($url);

        $response->assertRedirect();
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
