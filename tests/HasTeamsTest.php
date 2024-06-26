<?php

namespace Victorbondaruk\Access\Tests;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Victorbondaruk\Access\Access;
use Victorbondaruk\Access\OwnerRole;
use Victorbondaruk\Access\Role;
use Victorbondaruk\Access\Tests\Fixtures\User as UserFixture;

class HasTeamsTest extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Access::$permissions = [];
        Access::$roles = [];

        Access::useUserModel(UserFixture::class);
    }

    public function test_teamRole_returns_an_OwnerRole_for_the_team_owner(): void
    {
        $team = Team::factory()->create();

        $this->assertInstanceOf(OwnerRole::class, $team->owner->teamRole($team));
    }

    public function test_teamRole_returns_the_matching_role(): void
    {
        Access::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        $team = Team::factory()
            ->hasAttached(User::factory(), [
                'role' => 'admin',
            ])
            ->create();
        $role = $team->users->first()->teamRole($team);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertSame('admin', $role->key);
    }

    public function test_teamRole_returns_null_if_the_user_does_not_belong_to_the_team(): void
    {
        $team = Team::factory()->create();

        $this->assertNull((new UserFixture())->teamRole($team));
    }

    public function test_teamRole_returns_null_if_the_user_does_not_have_a_role_on_the_site(): void
    {
        $team = Team::factory()
            ->has(User::factory())
            ->create();

        $this->assertNull($team->users->first()->teamRole($team));
    }

    public function test_teamPermissions_returns_all_for_team_owners(): void
    {
        $team = Team::factory()->create();

        $this->assertSame(['*'], $team->owner->teamPermissions($team));
    }

    public function test_teamPermissions_returns_empty_for_non_members(): void
    {
        $team = Team::factory()->create();

        $this->assertSame([], (new UserFixture())->teamPermissions($team));
    }

    public function test_teamPermissions_returns_permissions_for_the_users_role(): void
    {
        Access::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        $team = Team::factory()
            ->hasAttached(User::factory(), [
                'role' => 'admin',
            ])
            ->create();

        $this->assertSame(['read', 'create'], $team->users->first()->teamPermissions($team));
    }

    public function test_teamPermissions_returns_empty_permissions_for_members_without_a_defined_role(): void
    {
        Access::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        $team = Team::factory()
            ->has(User::factory())
            ->create();

        $this->assertSame([], $team->users->first()->teamPermissions($team));
    }
}
