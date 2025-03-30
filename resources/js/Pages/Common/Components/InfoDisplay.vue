<script setup>
defineProps({
    label: {
        type: String,
        required: true
    },
    value: {
        type: String,
        default: '-'
    }
})
</script>

<template>
    <div>
        <p class="text-xs uppercase text-slate-400 dark:text-navy-300">
            {{ label }}
        </p>
        <p class="mt-1 font-medium text-slate-700 dark:text-navy-100">
            {{ value || '-' }}
        </p>
    </div>
</template>
