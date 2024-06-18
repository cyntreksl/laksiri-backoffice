<script setup>

import Tab from "@/Components/Tab.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import TextInput from "@/Components/TextInput.vue";
</script>

<template>
    <Tab label="Document for Shipment" name="tabDocuments">
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
    </Tab>
</template>

<style scoped>

</style>
