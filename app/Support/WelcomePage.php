<?php

namespace App\Support;

class WelcomePage
{
    public static function title(): string
    {
        return 'A free speech board for kids - Talkie';
    }

    public static function description(): string
    {
        return 'Talkie is a free picture board that speaks for your child. Tap a word to hear it and build a sentence. The words come from toddler and preschool speech research, shaped with speech-language pathologists.';
    }

    public static function imagePath(): string
    {
        return 'images/mother-and-child.jpg';
    }

    public static function imageUrl(): string
    {
        return asset(self::imagePath());
    }

    public static function imageAlt(): string
    {
        return 'A parent and young child looking at a tablet together';
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function faqs(): array
    {
        return [
            [
                'question' => 'What is Talkie?',
                'answer' => 'Talkie is a free online board of picture buttons. When your child taps a word, Talkie says it out loud and adds it to a sentence at the top of the screen. They can then speak the whole sentence.',
            ],
            [
                'question' => 'Does my child need an account?',
                'answer' => 'No. Anyone can open the board and start tapping. Sign in with Google only if you want to save your child’s name, a preferred voice, and your own words and folders.',
            ],
            [
                'question' => 'Where do the words come from?',
                'answer' => 'The first screen uses the toddler core words from Banajee, DiCarlo, and Stricklin. The folders use the preschool words from Marvin, Beukelman, and Bilyeu. Licensed speech-language pathologists helped decide how those words are grouped and how sentences are built.',
            ],
            [
                'question' => 'Is Talkie a replacement for speech therapy?',
                'answer' => 'No. Talkie is a practice board you can use at home, at school, or on the go. It does not replace working with your child’s speech-language pathologist.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        $url = url('/');

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => $url.'#website',
                    'name' => 'Talkie',
                    'url' => $url,
                    'description' => self::description(),
                    'image' => self::image(),
                    'inLanguage' => 'en',
                ],
                [
                    '@type' => 'WebApplication',
                    '@id' => $url.'#app',
                    'name' => 'Talkie',
                    'url' => $url,
                    'description' => self::description(),
                    'image' => self::image(),
                    'applicationCategory' => 'EducationalApplication',
                    'operatingSystem' => 'Web',
                    'isAccessibleForFree' => true,
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'USD',
                    ],
                    'audience' => [
                        '@type' => 'PeopleAudience',
                        'audienceType' => 'Parents and children learning to communicate',
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    '@id' => $url.'#faq',
                    'image' => self::image(),
                    'mainEntity' => array_map(
                        fn (array $faq): array => [
                            '@type' => 'Question',
                            'name' => $faq['question'],
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => $faq['answer'],
                            ],
                        ],
                        self::faqs(),
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array{'@type': string, url: string, width: int, height: int, caption: string}
     */
    private static function image(): array
    {
        return [
            '@type' => 'ImageObject',
            'url' => self::imageUrl(),
            'width' => 1920,
            'height' => 1080,
            'caption' => self::imageAlt(),
        ];
    }
}
