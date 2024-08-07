<?php

namespace App\Providers;

use App\Actions\Access\DeleteUser;
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

        Access::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Access::defaultApiTokenPermissions(['read']);

        Access::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
