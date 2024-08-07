<?php

use Illuminate\Support\Facades\Route;
use Victorbondaruk\Access\Http\Controllers\CurrentTeamController;
use Victorbondaruk\Access\Http\Controllers\Livewire\ApiTokenController;
use Victorbondaruk\Access\Http\Controllers\Livewire\PrivacyPolicyController;
use Victorbondaruk\Access\Http\Controllers\Livewire\TeamController;
use Victorbondaruk\Access\Http\Controllers\Livewire\TermsOfServiceController;
use Victorbondaruk\Access\Http\Controllers\Livewire\UserProfileController;
use Victorbondaruk\Access\Http\Controllers\TeamInvitationController;
use Victorbondaruk\Access\Access;

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
    if (Access::hasTermsAndPrivacyPolicyFeature()) {
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
    }

    $authMiddleware = config('jetstream.guard')
        ? 'auth:' . config('jetstream.guard')
        : 'auth';

    $authSessionMiddleware = config('jetstream.auth_session', false)
        ? config('jetstream.auth_session')
        : null;

    Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
        // User & Profile...
        Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile.show');

        Route::group(['middleware' => 'verified'], function () {
            // API...
            if (Access::hasApiFeatures()) {
                Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
            }

            // Teams...
            if (Access::hasTeamFeatures()) {
                Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
                Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
                Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

                Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                    ->middleware(['signed'])
                    ->name('team-invitations.accept');
            }
        });
    });
});
