<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    count: {
        type: Number,
        required: true,
    },
    subTitle: {
        type: String,
        default: null,
    },
    icon: {
        type: String,
        required: true,
    },
    iconColor: {
        type: String,
        required: true,
    }
})
</script>

<template>
    <div class="card flex-row justify-between p-4 sm:p-5">
        <div>
            <p class="text-base font-medium text-slate-700 dark:text-navy-100">
                {{title}}
            </p>
            <div class="mt-4 flex items-baseline space-x-1">
                <p class="text-2xl font-semibold text-slate-700 dark:text-navy-100">
                    {{count}}
                </p>
            </div>
            <p v-if="subTitle" class="text-xs+">{{subTitle}}</p>
        </div>
        <div :class="`mask is-squircle flex size-10 items-center justify-center bg-${iconColor}/10`">
            <i :class="`fa fa-${icon} text-xl text-${iconColor}`"></i>
        </div>
        <div class="absolute bottom-0 right-0 overflow-hidden rounded-lg">
            <i :class="`fa fa-${icon} translate-x-1/4 translate-y-1/4 text-5xl opacity-15`"></i>
        </div>
    </div>
</template>
