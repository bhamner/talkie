<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NameController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        if ($request->user()->hasCompletedOnboarding()) {
            return redirect()->route('board');
        }

        return Inertia::render('onboarding/Name', [
            'preferred_name' => $request->user()->preferred_name,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_name' => ['required', 'string', 'max:80'],
        ]);

        $request->user()->update([
            'preferred_name' => trim($validated['preferred_name']),
        ]);

        return redirect()->route('onboarding.voice');
    }
}
