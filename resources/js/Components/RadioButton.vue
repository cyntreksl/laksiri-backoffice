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
    name: {
        type: String,
        required: true,
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
    <input
        v-model="model" :name="name" :value="value"
        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
        type="radio"
    />
    <span>{{ label }}</span>
</template>
