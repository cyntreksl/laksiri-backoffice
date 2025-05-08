<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DatePicker from "@/Components/DatePicker.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {router, usePage} from "@inertiajs/vue3";
import {reactive, ref, watch} from "vue";
import moment from "moment";
import {push} from "notivue";
import SearchSVG from "@/../images/illustrations/search.svg";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import Card from 'primevue/card';
import Select from 'primevue/select';
import Button from 'primevue/button';

const props = defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    pickups: {
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
    fromDate: props.filters.fromDate || moment(new Date()).subtract(7, 'd').format("YYYY-MM-DD"),
    toDate: props.filters.toDate || moment(new Date()).format("YYYY-MM-DD"),
    driverId: props.filters.driverId || null,
});

const initialPickups = ref(JSON.stringify(props.pickups));
const localPickups = ref([...props.pickups]);
const hasOrderChanged = ref(false);

const checkOrderChange = () => {
    hasOrderChanged.value = JSON.stringify(localPickups.value) !== initialPickups;
};

watch(localPickups, checkOrderChange, {deep: true});

const handleSearch = () => {
    if (!form.fromDate) {
        push.error("From date is required!");
    }

    if (!form.toDate) {
        push.error("To date is required!");
    }

    if (!form.driverId) {
        push.error("Please select a driver");
    }

    router.get(route("pickups.ordering"), form, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            router.visit(
                route("pickups.ordering", {
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
        [localPickups.value[index - 1], localPickups.value[index]] = [
            localPickups.value[index],
            localPickups.value[index - 1],
        ];
        checkOrderChange();
    } else {
        console.log("This item is already at the top.");
    }
};

const handleMoveDown = (index) => {
    if (index < props.pickups.length - 1) {
        [localPickups.value[index + 1], localPickups.value[index]] = [
            localPickups.value[index],
            localPickups.value[index + 1],
        ];
        checkOrderChange();
    } else {
        console.log("This item is already at the bottom.");
    }
};

const handleSave = () => {
    const updatedPickups = localPickups.value.map((pickup, index) => ({
        ...pickup,
        pickup_order: index + 1,
    }));

    router.put(
        route("pickups.update-order"),
        {pickups: updatedPickups},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                push.success("Pickup order rearranged!");
                initialPickups.value = JSON.stringify(localPickups.value); // Reset initial order
                hasOrderChanged.value = false; // Disable save button
            },
            onError: () => {
                push.error("Something went to wrong!");
            },
        }
    );
};

const pickupId = ref(null);
const showConfirmViewPickupModal = ref(false);

const confirmViewPickup = async (id) => {
    pickupId.value = id;
    showConfirmViewPickupModal.value = true;
};

const closeModal = () => {
    showConfirmViewPickupModal.value = false;
    pickupId.value = null;
};
</script>

<template>
    <AppLayout title="Pickup Priority Ordering">
        <template #header>Pickup Priority Ordering</template>

        <Breadcrumb/>

        <div class="my-5">
            <Card>
                <template #title>Pickup Priority Ordering</template>
                <template #content>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-5 mt-10 mx-5 items-center">
                        <div>
                            <InputLabel value="From Date"/>
                            <DatePicker v-model="form.fromDate"/>
                        </div>

                        <div>
                            <InputLabel value="To Date"/>
                            <DatePicker v-model="form.toDate"/>
                        </div>

                        <div>
                            <InputLabel value="Driver"/>
                            <Select v-model="form.driverId" :options="drivers" :showClear="true" class="w-full" filter input-id="user" option-label="name" option-value="id" placeholder="Select Driver"/>
                        </div>

                        <div class="mt-5 w-full">
                            <Button icon="pi pi-search" label="Find" severity="contrast" type="button" @click.prevent="handleSearch" />
                        </div>
                    </div>

                    <div v-if="localPickups.length > 0" class="mx-5 mb-5">
                        <div class="flex my-5 space-x-4 items-center">
                            <Button v-if="$page.props.user.permissions.includes('pickups.update pickup order')" :disabled="!hasOrderChanged" icon="pi pi-sort-alt"
                                    icon-pos="right"
                                    label="Save Order" size="small" @click.prevent="handleSave" />
                            <p class="text-error">Total Records {{ localPickups.length }}</p>
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
                                        v-if="usePage().props.user.permissions.includes('pickups.show')"
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
                                        Zone
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
                                    v-for="(pickup, index) in localPickups"
                                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500"
                                >
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <Button :disabled="index === 0" icon="pi pi-arrow-up" rounded variant="text" @click.prevent="handleMoveUp(index)" />
                                        <Button :disabled="index === pickups.length - 1" icon="pi pi-arrow-down" rounded variant="text" @click.prevent="handleMoveDown(index)" />
                                    </td>
                                    <td v-if="usePage().props.user.permissions.includes('pickups.show')" class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <Button icon="pi pi-eye" rounded severity="warn" variant="text" @click.prevent="confirmViewPickup(pickup.id)" />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{ pickup.reference }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-3 py-3 font-medium text-slate-700 dark:text-navy-100 lg:px-5"
                                    >
                                        {{ pickup.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{ pickup.zone_id ? pickup.zone?.name : "-" }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{
                                            pickup.address.length > 20 ? pickup.address.substring(0, 20) + "..." : pickup.address
                                        }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{ moment(pickup.created_at).format("YYYY-MM-DD") }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{
                                            pickup.notes.length > 30 ? pickup.notes.substring(0, 30) + "..." : pickup.notes
                                        }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="flex justify-center px-5 my-10">
                        <img :src="SearchSVG" alt="search" class="w-80"/>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLDetailModal
        :pickup-id="pickupId"
        :show="showConfirmViewPickupModal"
        @close="closeModal"
        @update:show="showConfirmViewPickupModal = $event"
    />
</template>
