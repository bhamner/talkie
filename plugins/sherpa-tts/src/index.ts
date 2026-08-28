import { registerPlugin } from '@capacitor/core';
import type { SherpaTtsPlugin } from './definitions';

const SherpaTts = registerPlugin<SherpaTtsPlugin>('SherpaTts', {
    web: () => import('./web').then((module) => new module.SherpaTtsWeb()),
});

export * from './definitions';
export { SherpaTts };
