<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type"
            class="inline-flex items-center justify-center btn border border-error font-medium text-error hover:bg-error hover:text-white focus:bg-error focus:text-white active:bg-error/90">
        <slot/>
    </button>
</template>
