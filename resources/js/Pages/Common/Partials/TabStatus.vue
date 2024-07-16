<script setup>
import Tab from "@/Components/Tab.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import {ref, watch} from "vue";
import moment from "moment";

const props = defineProps({
    hblId: {
        type: Number,
        required: true,
    }
})

const pickupStatus = ref([]);
const hblStatus = ref([]);

const fetchPickupStatus = async () => {
    try {
        const response = await fetch(`get-pickup-status/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            pickupStatus.value = data.status;
        }

    } catch (error) {
        console.log(error);
    }
}

const fetchHBLStatus = async () => {
    try {
        const response = await fetch(`get-hbl-status/${props.hblId}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            hblStatus.value = data.status;
        }

    } catch (error) {
        console.log(error);
    }
}

watch(() => props.hblId, () => {
    fetchPickupStatus()
    fetchHBLStatus()
})


const pickupStatusColor = (status) => {
    switch (status) {
        case 'Pickup Created':
            return 'bg-primary';
        case 'Driver Assigned':
            return 'bg-secondary';
        case 'Cargo collected by driver':
            return 'bg-success';
        case 'Pickup Exception: Assigned Driver':
            return 'bg-error';
    }
};

const hblStatusColor = (status) => {
    switch (status) {
        case 'HBL Preparation by warehouse':
            return 'bg-primary';
        case 'Cash Received by Accountant':
            return 'bg-secondary';
        case 'Container Loading':
            return 'bg-success';
        case 'Container Shipped':
            return 'bg-error';
        case 'Container Arrival':
            return 'bg-slate-500';
    }
};
</script>

<template>
    <Tab label="Status & Audit" name="tabStatus">

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

                    <div v-if="pickupStatus">
                        <ol class="timeline max-w-sm">
                            <li v-for="(log, index) in pickupStatus" class="timeline-item">
                                <div
                                    :class="`${pickupStatusColor(log.status)} timeline-item-point rounded-full dark:bg-accent`"
                                >
                                     <span
                                         v-if="index === Object.keys(pickupStatus).length - 1"
                                         :class="`inline-flex h-full w-full animate-ping rounded-full ${pickupStatusColor(log.status)} opacity-80`"
                                     ></span>
                                </div>
                                <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                    <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                        <p
                                            class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                        >
                                            {{ log.status }}
                                        </p>
                                    </div>
                                    <p class="py-1">{{ moment(log.created_at).format('YYYY-MM-DD hh:mm') }}</p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <div v-if="hblStatus">
                        <ol class="timeline max-w-sm">
                            <li v-for="(log, index) in hblStatus" class="timeline-item">
                                <div
                                    :class="`${hblStatusColor(log.status)} timeline-item-point rounded-full dark:bg-accent`"
                                >
                                     <span
                                         v-if="index === Object.keys(hblStatus).length - 1"
                                         :class="`inline-flex h-full w-full animate-ping rounded-full ${hblStatusColor(log.status)} opacity-80`"
                                     ></span>
                                </div>
                                <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                    <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                        <p
                                            class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                        >
                                            {{ log.status }}
                                        </p>
                                    </div>
                                    <p class="py-1">{{ moment(log.created_at).format('YYYY-MM-DD hh:mm') }}</p>
                                </div>
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
</template>
