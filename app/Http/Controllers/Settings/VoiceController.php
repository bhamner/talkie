<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\TalkieVoices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VoiceController extends Controller
{
    public function edit(Request $request): Response
    {
        $settings = $request->user()->settings;

        return Inertia::render('settings/Voice', [
            'voices' => TalkieVoices::all(),
            'voice' => [
                'id' => $settings?->voice_id ?? 'device-default',
                'uri' => $settings?->voice_uri,
                'name' => $settings?->voice_name,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'voice_id' => ['required', 'string', Rule::in(TalkieVoices::selectableIds())],
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
            ]
        );

        return back()->with('status', 'voice-updated');
    }
}
