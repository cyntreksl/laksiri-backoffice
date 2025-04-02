<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DatePicker from "@/Components/DatePicker.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import {router, usePage} from "@inertiajs/vue3";
import {reactive, ref, watch} from "vue";
import moment from "moment";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {push} from "notivue";
import SearchSVG from "@/../images/illustrations/search.svg";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";

const props = defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    deliveries: {
        type: Object,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => {
        },
    },
});

const form = reactive({
    driverId: props.filters.driverId || null,
});

const initialDeliveries = ref(JSON.stringify(props.deliveries));
const localDeliveries = ref([...props.deliveries]);
const hasOrderChanged = ref(false);

const checkOrderChange = () => {
    hasOrderChanged.value = JSON.stringify(localDeliveries.value) !== initialDeliveries;
};

watch(localDeliveries, checkOrderChange, {deep: true});

const handleSearch = () => {
    if (!form.driverId) {
        push.error("Please select a driver");
    }

    router.get(route("delivery.ordering"), form, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            router.visit(
                route("delivery.ordering", {
                    driverId: props.filters.driverId,
                    fromDate: props.filters.fromDate,
                    toDate: props.filters.toDate,
                })
            );
        },
    });
};

const handleMoveUp = (index) => {
    if (index > 0) {
        [localDeliveries.value[index - 1], localDeliveries.value[index]] = [
            localDeliveries.value[index],
            localDeliveries.value[index - 1],
        ];
        checkOrderChange();
    } else {
        console.log("This item is already at the top.");
    }
};

const handleMoveDown = (index) => {
    if (index < props.deliveries.length - 1) {
        [localDeliveries.value[index + 1], localDeliveries.value[index]] = [
            localDeliveries.value[index],
            localDeliveries.value[index + 1],
        ];
        checkOrderChange();
    } else {
        console.log("This item is already at the bottom.");
    }
};

const handleSave = () => {
    const updatedDeliveries = localDeliveries.value.map((delivery, index) => ({
        ...delivery,
        deliver_order: index + 1,
    }));


    router.put(
        route("delivery.update-order"),
        {deliveries: updatedDeliveries},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                push.success("Delivery order rearranged!");
                initialDeliveries.value = JSON.stringify(localDeliveries.value); // Reset initial order
                hasOrderChanged.value = false; // Disable save button
            },
            onError: () => {
                push.error("Something went to wrong!");
            },
        }
    );
};

const hblId = ref(null);
const showConfirmViewHBLModal = ref(false);

const confirmViewDelivery = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
};
</script>

<template>
    <AppLayout title="Pending Pickups">
        <template #header>Pending Pickups</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2
                                class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Deliver Ordering
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-5 mt-10 mx-5">
                            <div>
                                <InputLabel value="Driver"/>
                                <select
                                    v-model="form.driverId"
                                    class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option :value="null" disabled>Select Driver</option>
                                    <option
                                        v-for="driver in drivers"
                                        :key="driver.id"
                                        :value="driver.id"
                                    >
                                        {{ driver.name }}
                                    </option>
                                </select>
                            </div>
                            <div v-if="false">
                                <InputLabel value="From Date"/>
                                <DatePicker v-model="form.fromDate"/>
                            </div>

                            <div v-if="false">
                                <InputLabel value="To Date"/>
                                <DatePicker v-model="form.toDate"/>
                            </div>

                            <div class="mt-5">
                                <PrimaryOutlineButton @click.prevent="handleSearch">
                                    Search
                                </PrimaryOutlineButton>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="localDeliveries.length > 0" class="mx-5 mb-5">
                    <div class="flex my-5 space-x-4 items-center">
                        <PrimaryButton
                            :disabled="!hasOrderChanged"
                            @click.prevent="handleSave"
                        >
                            Save Order
                            <svg
                                class="w-6 h-6 ml-3"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </PrimaryButton>
                        <p class="text-error">Total Records {{ localDeliveries.length }}</p>
                    </div>

                    <div class="min-w-full overflow-y-auto h-[500px]">
                        <table class="is-hoverable w-full text-left">
                            <thead>
                            <tr>
                                <th
                                    class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Order
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-center"
                                >
                                    Move
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Show
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Reference
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Name
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Address
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Created Date
                                </th>
                                <th
                                    class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                >
                                    Note
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="(delivery, index) in localDeliveries"
                                class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500"
                            >
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <button
                                        :disabled="index === 0"
                                        class="btn size-9 rounded-full p-0 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25 disabled:pointer-events-none disabled:select-none disabled:opacity-60 disabled:text-gray-500"
                                        @click.prevent="handleMoveUp(index)"
                                    >
                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        :disabled="index === deliveries.length - 1"
                                        class="btn size-9 rounded-full p-0 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25 disabled:pointer-events-none disabled:select-none disabled:opacity-60 disabled:text-gray-500"
                                        @click.prevent="handleMoveDown(index)"
                                    >
                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <button
                                        class="btn size-9 rounded-full p-0 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                                        @click.prevent="confirmViewDelivery(delivery.hbl.id)"
                                    >
                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ delivery.hbl.hbl_number }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-3 py-3 font-medium text-slate-700 dark:text-navy-100 lg:px-5"
                                >
                                    {{ delivery.hbl.hbl_name }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{
                                        delivery.hbl.address.length > 20 ? delivery.hbl.address.substring(0, 20) + "..." : delivery.hbl.address
                                    }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ moment(delivery.created_at).format("YYYY-MM-DD") }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ delivery.notes }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="flex justify-center px-5 my-10">
                    <img :src="SearchSVG" alt="search" class="w-80"/>
                </div>
            </div>
        </div>

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeModal"
            @update:show="showConfirmViewHBLModal = $event"
        />
    </AppLayout>
</template>
