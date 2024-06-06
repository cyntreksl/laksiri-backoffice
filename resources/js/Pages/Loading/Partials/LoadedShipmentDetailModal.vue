<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import Tabs from "@/Components/Tabs.vue";
import Tab from "@/Components/Tab.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    container: {
        type: Object,
        default: () => {
        },
    }
});


console.log(props.container)
const emit = defineEmits(['close']);
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'7xl'" :show="show" @close="close">
        <template #title>
            <div class="flex justify-between items-center">
                <div></div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

        </template>
        <template #content>
            <Tabs>
                <template #icon="{tab}">
                    <svg v-if="tab.name === 'tabHome'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>

                    <svg v-if="tab.name === 'tabShipment'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>

                    <svg v-if="tab.name === 'tabDocuments'" class="w-6 h-6" fill="none" stroke="currentColor"
                         stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"/>
                    </svg>
                </template>

                <Tab label="HBLs Under Shipment" name="tabHome">
                    <div
                        class="mt-3 flex flex-col items-center justify-between space-y-2 text-center sm:flex-row sm:space-y-0 sm:text-left">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                                HBLs Under Loading
                            </h3>
                            <p class="mt-1 hidden sm:block">{{ container.reference }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <PrimaryOutlineButton>
                                <svg class="size-5 mr-2" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>

                                Add HBL To Shipment
                            </PrimaryOutlineButton>

                            <PrimaryOutlineButton>
                                <svg class="size-5 mr-2" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>

                                Print All HBL
                            </PrimaryOutlineButton>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <SimpleOverviewWidget :count="0" title="HBL">
                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-app-window text-info" fill="none" height="24" stroke="currentColor"
                                 stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                 width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path
                                    d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"/>
                                <path d="M6 8h.01"/>
                                <path d="M9 8h.01"/>
                            </svg>
                        </SimpleOverviewWidget>

                        <SimpleOverviewWidget :count="0" title="Package">
                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-package text-info"
                                 fill="none"
                                 height="24" stroke="currentColor" stroke-linecap="round"
                                 stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                                <path d="M12 12l8 -4.5"/>
                                <path d="M12 12l0 9"/>
                                <path d="M12 12l-8 -4.5"/>
                                <path d="M16 5.25l-8 4.5"/>
                            </svg>
                        </SimpleOverviewWidget>

                        <SimpleOverviewWidget :count="0" title="Weight">
                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-weight text-info"
                                 fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                 stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                                <path
                                    d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z"/>
                            </svg>
                        </SimpleOverviewWidget>

                        <SimpleOverviewWidget :count="0" title="Volume">
                            <svg class="icon icon-tabler icons-tabler-outline icon-tabler-scale text-info"
                                 fill="none"
                                 height="24" stroke="currentColor" stroke-linecap="round"
                                 stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M7 20l10 0"/>
                                <path d="M6 6l6 -1l6 1"/>
                                <path d="M12 3l0 17"/>
                                <path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                                <path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0"/>
                            </svg>
                        </SimpleOverviewWidget>
                    </div>
                </Tab>

                <Tab label="Shipment Details" name="tabShipment">

                    <div class="flex justify-end">
                        <div class="badge space-x-2 bg-success text-white shadow-soft shadow-success/50 mb-2">
                            <i
                                class="fa-solid fa-person-biking text-sm text-white dark:text-text-white"
                            ></i>
                            <span>Driver Picked</span>
                        </div>
                    </div>

                    <AccordionPanel show-panel title="Cargo Status">
                        <template #header-image>
                            <div
                                class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-status-change" fill="none"
                                     height="24" stroke="currentColor"
                                     stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                     width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M6 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M18 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M6 12v-2a6 6 0 1 1 12 0v2"/>
                                    <path d="M15 9l3 3l3 -3"/>
                                </svg>
                            </div>
                        </template>
                        <div class="px-4 py-4 sm:px-5">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">

                                <div>
                                    <ol class="steps">
                                        <li class="step before:bg-primary dark:before:bg-accent">
                                            <div
                                                class="step-header rounded-full bg-primary text-white dark:bg-accent"
                                            >
                                                1
                                            </div>
                                            <h3 class="text-slate-600 dark:text-navy-100">Job Added</h3>
                                            <h3 class="text-slate-600 dark:text-navy-100 font-bold">TESTDRIVER |
                                                2024-05-12 17:12</h3>
                                        </li>
                                        <li class="step before:bg-primary dark:before:bg-accent">
                                            <div
                                                class="step-header rounded-full bg-primary text-white dark:bg-accent"
                                            >
                                                2
                                            </div>
                                            <h3 class="text-slate-600 dark:text-navy-100">Tentative Pickup</h3>
                                            <h3 class="text-slate-600 dark:text-navy-100 font-bold">TESTDRIVER |
                                                2024-05-12 17:12</h3>
                                        </li>
                                        <li class="step before:bg-primary dark:before:bg-accent">
                                            <div
                                                class="step-header rounded-full bg-primary text-white dark:bg-accent"
                                            >
                                                3
                                            </div>
                                            <h3 class="text-slate-600 dark:text-navy-100">Picked on</h3>
                                            <h3 class="text-slate-600 dark:text-navy-100 font-bold">2024-05-26 19:26
                                                N/A</h3>
                                        </li>
                                    </ol>
                                </div>

                                <div>
                                    <ol class="steps">
                                        <li class="step before:bg-lime-600 dark:before:bg-accent">
                                            <div
                                                class="step-header rounded-full bg-lime-600 text-white dark:bg-accent"
                                            >
                                                1
                                            </div>
                                            <h3 class="text-slate-600 dark:text-navy-100">HBL Added on</h3>
                                            <h3 class="text-slate-600 dark:text-navy-100 font-bold">2024-05-26
                                                19:26</h3>
                                        </li>
                                        <li class="step before:bg-lime-600 dark:before:bg-accent">
                                            <div
                                                class="step-header rounded-full bg-lime-600 text-white dark:bg-accent"
                                            >
                                                2
                                            </div>
                                            <h3 class="text-slate-600 dark:text-navy-100">Cash Collected</h3>
                                            <h3 class="text-slate-600 dark:text-navy-100 font-bold">2024-05-26
                                                19:26</h3>
                                        </li>
                                        <li class="step before:bg-lime-600 dark:before:bg-accent">
                                            <div
                                                class="step-header rounded-full bg-lime-600 text-white dark:bg-accent"
                                            >
                                                3
                                            </div>
                                            <h3 class="text-slate-600 dark:text-navy-100">Warehouse</h3>
                                            <h3 class="text-slate-600 dark:text-navy-100 font-bold">2024-05-26
                                                19:26</h3>
                                        </li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </AccordionPanel>

                    <AccordionPanel show-panel title="Shipment Details">
                        <template #header-image>
                            <div
                                class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-package-export"
                                     fill="none" height="24" stroke="currentColor"
                                     stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                     width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5"/>
                                    <path d="M12 12l8 -4.5"/>
                                    <path d="M12 12v9"/>
                                    <path d="M12 12l-8 -4.5"/>
                                    <path d="M15 18h7"/>
                                    <path d="M19 15l3 3l-3 3"/>
                                </svg>
                            </div>
                        </template>
                        <div class="px-4 py-4 sm:px-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                                -
                            </div>
                        </div>
                    </AccordionPanel>

                    <AccordionPanel show-panel title="Complaint Details">
                        <template #header-image>
                            <div
                                class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-mood-sad-2" fill="none"
                                     height="24" stroke="currentColor"
                                     stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                     width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                                    <path d="M14.5 16.05a3.5 3.5 0 0 0 -5 0"/>
                                    <path d="M10 9.25c-.5 1 -2.5 1 -3 0"/>
                                    <path d="M17 9.25c-.5 1 -2.5 1 -3 0"/>
                                </svg>
                            </div>
                        </template>
                        <div class="px-4 py-4 sm:px-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                                <p>NO COMPLAINTS FOR THIS HBL</p>
                            </div>
                        </div>
                    </AccordionPanel>

                    <AccordionPanel show-panel title="Audit Details">
                        <template #header-image>
                            <div
                                class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-automation" fill="none"
                                     height="24" stroke="currentColor"
                                     stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                     width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path
                                        d="M13 20.693c-.905 .628 -2.36 .292 -2.675 -1.01a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.492 .362 1.716 2.219 .674 3.03"/>
                                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>
                                    <path d="M17 22l5 -3l-5 -3z"/>
                                </svg>
                            </div>
                        </template>
                        <div class="px-4 py-4 sm:px-5">
                            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                                <table class="is-hoverable w-full text-left">
                                    <thead>
                                    <tr>
                                        <th
                                            class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Detail
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Time
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Auth
                                        </th>
                                        <th
                                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Branch
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
                    </AccordionPanel>
                </Tab>

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
            </Tabs>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
        </template>
    </DialogModal>
</template>
