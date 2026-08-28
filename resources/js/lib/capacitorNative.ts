import { App } from '@capacitor/app';
import { Browser } from '@capacitor/browser';
import { Capacitor } from '@capacitor/core';

const consumeHandoff = async (url: string): Promise<void> => {
    let parsed: URL;

    try {
        parsed = new URL(url);
    } catch {
        return;
    }

    if (parsed.protocol !== 'talkie:' || parsed.hostname !== 'auth') {
        return;
    }

    const token = parsed.searchParams.get('token');
    try {
        await Browser.close();
    } catch {
        // Custom Tabs may already be closed.
    }

    if (!token) {
        return;
    }

    window.location.href = `/auth/native/handoff?token=${encodeURIComponent(token)}`;
};

export const bootNativeShell = async (): Promise<void> => {
    if (!Capacitor.isNativePlatform()) {
        return;
    }

    const launch = await App.getLaunchUrl();
    if (launch?.url) {
        await consumeHandoff(launch.url);
    }

    await App.addListener('appUrlOpen', ({ url }) => {
        void consumeHandoff(url);
    });
};
