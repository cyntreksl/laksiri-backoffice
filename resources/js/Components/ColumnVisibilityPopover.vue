<script setup>
import Popper from "vue3-popper";
</script>

<template>
    <Popper>
        <button
            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
            x-tooltip.placement.top="'Column Visibility'">
            <i class="fa-solid fa-grip"></i>
        </button>
        <template #content>
            <div class="max-w-[16rem]">
                <div
                    class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                    <div
                        class="rounded-md border border-slate-150 bg-white p-4 dark:border-navy-600 dark:bg-navy-700">
                        <h3 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                            Select Columns
                        </h3>
                        <p class="mt-1 text-xs+">Choose which columns you want to see </p>
                        <div class="mt-4 flex flex-col space-y-4 text-slate-600 dark:text-navy-100">
                            <slot/>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Popper>
</template>

<style scoped>

</style>
