<script setup>
import {computed, defineEmits} from "vue";

const props = defineProps({
    modelValue: {
        type: [Array, Boolean]
    },
    value: {
        type: [Boolean, Object]
    },
    label: {
        type: String
    },
});

const emit = defineEmits(["update:modelValue"]);

const model = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    },
});
</script>
<template>
    <input v-model="model" :value="value" class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
           type="checkbox"/>
    <span>{{ label }}</span>
</template>
<style scoped>
[type='checkbox']:checked {
    background-image: none !important;
}

[type='checkbox']:focus, [type='radio']:focus {
    --tw-ring-offset-width: 0 !important;
}
</style>
