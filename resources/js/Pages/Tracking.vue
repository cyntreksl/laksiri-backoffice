<script setup xmlns="http://www.w3.org/1999/html">
import {onMounted, ref, watch} from "vue";
import moment from "moment";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Head, Link} from "@inertiajs/vue3";
import DashboardMeet from "../../images/illustrations/dashboard-meet.svg";
import DashboardMeetDark from "../../images/illustrations/dashboard-meet-dark.svg";

const props = defineProps({
    reference: {
        type: String,
        required: false,
    }
});

const reference = ref(props.reference ?? null);
const errorMessage = ref('');
const isLoading = ref(false);
const hblStatus = ref([]);

const handleSubmit = async () => {
    errorMessage.value = '';

    if (!reference.value) {
        errorMessage.value = "Please enter the valid reference first!";
        return;
    }

    isLoading.value = true;

    try {
        const response = await fetch(`/get-hbl-status-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
            },
        });

        if (!response.ok) {
            if (response.status === 404) {
                hblStatus.value = [];
                errorMessage.value = 'Invalid reference!';
            }
            throw new Error('Network response was not ok.');
        } else {
            hblStatus.value = await response.json();
        }

    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
};
// Call handleSubmit if a reference is provided initially
onMounted(() => {
    if (reference.value) {
        handleSubmit();
    }
});

const hblStatusColor = (status) => {
    switch (status) {
        case 'HBL Preparation by warehouse':
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
        default:
            return 'bg-gray-400';
    }
};
</script>

<template>
    <Head title="Tracking"/>
    <div class="text-right m-2">
        <Link
            :href="route('login')"
            class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
        >
            Back To Login
        </Link>
    </div>

    <div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
        <div class="flex w-full place-items-center justify-center">
            <div class="w-full max-w-lg p-6">
                <h1 class="text-3xl font-bold text-center mb-5 text-black">Track Your HBL Status</h1>

                <div class="card mt-5 rounded-lg p-5 lg:p-7">

                    <!-- Error Message -->
                    <div
                        v-if="errorMessage"
                        class="mb-3 alert flex overflow-hidden rounded-lg bg-error/10 text-error dark:bg-error/15"
                    >
                        <div class="flex flex-1 items-center space-x-3 p-4">
                            <svg
                                class="size-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                />
                            </svg>
                            <div class="flex-1">{{ errorMessage }}</div>
                        </div>
                        <div class="w-1.5 bg-error"></div>
                    </div>

                    <!-- Reference Input and Submit Button -->
                    <div class="flex space-x-2">
                        <div class="w-full">
                            <input
                                v-model="reference"
                                class="form-input peer rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent w-full"
                                placeholder="Enter HBL Reference / Number"
                                type="text"
                            />
                        </div>
                        <PrimaryButton @click.prevent="handleSubmit">
                            <svg
                                class="icon icon-tabler icons-tabler-outline icon-tabler-search mr-2"
                                fill="none"
                                height="24"
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                width="24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path d="M0 0h24v24H0z" fill="none" stroke="none" />
                                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                <path d="M21 21l-6 -6" />
                            </svg>
                            Find
                        </PrimaryButton>
                    </div>

                    <!-- Loading State -->
                    <div
                        v-if="isLoading"
                        class="flex animate-pulse flex-col space-y-4 border border-slate-150 dark:border-navy-500 mt-10"
                    >
                        <div class="px-4 pt-4">
                            <div class="h-8 w-10/12 rounded-full bg-slate-150 dark:bg-navy-500"></div>
                        </div>
                        <div class="h-48 w-full bg-slate-150 dark:bg-navy-500"></div>
                        <div class="flex flex-1 flex-col justify-between space-y-4 p-4">
                            <div class="h-4 w-9/12 rounded bg-slate-150 dark:bg-navy-500"></div>
                            <div class="h-4 w-6/12 rounded bg-slate-150 dark:bg-navy-500"></div>
                            <div class="h-4 w-full rounded bg-slate-150 dark:bg-navy-500"></div>
                        </div>
                    </div>

                    <!-- HBL Status Timeline -->
                    <ol v-if="hblStatus.length > 0 && !isLoading" class="timeline max-w-sm mt-10">
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

                    <!-- No Results Found -->
                    <div v-if="hblStatus.length === 0 && !isLoading" class="mt-10 text-center text-slate-500">
                        <p>No results found. Please enter a valid reference number.</p>
                    </div>

                </div>
                <template v-if="hblStatus.length === 0 && ! isLoading">
                    <img
                        :src="DashboardMeet"
                        alt="image"
                        class="w-full mt-10"
                        x-show="!$store.global.isDarkModeEnabled "
                    />
                    <img
                        :src="DashboardMeetDark"
                        alt="image"
                        class="w-full mt-10"
                        x-show="$store.global.isDarkModeEnabled"
                    />
                </template>
            </div>

        </div>
    </div>
</template>
