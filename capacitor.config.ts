import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'kids.talkie',
    appName: 'Talkie',
    webDir: 'native/www',
    appendUserAgent: 'TalkieNative',
    server: {
        url: 'https://www.talkie.kids',
        androidScheme: 'https',
        errorPath: 'error.html',
        allowNavigation: ['talkie.kids', '*.talkie.kids', 'google.com', '*.google.com'],
    },
    android: {
        allowMixedContent: false,
        adjustMarginsForEdgeToEdge: 'force',
        overrideUserAgent:
            'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36 TalkieNative/android',
    },
};

export default config;
