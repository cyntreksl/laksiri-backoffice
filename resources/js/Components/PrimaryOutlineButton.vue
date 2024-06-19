<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type"
            class="inline-flex items-center btn border border-primary font-medium text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white active:bg-primary/90 disabled:pointer-events-none disabled:select-none disabled:opacity-60">
        <slot/>
    </button>
</template>
