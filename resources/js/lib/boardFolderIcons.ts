import ArrangeByNumbersOneNineIcon from '@hugeicons/core-free-icons/ArrangeByNumbersOneNineIcon';
import BeachIcon from '@hugeicons/core-free-icons/BeachIcon';
import DiceIcon from '@hugeicons/core-free-icons/DiceIcon';
import ExclamationMarkBigIcon from '@hugeicons/core-free-icons/ExclamationMarkBigIcon';
import FolderDetailsIcon from '@hugeicons/core-free-icons/FolderDetailsIcon';
import GarbageTruckIcon from '@hugeicons/core-free-icons/GarbageTruckIcon';
import GroupItemsIcon from '@hugeicons/core-free-icons/GroupItemsIcon';
import House05Icon from '@hugeicons/core-free-icons/House05Icon';
import Image03Icon from '@hugeicons/core-free-icons/Image03Icon';
import MaskTheater01Icon from '@hugeicons/core-free-icons/MaskTheater01Icon';
import PokemonIcon from '@hugeicons/core-free-icons/PokemonIcon';
import RainbowIcon from '@hugeicons/core-free-icons/RainbowIcon';
import ServingFoodIcon from '@hugeicons/core-free-icons/ServingFoodIcon';
import Time02Icon from '@hugeicons/core-free-icons/Time02Icon';
import UserFullViewIcon from '@hugeicons/core-free-icons/UserFullViewIcon';
import UserGroupIcon from '@hugeicons/core-free-icons/UserGroupIcon';

type BoardFolderIconData = typeof ArrangeByNumbersOneNineIcon;

export const folderIconCatalog: Record<string, BoardFolderIconData> = {
    ArrangeByNumbersOneNineIcon,
    BeachIcon,
    DiceIcon,
    ExclamationMarkBigIcon,
    FolderDetailsIcon,
    GarbageTruckIcon,
    GroupItemsIcon,
    House05Icon,
    Image03Icon,
    MaskTheater01Icon,
    PokemonIcon,
    RainbowIcon,
    ServingFoodIcon,
    Time02Icon,
    UserFullViewIcon,
    UserGroupIcon,
};

export function boardFolderIconByKey(icon: string | null | undefined): BoardFolderIconData | undefined {
    if (!icon) {
        return undefined;
    }

    return folderIconCatalog[icon];
}
