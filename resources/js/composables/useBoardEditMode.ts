import { ref } from 'vue';

const editMode = ref(false);

export function useBoardEditMode() {
    const enterEditMode = () => {
        editMode.value = true;
    };

    const exitEditMode = () => {
        editMode.value = false;
    };

    const toggleEditMode = () => {
        editMode.value = !editMode.value;
    };

    return {
        editMode,
        enterEditMode,
        exitEditMode,
        toggleEditMode,
    };
}
