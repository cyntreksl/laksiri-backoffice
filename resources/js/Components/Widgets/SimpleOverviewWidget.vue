<script setup>
defineProps({
    count: {
        type: String||Number,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    bgColor: {
        type: String,
        default: "slate-100",
    }
})
</script>

<template>
    <div :class="`rounded-lg bg-${bgColor} p-3 dark:bg-navy-600 w-auto`">
        <div class="flex justify-between space-x-1">
            <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                {{count}}
            </p>
            <slot />
        </div>
        <p class="mt-1 text-xs+">{{title}}</p>
    </div>
</template>
