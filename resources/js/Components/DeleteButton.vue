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
            class="inline-flex items-center btn bg-[#EC6239] font-medium text-white hover:bg-[#D65532] focus:bg-[#D65532] active:bg-[#C14B2C]/90 disabled:pointer-events-none disabled:select-none disabled:opacity-60">
        <slot />
    </button>
</template>
<!-- Poya dina Ciyntrek wetha bathi bara sithin sadana ladi  -->
