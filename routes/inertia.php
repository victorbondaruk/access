<?php

use Illuminate\Support\Facades\Route;
use Victorbondaruk\Access\Http\Controllers\CurrentTeamController;
use Victorbondaruk\Access\Http\Controllers\Inertia\ApiTokenController;
use Victorbondaruk\Access\Http\Controllers\Inertia\CurrentUserController;
use Victorbondaruk\Access\Http\Controllers\Inertia\OtherBrowserSessionsController;
use Victorbondaruk\Access\Http\Controllers\Inertia\PrivacyPolicyController;
use Victorbondaruk\Access\Http\Controllers\Inertia\ProfilePhotoController;
use Victorbondaruk\Access\Http\Controllers\Inertia\TeamController;
use Victorbondaruk\Access\Http\Controllers\Inertia\TeamMemberController;
use Victorbondaruk\Access\Http\Controllers\Inertia\TermsOfServiceController;
use Victorbondaruk\Access\Http\Controllers\Inertia\UserProfileController;
use Victorbondaruk\Access\Http\Controllers\TeamInvitationController;
use Victorbondaruk\Access\Access;

Route::group(['middleware' => config('access.middleware', ['web'])], function () {
    if (Access::hasTermsAndPrivacyPolicyFeature()) {
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
    }

    $authMiddleware = config('access.guard')
        ? 'auth:' . config('access.guard')
        : 'auth';

    $authSessionMiddleware = config('access.auth_session', false)
        ? config('access.auth_session')
        : null;

    Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
        // User & Profile...
        Route::get('/user/profile', [UserProfileController::class, 'show'])
            ->name('profile.show');

        Route::delete('/user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])
            ->name('other-browser-sessions.destroy');

        Route::delete('/user/profile-photo', [ProfilePhotoController::class, 'destroy'])
            ->name('current-user-photo.destroy');

        if (Access::hasAccountDeletionFeatures()) {
            Route::delete('/user', [CurrentUserController::class, 'destroy'])
                ->name('current-user.destroy');
        }

        Route::group(['middleware' => 'verified'], function () {
            // API...
            if (Access::hasApiFeatures()) {
                Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
                Route::post('/user/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
                Route::put('/user/api-tokens/{token}', [ApiTokenController::class, 'update'])->name('api-tokens.update');
                Route::delete('/user/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
            }

            // Teams...
            if (Access::hasTeamFeatures()) {
                Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
                Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
                Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
                Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
                Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
                Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
                Route::post('/teams/{team}/members', [TeamMemberController::class, 'store'])->name('team-members.store');
                Route::put('/teams/{team}/members/{user}', [TeamMemberController::class, 'update'])->name('team-members.update');
                Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');

                Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                    ->middleware(['signed'])
                    ->name('team-invitations.accept');

                Route::delete('/team-invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
                    ->name('team-invitations.destroy');
            }
        });
    });
});
