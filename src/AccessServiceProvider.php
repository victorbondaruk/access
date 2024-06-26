<?php

namespace Victorbondaruk\Access;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Inertia\Inertia;
use Laravel\Fortify\Events\PasswordUpdatedViaController;
use Laravel\Fortify\Fortify;
use Victorbondaruk\Access\Http\Livewire\ApiTokenManager;
use Victorbondaruk\Access\Http\Livewire\CreateTeamForm;
use Victorbondaruk\Access\Http\Livewire\DeleteTeamForm;
use Victorbondaruk\Access\Http\Livewire\DeleteUserForm;
use Victorbondaruk\Access\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Victorbondaruk\Access\Http\Livewire\NavigationMenu;
use Victorbondaruk\Access\Http\Livewire\TeamMemberManager;
use Victorbondaruk\Access\Http\Livewire\TwoFactorAuthenticationForm;
use Victorbondaruk\Access\Http\Livewire\UpdatePasswordForm;
use Victorbondaruk\Access\Http\Livewire\UpdateProfileInformationForm;
use Victorbondaruk\Access\Http\Livewire\UpdateTeamNameForm;
use Victorbondaruk\Access\Http\Middleware\ShareInertiaData;
use Livewire\Livewire;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/access.php', 'access');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::viewPrefix('auth.');

        $this->configurePublishing();
        $this->configureRoutes();
        $this->configureCommands();

        RedirectResponse::macro('banner', function ($message) {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'success',
                'banner' => $message,
            ]);
        });

        RedirectResponse::macro('warningBanner', function ($message) {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'warning',
                'banner' => $message,
            ]);
        });

        RedirectResponse::macro('dangerBanner', function ($message) {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'danger',
                'banner' => $message,
            ]);
        });

        if (config('access.stack') === 'inertia' && class_exists(Inertia::class)) {
            $this->bootInertia();
        }

        if (config('access.stack') === 'livewire' && class_exists(Livewire::class)) {
            Livewire::component('navigation-menu', NavigationMenu::class);
            Livewire::component('profile.update-profile-information-form', UpdateProfileInformationForm::class);
            Livewire::component('profile.update-password-form', UpdatePasswordForm::class);
            Livewire::component('profile.two-factor-authentication-form', TwoFactorAuthenticationForm::class);
            Livewire::component('profile.logout-other-browser-sessions-form', LogoutOtherBrowserSessionsForm::class);
            Livewire::component('profile.delete-user-form', DeleteUserForm::class);

            if (Features::hasApiFeatures()) {
                Livewire::component('api.api-token-manager', ApiTokenManager::class);
            }

            if (Features::hasTeamFeatures()) {
                Livewire::component('teams.create-team-form', CreateTeamForm::class);
                Livewire::component('teams.update-team-name-form', UpdateTeamNameForm::class);
                Livewire::component('teams.team-member-manager', TeamMemberManager::class);
                Livewire::component('teams.delete-team-form', DeleteTeamForm::class);
            }
        }
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../stubs/config/access.php' => config_path('access.php'),
        ], 'access-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/0001_01_01_000000_create_users_table.php' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
        ], 'access-migrations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations/2020_05_21_100000_create_teams_table.php' => database_path('migrations/2020_05_21_100000_create_teams_table.php'),
            __DIR__ . '/../database/migrations/2020_05_21_200000_create_team_user_table.php' => database_path('migrations/2020_05_21_200000_create_team_user_table.php'),
            __DIR__ . '/../database/migrations/2020_05_21_300000_create_team_invitations_table.php' => database_path('migrations/2020_05_21_300000_create_team_invitations_table.php'),
        ], 'access-team-migrations');

        $this->publishes([
            __DIR__ . '/../routes/' . config('access.stack') . '.php' => base_path('routes/access.php'),
        ], 'access-routes');

        $this->publishes([
            __DIR__ . '/../stubs/inertia/resources/js/Pages/Auth' => resource_path('js/Pages/Auth'),
            __DIR__ . '/../stubs/inertia/resources/js/Components/AuthenticationCard.vue' => resource_path('js/Components/AuthenticationCard.vue'),
            __DIR__ . '/../stubs/inertia/resources/js/Components/AuthenticationCardLogo.vue' => resource_path('js/Components/AuthenticationCardLogo.vue'),
            __DIR__ . '/../stubs/inertia/resources/js/Components/Checkbox.vue' => resource_path('js/Components/Checkbox.vue'),
        ], 'access-inertia-auth-pages');
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        if (Access::$registersRoutes) {
            Route::group([
                'namespace' => 'Victorbondaruk\Access\Http\Controllers',
                'domain' => config('access.domain', null),
                'prefix' => config('access.prefix', config('access.path')),
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/' . config('access.stack') . '.php');
            });
        }
    }

    /**
     * Configure the commands offered by the application.
     *
     * @return void
     */
    protected function configureCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }

    /**
     * Boot any Inertia related services.
     *
     * @return void
     */
    protected function bootInertia()
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('web', ShareInertiaData::class);
        $kernel->appendToMiddlewarePriority(ShareInertiaData::class);

        if (class_exists(HandleInertiaRequests::class)) {
            $kernel->appendToMiddlewarePriority(HandleInertiaRequests::class);
        }

        Event::listen(function (PasswordUpdatedViaController $event) {
            if (request()->hasSession()) {
                request()->session()->put(['password_hash_sanctum' => Auth::user()->getAuthPassword()]);
            }
        });

        Fortify::loginView(function () {
            return Inertia::render('Auth/Login', [
                'canResetPassword' => Route::has('password.request'),
                'status' => session('status'),
            ]);
        });

        Fortify::requestPasswordResetLinkView(function () {
            return Inertia::render('Auth/ForgotPassword', [
                'status' => session('status'),
            ]);
        });

        Fortify::resetPasswordView(function (Request $request) {
            return Inertia::render('Auth/ResetPassword', [
                'email' => $request->input('email'),
                'token' => $request->route('token'),
            ]);
        });

        Fortify::registerView(function () {
            return Inertia::render('Auth/Register');
        });

        Fortify::verifyEmailView(function () {
            return Inertia::render('Auth/VerifyEmail', [
                'status' => session('status'),
            ]);
        });

        Fortify::twoFactorChallengeView(function () {
            return Inertia::render('Auth/TwoFactorChallenge');
        });

        Fortify::confirmPasswordView(function () {
            return Inertia::render('Auth/ConfirmPassword');
        });
    }
}
