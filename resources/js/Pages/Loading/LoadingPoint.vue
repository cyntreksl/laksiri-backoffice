<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import moment from "moment";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {computed, ref} from "vue";
import draggable from 'vuedraggable'

const props = defineProps({
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

const searchQuery = ref('');

const hblPackagesArr = ref(props.hbls.flatMap(hbl => hbl.packages));

const filteredPackages = computed(() => {
    if (!searchQuery.value) {
        return hblPackagesArr.value;
    }
    return hblPackagesArr.value.filter(packageData => {
        const hbl = findHblByPackageId(packageData.id).hbl;
        return hbl.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
})

const containerArr = ref([]);

const handleLoad = (index) => {
    if (index !== -1) {
        containerArr.value.push(hblPackagesArr.value[index]);
        hblPackagesArr.value.splice(index, 1);
    }
}

const handleUnload = (index) => {
    if (index !== -1) {
        hblPackagesArr.value.push(containerArr.value[index]);
        containerArr.value.splice(index, 1)
    }
}

// find HBL by packageId
const findHblByPackageId = (packageId) => {
    return props.hbls.find(hbl => hbl.packages.some(p => p.id === packageId));
}
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
                        <svg class="size-5 mr-2 icon icon-tabler icons-tabler-outline icon-tabler-file-report"
                             fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                             stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                            <path d="M17 17m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                            <path d="M17 13v4h4"/>
                            <path d="M12 3v4a1 1 0 0 0 1 1h4"/>
                            <path d="M11.5 21h-6.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v2m0 3v4"/>
                        </svg>
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
                    <div class="relative flex max-h-full shrink-0 flex-col">
                        <div class="board-draggable-handler flex items-center justify-between px-0.5 pb-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa fa-boxes-packing text-base"></i>
                                </div>
                                <h3 class="text-base text-slate-700 dark:text-navy-100">
                                    HBL Packages
                                </h3>
                            </div>
                            <label class="relative hidden w-full max-w-[16rem] sm:flex">
                                <input
                                    v-model="searchQuery"
                                    class="form-input peer h-8 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 text-xs+ placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 dark:border-navy-450 dark:hover:border-navy-400 focus:ring-0"
                                    placeholder="Search on HBL Packages" type="text"/>
                                <span
                                    class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                    <svg class="size-4 transition-colors duration-200" fill="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.316 13.781l.73-.171-.73.171zm0-5.457l.73.171-.73-.171zm15.473 0l.73-.171-.73.171zm0 5.457l.73.171-.73-.171zm-5.008 5.008l-.171-.73.171.73zm-5.457 0l-.171.73.171-.73zm0-15.473l-.171-.73.171.73zm5.457 0l.171-.73-.171.73zM20.47 21.53a.75.75 0 101.06-1.06l-1.06 1.06zM4.046 13.61a11.198 11.198 0 010-5.115l-1.46-.342a12.698 12.698 0 000 5.8l1.46-.343zm14.013-5.115a11.196 11.196 0 010 5.115l1.46.342a12.698 12.698 0 000-5.8l-1.46.343zm-4.45 9.564a11.196 11.196 0 01-5.114 0l-.342 1.46c1.907.448 3.892.448 5.8 0l-.343-1.46zM8.496 4.046a11.198 11.198 0 015.115 0l.342-1.46a12.698 12.698 0 00-5.8 0l.343 1.46zm0 14.013a5.97 5.97 0 01-4.45-4.45l-1.46.343a7.47 7.47 0 005.568 5.568l.342-1.46zm5.457 1.46a7.47 7.47 0 005.568-5.567l-1.46-.342a5.97 5.97 0 01-4.45 4.45l.342 1.46zM13.61 4.046a5.97 5.97 0 014.45 4.45l1.46-.343a7.47 7.47 0 00-5.568-5.567l-.342 1.46zm-5.457-1.46a7.47 7.47 0 00-5.567 5.567l1.46.342a5.97 5.97 0 014.45-4.45l-.343-1.46zm8.652 15.28l3.665 3.664 1.06-1.06-3.665-3.665-1.06 1.06z"/>
                    </svg>
                </span>
                            </label>
                        </div>
                        <div>
                            <draggable v-if="Object.keys(filteredPackages).length > 0"
                                       :list="filteredPackages"
                                       class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                       group="people"
                                       item-key="id">
                                <template #item="{element, index}">
                                    <div class="card cursor-pointer shadow-sm">
                                        <div class="flex justify-between items-center">
                                            <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                <div>
                                                    <div class="flex justify-between">
                                                        <p class="font-medium tracking-wide text-slate-600 dark:text-navy-100">
                                                            {{ findHblByPackageId(element.id).hbl }}
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
                                                        <span>{{
                                                                moment(element.created_at).format('YYYY-MM-DD')
                                                            }}</span>
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
                                                        <span>Volume {{ element.volume }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                            fill="none" height="24" stroke="currentColor"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                            width="24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                            <path
                                                                d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                        </svg>
                                                        <span>Weight {{ element.volume }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                                            fill="none" stroke="currentColor"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M5 9l14 0"/>
                                                            <path d="M5 15l14 0"/>
                                                            <path d="M11 4l-4 16"/>
                                                            <path d="M17 4l-4 16"/>
                                                        </svg>
                                                        <span>Quantity {{ element.quantity }}</span>
                                                    </div>
                                                </div>
                                                <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                                    {{ element.package_type }}
                                                </p>
                                            </div>
                                            <div class="px-2.5">
                                                <svg
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-corner-up-right-double hover:text-success"
                                                    fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                    width="24"
                                                    x-tooltip.placement.top.success="'Click to Load'"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    @click.prevent="handleLoad(index)">
                                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                    <path d="M4 18v-6a3 3 0 0 1 3 -3h7"/>
                                                    <path d="M10 13l4 -4l-4 -4m5 8l4 -4l-4 -4"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>

                            <div v-else
                                 class="cursor-pointer border-2 border-error/20 bg-error/10 rounded-lg border-dashed">
                                <div class="flex justify-center items-center space-x-3 px-2.5 pb-2 pt-1.5 h-24">
                                    <div class="text-center">
                                        <p
                                            class="font-medium text-lg tracking-wide text-slate-400 line-clamp-2 dark:text-navy-100">
                                            Sorry! Not Found HBL Packages.
                                        </p>

                                        <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                            Please add HBL records first.
                                        </p>
                                    </div>
                                </div>
                            </div>
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
                            <draggable
                                :list="containerArr"
                                class="is-scrollbar-hidden relative space-y-2.5 overflow-y-auto p-0.5"
                                group="people"
                                item-key="id"
                            >
                                <template #item="{element, index}">
                                    <div class="card cursor-pointer shadow-sm">
                                        <div class="flex justify-between items-center">
                                            <div class="space-y-3 rounded-lg px-2.5 pb-2 pt-1.5">
                                                <div>
                                                    <div class="flex justify-between">
                                                        <p class="font-medium tracking-wide text-slate-600 dark:text-navy-100">
                                                            {{ findHblByPackageId(element.id).hbl }}
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
                                                        <span>{{
                                                                moment(element.created_at).format('YYYY-MM-DD')
                                                            }}</span>
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
                                                        <span>Volume {{ element.volume }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-error/10 py-1 px-1.5 text-error dark:bg-error/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-weight"
                                                            fill="none" height="24" stroke="currentColor"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                            width="24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                                            <path
                                                                d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                                                        </svg>
                                                        <span>Weight {{ element.volume }}</span>
                                                    </div>

                                                    <div
                                                        class="badge space-x-1 bg-success/10 py-1 px-1.5 text-success dark:bg-success/15">
                                                        <svg
                                                            class="size-4 icon icon-tabler icons-tabler-outline icon-tabler-hash"
                                                            fill="none" stroke="currentColor"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                            <path d="M5 9l14 0"/>
                                                            <path d="M5 15l14 0"/>
                                                            <path d="M11 4l-4 16"/>
                                                            <path d="M17 4l-4 16"/>
                                                        </svg>
                                                        <span>Quantity {{ element.quantity }}</span>
                                                    </div>
                                                </div>
                                                <p class="mt-px text-xs text-slate-400 dark:text-navy-300">
                                                    {{ element.package_type }}
                                                </p>
                                            </div>
                                            <div class="px-2.5">
                                                <svg
                                                    class=" hover:text-error icon icon-tabler icons-tabler-outline icon-tabler-corner-up-left-double"
                                                    fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                                    width="24"
                                                    x-tooltip.placement.top.error="'Click to Unload'"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    @click.prevent="handleUnload(index)">
                                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                    <path d="M19 18v-6a3 3 0 0 0 -3 -3h-7"/>
                                                    <path d="M13 13l-4 -4l4 -4m-5 8l-4 -4l4 -4"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                            <div v-if="containerArr.length === 0" class="cursor-pointer border-2 rounded-lg border-dashed">
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
