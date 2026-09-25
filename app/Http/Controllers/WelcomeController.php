<?php

namespace App\Http\Controllers;

use App\Support\WelcomePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user() !== null) {
            return redirect()->route('board');
        }

        return view('welcome', [
            'title' => WelcomePage::title(),
            'description' => WelcomePage::description(),
            'canonicalUrl' => url('/'),
            'imageUrl' => WelcomePage::imageUrl(),
            'imageAlt' => WelcomePage::imageAlt(),
            'faqs' => WelcomePage::faqs(),
            'schema' => WelcomePage::schema(),
        ]);
    }
}
