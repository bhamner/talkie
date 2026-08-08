<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GateController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match ($user->onboardingStep()) {
            'name' => redirect()->route('onboarding.name'),
            'voice' => redirect()->route('onboarding.voice'),
            default => redirect()->route('board'),
        };
    }
}
