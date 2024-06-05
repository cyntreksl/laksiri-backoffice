<script setup>
import {computed, inject, onMounted} from "vue";

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
})

const addTab = inject('addTab');
const activeTab = inject('activeTab');

const isActive = computed(() => activeTab.value === props.name);

onMounted(() => {
    addTab({
        name: props.name,
        label: props.label,
    });
});

</script>

<template>
    <div v-show="isActive">
        <slot></slot>
    </div>
</template>
