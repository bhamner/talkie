<?php

namespace App\Support;

use App\Models\User;

class TalkieVoices
{
    public const PLATFORM_WEB = 'web';

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return config('talkie_voices', []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $voice) {
            if ($voice['id'] === $id) {
                return $voice;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function forClient(?User $user, string $platform = self::PLATFORM_WEB): array
    {
        return array_map(
            fn (array $voice): array => self::present($voice, $user, $platform),
            self::all(),
        );
    }

    /**
     * @return list<string>
     */
    public static function selectableIds(?User $user, string $platform = self::PLATFORM_WEB): array
    {
        return collect(self::forClient($user, $platform))
            ->where('selectable', true)
            ->pluck('id')
            ->all();
    }

    /**
     * @return array{id: string|null, uri: string|null, name: string|null, provider: string, engine: string|null, model: string|null, speaker_id: int|null}
     */
    public static function current(?User $user): array
    {
        $id = $user?->settings?->voice_id;
        $catalog = $id ? self::find($id) : null;

        return [
            'id' => $id,
            'uri' => $user?->settings?->voice_uri,
            'name' => $user?->settings?->voice_name,
            'provider' => $catalog['provider'] ?? 'device',
            'engine' => $catalog['engine'] ?? null,
            'model' => $catalog['model'] ?? null,
            'speaker_id' => $catalog['speaker_id'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $voice
     * @return array<string, mixed>
     */
    private static function present(array $voice, ?User $user, string $platform): array
    {
        $onPlatform = in_array($platform, $voice['platforms'] ?? [], true);
        $selectable = $onPlatform && match ($voice['provider'] ?? null) {
            'device' => true,
            'bundled' => $user !== null && ($voice['engine'] ?? null) === 'piper',
            default => false,
        };

        $voice['selectable'] = $selectable;
        $voice['lock_reason'] = $selectable ? null : ($onPlatform ? 'sign_in' : 'app');

        return $voice;
    }
}
