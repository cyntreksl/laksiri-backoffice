<script setup>
import Tab from "@/Components/Tab.vue";
import AccordionPanel from "@/Components/AccordionPanel.vue";
import {onMounted, ref, watch} from "vue";
import moment from "moment";
import NotFound from '@/../images/illustrations/empty-girl-box.svg';
import InfoDisplay from "@/Pages/Common/Components/InfoDisplay.vue";
import AuditDetails from "@/Pages/Common/Components/AuditDetails.vue";
import PostSkeleton from "@/Components/PostSkeleton.vue";
import {usePage} from '@inertiajs/vue3';

const props = defineProps({
    hbl: {
        type: Object,
        default: () => {
        },
    },
    pickup: {
        type: Object,
        default: () => ({}),
    },
    showAuditDetails: {
        type: Boolean,
        default: true,
    },
    showCallFlags: {
        type: Boolean,
        default: true,
    },
})
watch(
    () => props.pickup,
    (newVal) => {
        console.log("Pickup data updated in child:", newVal);
    },
    { immediate: true }
);

const pickupStatus = ref([]);
const hblStatus = ref([]);
const isLoadingPickupStatus = ref(false);

const fetchPickupStatus = async () => {
    isLoadingPickupStatus.value = true;

    try {
        const id = props.hbl.pickup_id ? props.hbl.pickup_id : props.pickup?.id;
        const response = await fetch(`get-pickup-status/${id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
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
    } finally {
        isLoadingPickupStatus.value = false;
    }
}

const isLoadingHBLStatus = ref(false);

const fetchHBLStatus = async () => {
    isLoadingHBLStatus.value = true;

    try {
        const response = await fetch(`/get-hbl-status/${props.hbl?.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
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
    } finally {
        isLoadingHBLStatus.value = false;
    }
}

const fetchHBLStatusByPickup = async () => {
    isLoadingHBLStatus.value = true;

    try {
        const response = await fetch(`/get-hbl-status-by-pickup/${props.pickup?.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
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
    } finally {
        isLoadingHBLStatus.value = false;
    }
}

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
        case 'HBL Preparation by driver':
            return 'bg-primary';
        case 'Cash Received by Accountant':
            return 'bg-secondary';
        case 'Container Loading':
            return 'bg-success';
        case 'Container Shipped':
            return 'bg-error';
        case 'Container Arrival':
            return 'bg-slate-500';
        case 'Blocked By RTF':
            return 'bg-red-500';
        case 'Revert To Cash Settlement':
            return 'bg-amber-400';
        case 'Container Unloaded in Colombo':
            return 'bg-gray-400';
    }
};

const pickup = ref({});
const isLoadingPickup = ref(false);

const fetchPickup = async () => {
    isLoadingPickup.value = true;

    try {
        const response = await fetch(`/pickups/${props.hbl.pickup_id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            pickup.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingPickup.value = false;
    }
}

const container = ref({});
const isLoadingContainer = ref(false);

const fetchContainer = async () => {
    isLoadingContainer.value = true;

    try {
        const response = await fetch(`/get-container/${props.hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            container.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingContainer.value = false;
    }
}

const unloadingIssues = ref({});
const isLoadingUnloadingIssues = ref(false);

const fetchUnloadingIssues = async () => {
    isLoadingUnloadingIssues.value = true;

    try {
        const response = await fetch(`/get-unloading-issues-by-hbl/${props.hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            unloadingIssues.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingUnloadingIssues.value = false;
    }
}

const logs = ref({});
const isLoadingLogs = ref(false);

const fetchLogs = async () => {
    isLoadingLogs.value = true;

    try {
        const response = await fetch(`/get-logs/${props.hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            logs.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingLogs.value = false;
    }
}

const callFlags = ref({});
const isLoadingCallFlags = ref(false);

const fetchCallFlags = async () => {
    isLoadingCallFlags.value = true;

    try {
        const response = await fetch(`/get-call-flags/${props.hbl.id}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            callFlags.value = data;
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoadingCallFlags.value = false;
    }
}

watch(() => props.hbl, (newVal) => {
    if (newVal !== undefined) {
        fetchPickupStatus();
        fetchHBLStatus();
        fetchPickup();
        fetchLogs();
        fetchCallFlags();
        fetchUnloadingIssues();
        fetchContainer();
    }
});

watch(() => props.pickup, (newVal) => {
    if (newVal !== undefined) {
        fetchPickupStatus();
        if(props.hbl.id){
            fetchHBLStatus()
        }else fetchHBLStatusByPickup();
    }
});

onMounted(() => {
    if (props.hbl !== null && props.hbl.id !== undefined) {
        fetchPickupStatus();
        fetchHBLStatus();
        fetchPickup();
        fetchLogs();
        fetchCallFlags();
        fetchUnloadingIssues();
        fetchContainer();
    }
});
</script>

<template>
    <Tab label="Status & Audit" name="tabStatus">

        <div v-if="hbl.pickup_id" class="flex justify-end">
            <div class="badge space-x-2 bg-success text-white shadow-soft shadow-success/50 mb-2">
                <i
                    class="fa-solid fa-person-biking text-sm text-white dark:text-text-white"
                ></i>
                <span>Driver Picked at {{ pickup.pickup_date }}</span>
            </div>
        </div>

        <PostSkeleton v-if="isLoadingPickupStatus && isLoadingHBLStatus"/>

        <AccordionPanel v-else show-panel title="Cargo Status">
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
                        <div class="flex items-center space-x-4">
                            <div
                                class="relative flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/20 dark:bg-accent">
                                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-truck text-primary"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/>
                                </svg>
                            </div>
                            <span class="font-medium text-slate-700 dark:text-navy-100">Pickup Status</span>
                        </div>
                        <div v-if="pickupStatus.length > 0" class="mt-5">
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
                                            <span class="text-xs text-slate-400 dark:text-navy-300"
                                            >{{ moment(log.created_at).format('YYYY-MM-DD hh:mm') }}</span
                                            >
                                        </div>
                                        <p class="py-1">{{ log?.created_by }}</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        <div v-if="pickupStatus.length === 0" class="w-full flex justify-center">
                            <img :src="NotFound" alt="image"
                                 class="w-1/4 mt-10"/>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center space-x-4">
                            <div
                                class="relative flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/20 dark:bg-accent">
                                <svg class="icon icon-tabler icons-tabler-outline icon-tabler-app-window text-primary"
                                     fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                    <path
                                        d="M3 5m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"/>
                                    <path d="M6 8h.01"/>
                                    <path d="M9 8h.01"/>
                                </svg>
                            </div>
                            <span class="font-medium text-slate-700 dark:text-navy-100">HBL Status</span>
                        </div>

                        <div v-if="hblStatus.length > 0" class="mt-5">
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
                                            <span class="text-xs text-slate-400 dark:text-navy-300"
                                            >{{ moment(log.created_at).format('YYYY-MM-DD hh:mm') }}</span
                                            >
                                        </div>
                                        <p class="py-1">{{ log?.created_by }}</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        <div v-if="hblStatus.length === 0">
                            <img :src="NotFound" alt="image"
                                 class="w-1/4 mt-10"/>
                        </div>
                    </div>
                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoadingContainer"/>

        <AccordionPanel v-else show-panel title="Shipment Details">
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
                <div v-if="Object.values(container).length !== 0"
                     class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-8">
                    <InfoDisplay :value="container?.note" label="Name / Note"/>

                    <InfoDisplay :value="container?.reference" label="Ref"/>

                    <InfoDisplay :value="container?.loading_started_at" label="Created Date"/>

                    <InfoDisplay :value="container?.estimated_time_of_departure" label="ETD"/>

                    <InfoDisplay :value="container?.estimated_time_of_arrival" label="ETA"/>

                    <InfoDisplay :value="container?.reached_date" label="Reached Date"/>

                    <InfoDisplay :value="container?.cargo_type" label="Cargo Mode"/>

                    <InfoDisplay :value="hbl?.hbl_type" label="Delivery Type"/>

                    <InfoDisplay :value="container.awb_number || container.bl_number" label="AWB/BL"/>

                    <InfoDisplay :value="'-'" label="MHBL"/>

                    <InfoDisplay :value="container?.is_reached ? 'Yes' : 'No'" label="Has Reached Destination?"/>

                    <InfoDisplay :value="'-'" label="Has Custom Cleared?"/>
                </div>

                <div v-if="Object.values(container).length === 0" class="w-full flex justify-center">
                    <img :src="NotFound" alt="image"
                         class="w-1/4 mt-10"/>
                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoadingUnloadingIssues"/>

        <AccordionPanel v-else show-panel title="Complaint Details">
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
                <div v-if="unloadingIssues.length > 0"
                     class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <table class="is-hoverable w-full text-left">
                        <thead>
                        <tr>
                            <th
                                class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Package
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Issue
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Type
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Is Damaged
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                RTF
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 rounded-r-lg px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Is Fixed
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="issue in unloadingIssues" :key="issue.id"
                            class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                                {{ issue.hbl_package?.package_type ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ issue.issue ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ issue.type ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <svg v-if="issue.is_damaged" class="size-6 text-success" fill="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                          fill-rule="evenodd"/>
                                </svg>

                                <svg v-else class="size-6 text-error" fill="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                          fill-rule="evenodd"/>
                                </svg>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <svg v-if="issue.rtf" class="size-6 text-success" fill="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                          fill-rule="evenodd"/>
                                </svg>

                                <svg v-else class="size-6 text-error" fill="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                          fill-rule="evenodd"/>
                                </svg>
                            </td>
                            <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                                <svg v-if="issue.is_fixed" class="size-6 text-success" fill="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                          fill-rule="evenodd"/>
                                </svg>

                                <svg v-else class="size-6 text-error" fill="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path clip-rule="evenodd"
                                          d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                          fill-rule="evenodd"/>
                                </svg>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="w-full flex justify-center">
                    <img :src="NotFound" alt="image"
                         class="w-1/4 mt-10"/>
                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoadingLogs"/>

        <AccordionPanel v-else show-panel title="Audit Details" v-if="props.showAuditDetails">
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
                <div v-if="logs.length > 0" class="is-scrollbar-hidden min-w-full overflow-x-auto">
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
                                Date Time
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Branch
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Auth
                            </th>
                            <th
                                class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Inspect
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="activity in logs"
                            class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                                {{ activity.description }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ moment(activity.created_at).format('YYYY-MM-DD H:mm:ss') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ hbl.branch_name }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ activity.causer?.name }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                <AuditDetails :old-properties="activity.properties?.old" :properties="activity.properties?.attributes"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </AccordionPanel>

        <PostSkeleton v-if="isLoadingCallFlags"/>

        <AccordionPanel v-else v-if="props.showCallFlags" show-panel title="Call Flags">
            <template #header-image>
                <div
                    class="flex size-8 items-center justify-center rounded-lg p-1 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-phone-spark" fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                        <path
                            d="M11.584 19.225a16 16 0 0 1 -8.584 -13.225a2 2 0 0 1 2 -2h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l.65 .26"/>
                        <path
                            d="M19 22.5a4.75 4.75 0 0 1 3.5 -3.5a4.75 4.75 0 0 1 -3.5 -3.5a4.75 4.75 0 0 1 -3.5 3.5a4.75 4.75 0 0 1 3.5 3.5"/>
                    </svg>
                </div>
            </template>
            <div class="px-4 py-4 sm:px-5">
                <div v-if="callFlags.length > 0" class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <table class="is-hoverable w-full text-left">
                        <thead>
                        <tr>
                            <th
                                class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Caller Name
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Date
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Notes
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Followup Date
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Created At
                            </th>
                            <th
                                class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                            >
                                Auth
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="flag in callFlags"
                            class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                                {{ flag.caller }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ flag.date }}
                            </td>
                            <td class="px-4 py-3 sm:px-5">
                                {{ flag.notes }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ flag.followup_date }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ moment(flag.created_at).format('YYYY-MM-DD H:mm:ss') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 rounded-r-lg sm:px-5">
                                {{ flag.causer?.name }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-if="Object.values(container).length === 0" class="w-full flex justify-center">
                <img :src="NotFound" alt="image" class="w-1/4 mt-10" />
            </div>
        </AccordionPanel>
    </Tab>
</template>
