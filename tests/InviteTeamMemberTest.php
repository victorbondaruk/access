<?php

namespace Victorbondaruk\Access\Tests;

use App\Actions\Access\CreateTeam;
use App\Actions\Access\InviteTeamMember;
use App\Models\Team;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Victorbondaruk\Access\Access;
use Victorbondaruk\Access\Tests\Fixtures\TeamPolicy;
use Victorbondaruk\Access\Tests\Fixtures\User;

class InviteTeamMemberTest extends OrchestraTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Gate::policy(Team::class, TeamPolicy::class);

        Access::useUserModel(User::class);
    }

    public function test_team_members_can_be_invited()
    {
        Mail::fake();

        Access::role('admin', 'Admin', ['foo']);

        $team = $this->createTeam();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $action = new InviteTeamMember;

        $action->invite($team->owner, $team, 'adam@laravel.com', 'admin');

        $team = $team->fresh();

        $this->assertCount(0, $team->users);
        $this->assertCount(1, $team->teamInvitations);
        $this->assertEquals('adam@laravel.com', $team->teamInvitations->first()->email);
        $this->assertEquals($team->id, $team->teamInvitations->first()->team->id);
    }

    public function test_user_cant_already_be_on_team()
    {
        Mail::fake();

        $this->expectException(ValidationException::class);

        $team = $this->createTeam();

        $otherUser = User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]);

        $action = new InviteTeamMember;

        $action->invite($team->owner, $team, 'adam@laravel.com', 'admin');
        $this->assertTrue(true);
        $action->invite($team->owner, $team->fresh(), 'adam@laravel.com', 'admin');
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
