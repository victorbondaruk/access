<?php

namespace Victorbondaruk\Access\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Victorbondaruk\Access\Access;

class TeamController extends Controller
{
    /**
     * Show the team management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $teamId)
    {
        $team = Access::newTeamModel()->findOrFail($teamId);

        if (Gate::denies('view', $team)) {
            abort(403);
        }

        return view('teams.show', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    /**
     * Show the team creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Access::newTeamModel());

        return view('teams.create', [
            'user' => $request->user(),
        ]);
    }
}
