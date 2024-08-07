<?php

namespace Victorbondaruk\Access\Tests;

use Victorbondaruk\Access\Access;

class AccessTest extends OrchestraTestCase
{
    public function test_roles_can_be_registered()
    {
        Access::$permissions = [];
        Access::$roles = [];

        Access::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        Access::role('editor', 'Editor', [
            'read',
            'update',
            'delete',
        ])->description('Editor Description');

        $this->assertTrue(Access::hasPermissions());

        $this->assertEquals([
            'create',
            'delete',
            'read',
            'update',
        ], Access::$permissions);
    }

    public function test_roles_can_be_json_serialized()
    {
        Access::$permissions = [];
        Access::$roles = [];

        $role = Access::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        $serialized = $role->jsonSerialize();

        $this->assertArrayHasKey('key', $serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('permissions', $serialized);
    }

    public function test_has_team_feature_will_always_return_false_when_team_is_not_enabled()
    {
        $this->assertFalse(Access::hasTeamFeatures());
        $this->assertFalse(Access::userHasTeamFeatures(new Fixtures\User));
        $this->assertFalse(Access::userHasTeamFeatures(new Fixtures\Admin));
    }

    /**
     * @define-env defineHasTeamEnvironment
     */
    public function test_has_team_feature_can_be_determined_when_team_is_enabled()
    {
        $this->assertTrue(Access::hasTeamFeatures());
        $this->assertTrue(Access::userHasTeamFeatures(new Fixtures\User));
        $this->assertFalse(Access::userHasTeamFeatures(new Fixtures\Admin));
    }
}
