<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {ref} from "vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const activeTab = ref('tabHome')

</script>

<template>
    <DialogModal :maxWidth="'7xl'" :show="show" @close="close">
        <template #content>
            <div class="tabs flex flex-col">
                <div
                    class="is-scrollbar-hidden overflow-x-auto rounded-lg bg-slate-200 text-slate-600 dark:bg-navy-800 dark:text-navy-200"
                >
                    <div class="tabs-list flex px-1.5 py-1">
                        <button
                            :class="activeTab === 'tabHome' ? 'bg-white shadow dark:bg-navy-500 dark:text-navy-100' : 'hover:text-slate-800 focus:text-slate-800 dark:hover:text-navy-100 dark:focus:text-navy-100'"
                            class="btn shrink-0 space-x-2 px-3 py-1.5 font-medium"
                            @click="activeTab = 'tabHome'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span> HBL Details </span>
                        </button>
                        <button
                            :class="activeTab === 'tabStatus' ? 'bg-white shadow dark:bg-navy-500 dark:text-navy-100' : 'hover:text-slate-800 focus:text-slate-800 dark:hover:text-navy-100 dark:focus:text-navy-100'"
                            class="btn shrink-0 space-x-2 px-3 py-1.5 font-medium"
                            @click="activeTab = 'tabStatus'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>

                            <span>Status & Audit</span>
                        </button>
                        <button
                            :class="activeTab === 'tabDocuments' ? 'bg-white shadow dark:bg-navy-500 dark:text-navy-100' : 'hover:text-slate-800 focus:text-slate-800 dark:hover:text-navy-100 dark:focus:text-navy-100'"
                            class="btn shrink-0 space-x-2 px-3 py-1.5 font-medium"
                            @click="activeTab = 'tabDocuments'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span>Documents</span>
                        </button>
                        <button
                            :class="activeTab === 'tabPrint' ? 'bg-white shadow dark:bg-navy-500 dark:text-navy-100' : 'hover:text-slate-800 focus:text-slate-800 dark:hover:text-navy-100 dark:focus:text-navy-100'"
                            class="btn shrink-0 space-x-2 px-3 py-1.5 font-medium"
                            @click="activeTab = 'tabPrint'"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span>Print HBL & CR</span>
                        </button>
                    </div>
                </div>
                <div class="tab-content pt-4">
                    <div
                        v-show="activeTab === 'tabHome'"
                        x-transition:enter="transition-all duration-500 easy-in-out"
                        x-transition:enter-end="opacity-100 [transform:translate3d(0,0,0)]"
                        x-transition:enter-start="opacity-0 [transform:translate3d(1rem,0,0)]"
                    >
                        <AccordionPanel title="HBL Basic Details">
                            <template #header-image>
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-file-info w-full h-full" fill="none" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                        <path
                                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                        <path d="M11 14h1v4h1"/>
                                        <path d="M12 11h.01"/>
                                    </svg>
                                </div>
                            </template>
                            <div class="px-4 py-4 sm:px-5">
                                <div class="px-10 grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <ul>
                                        <li>Job Ref</li>
                                        <li>-</li>

                                        <li>Name</li>
                                        <li>-</li>

                                        <li>Passport Number</li>
                                        <li>-</li>

                                        <li>Warehouse Location</li>
                                        <li>-</li>
                                    </ul>

                                    <ul>
                                        <li>HBL</li>
                                        <li>-</li>

                                        <li>Contact</li>
                                        <li>-</li>

                                        <li>IQ Number</li>
                                        <li>-</li>

                                        <li>Delivery Type</li>
                                        <li>-</li>
                                    </ul>

                                    <ul>
                                        <li>CR</li>
                                        <li>-</li>

                                        <li>Address</li>
                                        <li>-</li>

                                        <li>Location</li>
                                        <li>-</li>

                                        <li>Cargo Mode</li>
                                        <li>-</li>
                                    </ul>
                                </div>
                            </div>
                        </AccordionPanel>

                        <AccordionPanel title="Consignee Details">
                            <template #header-image>
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon h-full w-full" fill="none" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path
                                            d="M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z"/>
                                        <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"/>
                                        <path d="M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707"/>
                                    </svg>
                                </div>
                            </template>
                            <div class="px-10 grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <ul>
                                    <li>Name</li>
                                    <li>-</li>

                                    <li>Contact</li>
                                    <li>-</li>
                                </ul>

                                <ul>
                                    <li>Address</li>
                                    <li>-</li>

                                    <li>Nic/PP No</li>
                                    <li>-</li>
                                </ul>

                                <ul>
                                    <li>Note</li>
                                    <li>-</li>
                                </ul>
                            </div>
                        </AccordionPanel>

                        <AccordionPanel title="Package Details">
                            <template #header-image>
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-package w-full h-full" fill="none" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                                        <path d="M12 12l8 -4.5"/>
                                        <path d="M12 12l0 9"/>
                                        <path d="M12 12l-8 -4.5"/>
                                        <path d="M16 5.25l-8 4.5"/>
                                    </svg>
                                </div>
                            </template>
                            <div class="my-4 flex justify-end space-x-4">
                                <div class="flex w-28 shrink-0 flex-col items-center">
                                    <div class="z-10 size-10 bg-pink-600 items-center flex justify-center text-white rounded-full">
                                        <svg  class="icon icon-tabler icons-tabler-outline icon-tabler-package"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                                    </div>

                                    <div class="card -mt-5 w-full rounded-2xl px-3 py-5 text-center text-white bg-navy-900">
                                        <p class="mt-3 text-base font-medium dark:text-navy-100">
                                            Packages
                                        </p>
                                        <div class="mt-6 flex justify-center space-x-1 font-inter">
                                            <p class="text-4xl font-medium dark:text-navy-100">
                                                2
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex w-28 shrink-0 flex-col items-center">
                                    <div class="z-10 size-10 bg-pink-600 items-center flex justify-center text-white rounded-full">
                                        <svg  class="icon icon-tabler icons-tabler-outline icon-tabler-weight"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.835 9h10.33a1 1 0 0 1 .984 .821l1.637 9a1 1 0 0 1 -.984 1.179h-13.604a1 1 0 0 1 -.984 -1.179l1.637 -9a1 1 0 0 1 .984 -.821z" /></svg>
                                    </div>

                                    <div class="card -mt-5 w-full rounded-2xl px-3 py-5 text-center text-white bg-navy-900">
                                        <p class="mt-3 text-base font-medium dark:text-navy-100">
                                            Weight
                                        </p>
                                        <div class="mt-6 flex justify-center space-x-1 font-inter">
                                            <p class="text-4xl font-medium dark:text-navy-100">
                                                2
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex w-28 shrink-0 flex-col items-center">
                                    <div class="z-10 size-10 bg-pink-600 items-center flex justify-center text-white rounded-full">
                                        <svg  class="icon icon-tabler icons-tabler-outline icon-tabler-scale"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M7 20l10 0" /><path d="M6 6l6 -1l6 1" /><path d="M12 3l0 17" /><path d="M9 12l-3 -6l-3 6a3 3 0 0 0 6 0" /><path d="M21 12l-3 -6l-3 6a3 3 0 0 0 6 0" /></svg>
                                    </div>

                                    <div class="card -mt-5 w-full rounded-2xl px-3 py-5 text-center text-white bg-navy-900">
                                        <p class="mt-3 text-base font-medium dark:text-navy-100">
                                            Volume
                                        </p>
                                        <div class="mt-6 flex justify-center space-x-1 font-inter">
                                            <p class="text-4xl font-medium dark:text-navy-100">
                                                2
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="is-scrollbar-hidden min-w-full overflow-x-auto my-10">
                                <table class="is-hoverable w-full text-left">
                                    <thead>
                                    <tr>
                                        <th
                                            class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Length (CM)
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Width
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Height
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Weight
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Volume (M.CU)
                                        </th>
                                        <th
                                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Remarks
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">1</td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">Cy Ganderton</td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            Quality Control Specialist
                                        </td>
                                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">Blue</td>
                                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">Blue</td>
                                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">Blue</td>
                                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">Blue</td>
                                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">Blue</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </AccordionPanel>

                        <AccordionPanel title="Payment Details">
                            <template #header-image>
                                <div
                                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg class="w-full h-full" fill="none" stroke="currentColor"
                                         stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </template>
                            <div class="px-10 grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <ul>
                                    <li>Grand Total</li>
                                    <li>-</li>
                                </ul>

                                <ul>
                                    <li>Paid Amount</li>
                                    <li>-</li>
                                </ul>
                            </div>
                        </AccordionPanel>
                    </div>
                    <div
                        v-show="activeTab === 'tabStatus'"
                        x-transition:enter="transition-all duration-500 easy-in-out"
                        x-transition:enter-end="opacity-100 [transform:translate3d(0,0,0)]"
                        x-transition:enter-start="opacity-0 [transform:translate3d(1rem,0,0)]"
                    >
                        <div>
                            <p>
                                Cras iaculis ipsum quis lectus faucibus, in mattis nulla molestie.
                                Vestibulum vel tristique libero. Morbi vulputate odio at viverra
                                sodales. Curabitur accumsan justo eu libero porta ultrices vitae eu
                                leo.
                            </p>
                            <div class="flex space-x-2 pt-3">
                                <a
                                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                                    href="#"
                                >
                                    Tag 1
                                </a>
                                <a
                                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                                    href="#"
                                >
                                    Tag 2
                                </a>
                            </div>

                            <p class="pt-3 text-xs text-slate-400 dark:text-navy-300">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore
                                dolore non atque?
                            </p>
                        </div>
                    </div>
                    <div
                        v-show="activeTab === 'tabDocuments'"
                        x-transition:enter="transition-all duration-500 easy-in-out"
                        x-transition:enter-end="opacity-100 [transform:translate3d(0,0,0)]"
                        x-transition:enter-start="opacity-0 [transform:translate3d(1rem,0,0)]"
                    >
                        <div>
                            <p>
                                Pellentesque pulvinar, sapien eget fermentum sodales, felis lacus
                                viverra magna, id pulvinar odio metus non enim. Ut id augue
                                interdum, ultrices felis eu, tincidunt libero.
                            </p>
                            <div class="flex space-x-2 pt-3">
                                <a
                                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                                    href="#"
                                >
                                    Tag 1
                                </a>
                                <a
                                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                                    href="#"
                                >
                                    Tag 2
                                </a>
                            </div>

                            <p class="pt-3 text-xs text-slate-400 dark:text-navy-300">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore
                                dolore non atque?
                            </p>
                        </div>
                    </div>
                    <div
                        v-show="activeTab === 'tabPrint'"
                        x-transition:enter="transition-all duration-500 easy-in-out"
                        x-transition:enter-end="opacity-100 [transform:translate3d(0,0,0)]"
                        x-transition:enter-start="opacity-0 [transform:translate3d(1rem,0,0)]"
                    >
                        <div>
                            <p>
                                Pellentesque pulvinar, sapien eget fermentum sodales, felis lacus
                                viverra magna, id pulvinar odio metus non enim. Ut id augue
                                interdum, ultrices felis eu, tincidunt libero.
                            </p>
                            <div class="flex space-x-2 pt-3">
                                <a
                                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                                    href="#"
                                >
                                    Tag 1
                                </a>
                                <a
                                    class="tag rounded-full border border-primary text-primary dark:border-accent-light dark:text-accent-light"
                                    href="#"
                                >
                                    Tag 2
                                </a>
                            </div>

                            <p class="pt-3 text-xs text-slate-400 dark:text-navy-300">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore
                                dolore non atque?
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
        </template>
    </DialogModal>
</template>

<style scoped>

</style>
