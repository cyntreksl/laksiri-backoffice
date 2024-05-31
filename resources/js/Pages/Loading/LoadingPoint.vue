<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import moment from "moment";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

defineProps({
    container: {
        type: Object,
        default: () => {
        }
    },
    hbls: {
        type: Object,
        default: () => {
        }
    }
})

</script>

<template>
    <AppLayout title="Loading Points">
        <template #header>Loading Points</template>

        <main class="kanban-app w-full">
            <div
                class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
                <div class="flex items-center space-x-1">
                    <h3 class="text-lg font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
                        Loading Point
                    </h3>
                </div>
                <div class="flex space-x-2">
                    <SecondaryButton>
                        <svg  class="size-5 mr-2 icon icon-tabler icons-tabler-outline icon-tabler-file-report"  fill="none"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M17 17m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M17 13v4h4" /><path d="M12 3v4a1 1 0 0 0 1 1h4" /><path d="M11.5 21h-6.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v2m0 3v4" /></svg>
                        Save Draft
                    </SecondaryButton>
                    <PrimaryButton>
                        Proceed to Review
                    </PrimaryButton>
                </div>
            </div>

            <div class="flex h-[calc(100vh-8.5rem)] flex-grow flex-col">
                <div
                    class="kanban-scrollbar grid grid-cols-1 sm:grid-cols-2 space-x-4 overflow-x-auto overflow-y-hidden px-[var(--margin-x)] transition-all duration-[.25s]">
                    <div class="board-draggable relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa fa-boxes-packing text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    HBL
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5">
                            <template v-for="hbl in hbls" :key="hbl.id">
                                <div v-for="packageData in hbl.packages" :key="packageData.id"
                                     class="card cursor-pointer shadow-sm">
                                    <div class="flex justify-between items-center">
                                        <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                            <div>
                                                <div class="flex justify-between">
                                                    <p class="font-medium tracking-wide text-slate-600 dark:text-navy-100">
                                                        {{ hbl.hbl }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap gap-1">
                                                <div
                                                    class="badge space-x-1 bg-slate-150 py-1 px-1.5 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                                                    <svg class="size-3.5" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"/>
                                                    </svg>
                                                    <span>{{ moment(packageData.created_at).format('YYYY-MM-DD') }}</span>
                                                </div>

                                                <div
                                                    class="badge space-x-1 bg-warning/10 py-1 px-1.5 text-warning dark:bg-warning/15">
                                                    <svg
                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-scale"
                                                        fill="none"
                                                        stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                        width="24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                        <path d="M7 20l10 0"/>
                                                        <path d="M6 6l6 -1l6 1"/>
                                                        <path d="M12 3l0 17"/>
                                                        <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                        <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                                    </svg>
                                                    <span>Volume {{ packageData.volume }}</span>
                                                </div>

                                                <div
                                                    class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                    <svg
                                                        class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                        fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                        width="24"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                        <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                        <path
                                                            d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                    </svg>
                                                    <span>Weight {{ packageData.volume }}</span>
                                                </div>

                                                <div
                                                    class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                    <svg class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash" fill="none" stroke="currentColor"
                                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                         viewBox="0 0 24 24"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                        <path d="M5 9l14 0"/>
                                                        <path d="M5 15l14 0"/>
                                                        <path d="M11 4l-4 16"/>
                                                        <path d="M17 4l-4 16"/>
                                                    </svg>
                                                    <span>Quantity {{ packageData.quantity }}</span>
                                                </div>
                                            </div>
                                            <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                                {{ packageData.package_type }}
                                            </p>
                                        </div>
                                        <div class="px-2.5">
                                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  x-tooltip.placement.top.success="'Click to Load'"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 18v-6a3 3 0 0 1 3 -3h7" /><path d="M10 13l4 -4l-4 -4m5 8l4 -4l-4 -4" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="board-draggable relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <i class="fa fa-boxes-alt text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    {{ container.cargo_type }} Container
                                </h3>
                            </div>
                            <div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    {{ container.container_type }}
                                </h3>
                            </div>
                        </div>
                        <div class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5">
                            <div class="cursor-pointer border-2 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-lg tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            {{ container.container_type }} Empty Container
                                        </p>

                                        <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                            Active to loading process
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
