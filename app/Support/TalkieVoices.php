<?php

namespace App\Support;

use App\Models\User;

class TalkieVoices
{
    public const PLATFORM_WEB = 'web';

    public const PIPER_ID = 'premium-nova';

    public const NATIVE_USER_AGENT_MARK = 'TalkieNative';

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

    public static function isNativeUserAgent(?string $userAgent): bool
    {
        return str_contains(strtolower((string) $userAgent), strtolower(self::NATIVE_USER_AGENT_MARK));
    }

    public static function isNativeRequest(): bool
    {
        $client = strtolower((string) request()->header('X-Talkie-Client'));

        if (in_array($client, ['android', 'ios', 'native'], true)) {
            return true;
        }

        return self::isNativeUserAgent(request()->userAgent());
    }

    /**
     * @return array{id: string|null, uri: string|null, name: string|null, provider: string, engine: string|null, model: string|null, speaker_id: int|null}
     */
    public static function current(?User $user, ?bool $nativeApp = null): array
    {
        $nativeApp ??= self::isNativeRequest();
        $id = $user?->settings?->voice_id;
        $catalog = $id ? self::find($id) : null;

        if ($catalog === null && $nativeApp) {
            $catalog = self::find(self::PIPER_ID);
            $id = $catalog['id'] ?? self::PIPER_ID;
        }

        return [
            'id' => $id,
            'uri' => $user?->settings?->voice_uri,
            'name' => $user?->settings?->voice_name ?? ($catalog['name'] ?? null),
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
