<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {router} from "@inertiajs/vue3";
import {reactive, ref, watch} from "vue";
import moment from "moment";
import {push} from "notivue";
import SearchSVG from "@/../images/illustrations/search.svg";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import Button from "primevue/button";
import Select from "primevue/select";
import Card from "primevue/card";

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
    <AppLayout title="Delivery Order">
        <template #header>Delivery Order</template>

        <Breadcrumb/>

        <div class="my-5">
            <Card>
                <template #title>Deliver Priority Ordering</template>
                <template #content>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-5 mt-10 mx-5 items-center">
                        <div>
                            <InputLabel value="Driver"/>
                            <Select v-model="form.driverId" :options="drivers" class="w-full" filter input-id="user" option-label="name" option-value="id" placeholder="Select Driver"/>
                        </div>

                        <div class="mt-5 w-full">
                            <Button icon="pi pi-search" label="Find" severity="contrast" size="small" type="button" @click.prevent="handleSearch" />
                        </div>
                    </div>

                    <div v-if="localDeliveries.length > 0" class="mx-5 mb-5">
                        <div class="flex my-5 space-x-4 items-center">
                            <Button :disabled="!hasOrderChanged" icon="pi pi-sort-alt"
                                    icon-pos="right"
                                    label="Save Order" size="small" @click.prevent="handleSave" />
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
                                        <Button :disabled="index === 0" icon="pi pi-arrow-up" rounded variant="text" @click.prevent="handleMoveUp(index)" />
                                        <Button :disabled="index === deliveries.length - 1" icon="pi pi-arrow-down" rounded variant="text" @click.prevent="handleMoveDown(index)" />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        <Button icon="pi pi-eye" rounded severity="warn" variant="text" @click.prevent="confirmViewDelivery(delivery.hbl.id)" />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{ delivery.hbl.hbl_number }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-3 py-3 font-medium text-slate-700 dark:text-navy-100 lg:px-5"
                                    >
                                        {{ delivery.hbl.hbl_name}}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{delivery.hbl.address.length > 20 ? delivery.hbl.address.substring(0, 20) + "..." : delivery.hbl.address }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{
                                            moment(delivery.created_at).format("YYYY-MM-DD")
                                        }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{
                                            delivery.notes
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

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeModal"
            @update:show="showConfirmViewHBLModal = $event"
        />
    </AppLayout>
</template>
