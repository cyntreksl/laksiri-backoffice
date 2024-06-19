<script setup>
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import Tab from "@/Components/Tab.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    hbl: {
        type: Object,
        default: () => {
        },
    }
});
</script>

<template>
    <Tab label="Documents" name="tabDocuments">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="col-span-1 space-y-3">
                <div class="filepond fp-bg-filled">
                    <input multiple type="file" x-init="$el._x_filepond = FilePond.create($el)"/>
                </div>
                <div>
                    <TextInput class="w-full" placeholder="Notes"/>
                </div>
                <div>
                    <PrimaryOutlineButton>
                        Upload
                    </PrimaryOutlineButton>
                </div>
            </div>
            <div class="col-span-1 sm:col-span-2">
                <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100">
                    List of Uploaded Documents
                </h2>
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto mt-5">
                    <table class="is-hoverable w-full text-left">
                        <thead>
                        <tr>
                            <th
                                class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                HBL
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Document Name
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Uploaded Details
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Notes
                            </th>
                            <th
                                class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                                -
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">-</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                -
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">-</td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">-</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="my-4">
            <h2 class="text-base font-medium tracking-wide text-slate-800 line-clamp-1 dark:text-navy-100">
                Other Attachments
            </h2>

            <div class="whitespace-nowrap py-3 hover:bg-gray-50 rounded">
                <div class="flex justify-between items-center space-x-4 px-2">
                    <div class="flex items-center space-x-4">
                        <div
                            class="relative flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-accent">
                            <svg class="size-5.5 text-primary dark:text-white icon icon-tabler icons-tabler-outline icon-tabler-pdf" fill="none" height="24" stroke="currentColor"
                                 stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                 width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M10 8v8h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-2z"/>
                                <path d="M3 12h2a2 2 0 1 0 0 -4h-2v8"/>
                                <path d="M17 12h3"/>
                                <path d="M21 8h-4v8"/>
                            </svg>
                        </div>
                        <span class="font-medium text-slate-700 dark:text-navy-100">HBL Record</span>
                    </div>
                    <a :href="route('hbls.download', hbl.id)">
                        <svg  class="icon icon-tabler icons-tabler-outline icon-tabler-download"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </Tab>
</template>
