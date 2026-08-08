<?php

namespace App\Support;

class TalkieVoices
{
    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     tier: string,
     *     provider: string,
     *     preview_text: string,
     *     selectable: bool
     * }>
     */
    public static function all(): array
    {
        return config('talkie_voices', []);
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $voice) {
            if ($voice['id'] === $id) {
                return $voice;
            }
        }

        return null;
    }

    public static function selectableIds(): array
    {
        return collect(self::all())
            ->where('selectable', true)
            ->pluck('id')
            ->all();
    }
}
