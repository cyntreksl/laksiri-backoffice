import {ref} from "vue";

export function useDarkModeSelector() {
    const isDarkMode = ref(localStorage.getItem("isDarkMode") === "true");

    const toggleDarkMode = () => {
        isDarkMode.value = !isDarkMode.value;
        setDarkMode(isDarkMode.value);
        localStorage.setItem("isDarkMode", isDarkMode.value);
    };

    const setDarkMode = (value = null) => {
        isDarkMode.value  = value ?? isDarkMode.value;
        if (isDarkMode.value) {
            document.body.classList.add("dark");
        } else {
            document.body.classList.remove("dark");
        }
    }

    return {isDarkMode, toggleDarkMode, setDarkMode};
}
