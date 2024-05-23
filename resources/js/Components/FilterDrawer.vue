<script setup>
import FilterCloseButton from "@/Components/FilterCloseButton.vue";
import FilterBorder from "@/Components/FilterBorder.vue";

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const close = () => {
    emit('close');
};
</script>
<template>
    <div v-show="show" class="block">
        <div class="fixed inset-0 z-[100] bg-slate-900/60 transition-opacity duration-200 show block"
             x-transition:enter="ease-out" x-transition:enter-end="opacity-100" x-transition:enter-start="opacity-0"
             x-transition:leave="ease-in" x-transition:leave-end="opacity-0"
             x-transition:leave-start="opacity-100"></div>
        <div class="fixed right-0 top-0 z-[101] w-72">
            <div
                class="flex h-screen p-5 w-full transform-gpu flex-col bg-white transition-transform duration-200 dark:bg-navy-700 show space-y-4 overflow-y-auto"
                x-transition:enter="ease-out" x-transition:enter-end="translate-x-0"
                x-transition:enter-start="translate-x-full" x-transition:leave="ease-in"
                x-transition:leave-end="translate-x-full" x-transition:leave-start="translate-x-0">
                <div class="my-3 flex h-5 items-center justify-between">
                    <h2 class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 lg:text-base">
                        <slot name="title"/>
                    </h2>

                    <FilterCloseButton @click="close"/>
                </div>

                <FilterBorder/>

                <slot name="content"/>
            </div>
        </div>
    </div>
</template>
