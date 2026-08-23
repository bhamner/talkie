<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PersonalizeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('auth/Personalize', [
            'status' => $request->session()->get('status'),
            'providers' => ['google', 'apple'],
        ]);
    }
}
