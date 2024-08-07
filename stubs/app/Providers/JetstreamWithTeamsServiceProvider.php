<?php

namespace App\Providers;

use App\Actions\Access\AddTeamMember;
use App\Actions\Access\CreateTeam;
use App\Actions\Access\DeleteTeam;
use App\Actions\Access\DeleteUser;
use App\Actions\Access\InviteTeamMember;
use App\Actions\Access\RemoveTeamMember;
use App\Actions\Access\UpdateTeamName;
use Illuminate\Support\ServiceProvider;
use Victorbondaruk\Access\Access;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Access::createTeamsUsing(CreateTeam::class);
        Access::updateTeamNamesUsing(UpdateTeamName::class);
        Access::addTeamMembersUsing(AddTeamMember::class);
        Access::inviteTeamMembersUsing(InviteTeamMember::class);
        Access::removeTeamMembersUsing(RemoveTeamMember::class);
        Access::deleteTeamsUsing(DeleteTeam::class);
        Access::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Access::defaultApiTokenPermissions(['read']);

        Access::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

        Access::role('editor', 'Editor', [
            'read',
            'create',
            'update',
        ])->description('Editor users have the ability to read, create, and update.');
    }
}
