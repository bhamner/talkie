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

type BoardFolderIconData = typeof MaskTheater01Icon;

const boardFolderIconMap: Record<string, BoardFolderIconData> = {
    Animals: PokemonIcon,
    Body: UserFullViewIcon,
    Colors: RainbowIcon,
    Describing: FolderDetailsIcon,
    Feelings: MaskTheater01Icon,
    Food: ServingFoodIcon,
    Friends: UserGroupIcon,
    Home: House05Icon,
    Nature: Image03Icon,
    Numbers: ArrangeByNumbersOneNineIcon,
    Places: BeachIcon,
    Really: ExclamationMarkBigIcon,
    Shapes: DiceIcon,
    Stuff: GroupItemsIcon,
    Time: Time02Icon,
    Vehicles: GarbageTruckIcon,
};

export function boardFolderIcon(name: string): BoardFolderIconData | undefined {
    return boardFolderIconMap[name.trim()];
}
