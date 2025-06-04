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
    <div class="flex flex-col">
        <p class="text-xs uppercase text-slate-400 dark:text-navy-300 flex-shrink-0" style="width: 120px">
            {{ label }}
        </p>
        <p class="font-medium text-slate-700 dark:text-navy-100 break-words">
            {{ value || '-' }}
        </p>
    </div>
</template>
