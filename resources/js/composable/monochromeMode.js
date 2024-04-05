import {ref} from "vue";

export function useMonochromeSelector() {
    const isMonochrome = ref(localStorage.getItem("isMonochrome") === "true");

    const toggleMonochromeMode = () => {
        isMonochrome.value = !isMonochrome.value;
        setMonochromeMode(isMonochrome.value);
        localStorage.setItem("isMonochrome", isMonochrome.value);
    };

    const setMonochromeMode = (value = null) => {
        isMonochrome.value = value ?? isMonochrome.value;
        if (isMonochrome.value) {
            document.body.classList.add("is-monochrome");
        } else {
            document.body.classList.remove("is-monochrome");
        }
    }

    return {isMonochrome, toggleMonochromeMode, setMonochromeMode};
}
