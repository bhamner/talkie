<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Services\BoardTemplateService;
use App\Support\TalkieVoices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VoiceController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('board');
        }

        if (! filled($user->preferred_name)) {
            return redirect()->route('onboarding.name');
        }

        return Inertia::render('onboarding/Voice', [
            'voices' => TalkieVoices::forClient($user),
            'voice' => TalkieVoices::current($user),
            'preferred_name' => $user->preferred_name,
        ]);
    }

    public function update(Request $request, BoardTemplateService $boardTemplate): RedirectResponse
    {
        if (! filled($request->user()->preferred_name)) {
            return redirect()->route('onboarding.name');
        }

        $validated = $request->validate([
            'voice_id' => ['required', 'string', Rule::in(TalkieVoices::selectableIds($request->user()))],
            'voice_uri' => ['nullable', 'string', 'max:255'],
            'voice_name' => ['nullable', 'string', 'max:255'],
        ]);

        $catalog = TalkieVoices::find($validated['voice_id']);

        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'voice_id' => $validated['voice_id'],
                'voice_uri' => $validated['voice_uri'] ?? null,
                'voice_name' => $validated['voice_name'] ?? ($catalog['name'] ?? null),
                'onboarding_completed_at' => now(),
            ]
        );

        $boardTemplate->copyToUser($request->user());

        return redirect()->route('board');
    }
}
