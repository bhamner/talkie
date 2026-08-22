import { Smile, SmilePlus, UsersRound, type LucideIcon } from 'lucide-vue-next';

const boardWordLucideIconMap: Record<string, LucideIcon> = {
    i: Smile,
    mine: SmilePlus,
    friend: UsersRound,
};

export function boardWordLucideIcon(label: string): LucideIcon | undefined {
    return boardWordLucideIconMap[label.trim().toLowerCase()];
}
