<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PersonalizeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('auth/Personalize', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'providers' => ['google', 'apple', 'facebook'],
        ]);
    }
}
