<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router} from "@inertiajs/vue3";
import {ref} from "vue";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import Tag from "primevue/tag";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import ContextMenu from "primevue/contextmenu";

defineProps({
    priceRules: {
        type: Object,
        default: () => {
        },
    },
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);
const cm = ref();
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const modes = ref(['volume', 'weight',]);
const selectedPriceRule = ref(null);

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("setting.prices.edit", selectedPriceRule.value.id)),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmDeletePriceRule(selectedPriceRule.value.id),
    },
]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_mode: { value: null, matchMode: FilterMatchMode.EQUALS },
    destination_branch_name: { value: null, matchMode: FilterMatchMode.EQUALS },
    price_mode: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmDeletePriceRule = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete rule?',
        header: 'Delete Price Rule?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'warn'
        },
        accept: () => {
            router.delete(route("setting.prices.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Price Rule Deleted Successfully!");
                    router.visit(route("setting.prices.index"));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
        },
        reject: () => {
        }
    });
};

const resolveCargoType = (cargo_type) => {
    switch (cargo_type) {
        case 'Sea Cargo':
            return {
                icon: "ti ti-sailboat",
                color: "success",
            };
        case 'Air Cargo':
            return {
                icon: "ti ti-plane-tilt",
                color: "info",
            };
        default:
            return null;
    }
};

const resolveMode = (mode) => {
    switch (mode) {
        case 'volume':
            return {
                icon: "ti ti-scale",
                color: "secondary",
            };
        case 'weight':
            return {
                icon: "ti ti-scale-outline",
                color: "danger",
            };
        default:
            return null;
    }
};

const resolveHBLType = (hbl_type) => {
    switch (hbl_type) {
        case 'UPB':
            return 'secondary';
        case 'Gift':
            return 'warn';
        case 'Door to Door':
            return 'info';
        default:
            return null;
    }
};

const resolveWarehouse = (warehouse) => {
    switch (warehouse.toUpperCase()) {
        case 'COLOMBO':
            return 'info';
        case 'NINTAVUR':
            return 'danger';
        default:
            return null;
    }
};
</script>

<template>
    <AppLayout title="Pricing">
        <template #header>Pricing</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedPriceRule = null" />
                    <DataTable
                        v-model:contextMenuSelection="selectedPriceRule"
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(priceRules).length"
                        :value="priceRules"
                        context-menu
                        dataKey="id"
                        filter-display="menu"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Price Rules
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="router.visit(route('setting.prices.create'))"
                                    >
                                        Create New Price Rule
                                    </Button>
                                </div>
                            </div>
                        </template>

                        <template #empty> No Price Rules found.</template>

                        <Column field="cargo_mode" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data.cargo_mode).icon" :severity="resolveCargoType(slotProps.data.cargo_mode).color" :value="slotProps.data.cargo_mode" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="condition" header="Condition"></Column>

                        <Column field="true_action" header="True Action"></Column>

                        <Column field="false_action" header="False Action"></Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data.hbl_type)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="price_mode" header="Mode" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveMode(slotProps.data.price_mode).icon" :severity="resolveMode(slotProps.data.price_mode).color" :value="slotProps.data.price_mode.toUpperCase()" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="modes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="bill_price" header="Bill Charges">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    <span>{{slotProps.data.bill_price.toFixed(2)}}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="destination_charges" header="Destination Charges">
                            <template #body="{data}">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    <span>{{
                                            data.per_package_charges ? (parseFloat(data.per_package_charges) + parseFloat(data.volume_charges)).toFixed(2) : null
                                        }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="bill_vat" header="Bill VAT">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <span>{{slotProps.data.bill_vat}} %</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="destination_branch_name" header="Destination" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveWarehouse(slotProps.data.destination_branch_name)" :value="slotProps.data.destination_branch_name.toUpperCase()"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="warehouses" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column class="!text-center" field="is_editable" header="Editable">
                            <template #body="{ data }">
                                <i :class="{ 'pi-times text-red-500': !data.is_editable, 'pi-check text-green-400': data.is_editable }" class="pi"></i>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ priceRules ? Object.keys(priceRules).length : 0 }}
                            Price Rules.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
