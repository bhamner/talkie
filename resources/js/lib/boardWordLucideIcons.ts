import { Smile, SmilePlus, UsersRound, type LucideIcon } from 'lucide-vue-next';

export const lucideIconCatalog: Record<string, LucideIcon> = {
    'lucide:smile': Smile,
    'lucide:smile-plus': SmilePlus,
    'lucide:users-round': UsersRound,
};

export function boardWordLucideIconByKey(icon: string | null | undefined): LucideIcon | undefined {
    if (!icon) {
        return undefined;
    }

    return lucideIconCatalog[icon];
}
