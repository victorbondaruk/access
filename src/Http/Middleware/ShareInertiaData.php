<?php

namespace Victorbondaruk\Access\Http\Middleware;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Victorbondaruk\Access\Access;

class ShareInertiaData
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        Inertia::share(array_filter([
            'access' => function () use ($request) {
                $user = $request->user();

                return [
                    'canCreateTeams' => $user &&
                        Access::userHasTeamFeatures($user) &&
                        Gate::forUser($user)->check('create', Access::newTeamModel()),
                    'canManageTwoFactorAuthentication' => Features::canManageTwoFactorAuthentication(),
                    'canUpdatePassword' => Features::enabled(Features::updatePasswords()),
                    'canUpdateProfileInformation' => Features::canUpdateProfileInformation(),
                    'hasEmailVerification' => Features::enabled(Features::emailVerification()),
                    'flash' => $request->session()->get('flash', []),
                    'hasAccountDeletionFeatures' => Access::hasAccountDeletionFeatures(),
                    'hasApiFeatures' => Access::hasApiFeatures(),
                    'hasTeamFeatures' => Access::hasTeamFeatures(),
                    'hasTermsAndPrivacyPolicyFeature' => Access::hasTermsAndPrivacyPolicyFeature(),
                    'managesProfilePhotos' => Access::managesProfilePhotos(),
                ];
            },
            'auth' => [
                'user' => function () use ($request) {
                    if (!$user = $request->user()) {
                        return;
                    }

                    $userHasTeamFeatures = Access::userHasTeamFeatures($user);

                    if ($user && $userHasTeamFeatures) {
                        $user->currentTeam;
                    }

                    return array_merge($user->toArray(), array_filter([
                        'all_teams' => $userHasTeamFeatures ? $user->allTeams()->values() : null,
                    ]), [
                        'two_factor_enabled' => Features::enabled(Features::twoFactorAuthentication())
                            && !is_null($user->two_factor_secret),
                    ]);
                },
            ],
            'errorBags' => function () {
                return collect(optional(Session::get('errors'))->getBags() ?: [])->mapWithKeys(function ($bag, $key) {
                    return [$key => $bag->messages()];
                })->all();
            },
        ]));

        return $next($request);
    }
}
