<?php

namespace Victorbondaruk\Access\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Victorbondaruk\Access\Access;

class TermsOfServiceController extends Controller
{
    /**
     * Show the terms of service for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function show(Request $request)
    {
        $termsFile = Access::localizedMarkdownPath('terms.md');

        return Inertia::render('TermsOfService', [
            'terms' => Str::markdown(file_get_contents($termsFile)),
        ]);
    }
}
