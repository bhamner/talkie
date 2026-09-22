<?php

namespace App\Http\Controllers;

use App\Support\WelcomePage;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
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
