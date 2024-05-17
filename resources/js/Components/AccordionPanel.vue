<script setup>
import {ref} from 'vue';

const props = defineProps({
    title: {type: String, required: true},
    subTitle: {type: String, required: false},
});

const showPanel = ref(false);
</script>

<template>
    <div class="flex flex-col space-y-4 sm:space-y-5 lg:space-y-6 my-2"
    >
        <div
            class="overflow-hidden rounded-lg border border-slate-150 dark:border-navy-500"
        >
            <div
                class="flex items-center justify-between bg-slate-150 px-4 py-4 dark:bg-navy-500 sm:px-5"
            >
                <div
                    class="flex items-center space-x-3.5 tracking-wide outline-none transition-all"
                >
                    <slot name="header-image"/>
                    <div>
                        <p class="text-slate-700 font-bold line-clamp-1 dark:text-navy-100">
                            {{ title }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-navy-300">
                            {{ subTitle }}
                        </p>
                    </div>
                </div>
                <button
                    class="btn -mr-1.5 size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                    @click.prevent="showPanel = !showPanel"
                >
                    <svg v-if="!showPanel" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19.5 8.25-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m4.5 15.75 7.5-7.5 7.5 7.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                </button>
            </div>
            <Transition v-if="showPanel" appear mode="out-in" name="fade">
                <div class="px-4 py-4 sm:px-5">
                    <slot/>
                </div>
            </Transition>
        </div>
    </div>
</template>
