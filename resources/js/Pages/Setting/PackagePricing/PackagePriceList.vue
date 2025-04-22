<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router} from "@inertiajs/vue3";
import {ref} from "vue";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import IconField from "primevue/iconfield";
import Tag from "primevue/tag";
import Select from "primevue/select";

defineProps({
    packageRules: {
        type: Object,
        default: () => {
        },
    },
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_mode: { value: null, matchMode: FilterMatchMode.EQUALS },
    destination_branch_name: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const confirmDeletePackagePriceRule = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete rule?',
        header: 'Delete Package Price Rule?',
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
            router.delete(route("setting.package-prices.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Package Price Rule Deleted Successfully!");
                    router.visit(route("setting.package-prices.index"));
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

const conversionFactors = {
    cm: 1,
    m: 100,
    in: 2.54,
    ft: 30.48,
};

function convertMeasurements(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return (value / factor).toFixed(2);
}
</script>

<template>
    <AppLayout title="Pricing">
        <template #header>Package Pricing</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(packageRules).length"
                        :value="packageRules"
                        dataKey="id"
                        filter-display="menu"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Package Price Rules
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="router.visit(route('setting.package-prices.create'))"
                                    >
                                        Create New Package Price Rule
                                    </Button>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Search Field -->
                                <IconField class="w-full sm:w-auto">
                                    <InputIcon>
                                        <i class="pi pi-search"/>
                                    </InputIcon>
                                    <InputText
                                        v-model="filters.global.value"
                                        class="w-full"
                                        placeholder="Keyword Search"
                                        size="small"
                                    />
                                </IconField>
                            </div>
                        </template>

                        <template #empty> No Package Price Rules found.</template>

                        <Column field="rule_title" header="Title"></Column>

                        <Column field="cargo_mode" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag :icon="resolveCargoType(slotProps.data.cargo_mode).icon" :severity="resolveCargoType(slotProps.data.cargo_mode).color" :value="slotProps.data.cargo_mode" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="cargoTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="length" header="Length">
                            <template #body="{data}">
                                <div class="flex items-center">
                                    <i class="ti ti-ruler-measure mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ convertMeasurements(data.measure_type, data.length )}} {{ data.measure_type }}
                                </div>
                            </template>
                        </Column>

                        <Column field="width" header="Width">
                            <template #body="{data}">
                                <div class="flex items-center">
                                    <i class="ti ti-arrow-autofit-width mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ convertMeasurements(data.measure_type, data.width )}} {{ data.measure_type }}
                                </div>
                            </template>
                        </Column>

                        <Column field="height" header="Height">
                            <template #body="{data}">
                                <div class="flex items-center">
                                    <i class="ti ti-arrow-autofit-height mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ convertMeasurements(data.measure_type, data.height )}} {{ data.measure_type }}
                                </div>
                            </template>
                        </Column>

                        <Column field="hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag :severity="resolveHBLType(slotProps.data.hbl_type)" :value="slotProps.data.hbl_type"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="hblTypes" :showClear="true" placeholder="Select One" style="min-width: 12rem" />
                            </template>
                        </Column>

                        <Column field="per_package_charge" header="Package Price">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    <span>{{slotProps.data.per_package_charge.toFixed(2)}}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="bill_price" header="Bill Price">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    <span>{{slotProps.data.bill_price.toFixed(2)}}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="volume_charges" header="Volume Charges">
                            <template #body="slotProps">
                                <div class="flex items-center justify-end">
                                    <i class="ti ti-cash mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    <span>{{slotProps.data.volume_charges.toFixed(2)}}</span>
                                </div>
                            </template>
                        </Column>

                        <Column field="volume_charges" header="Bill VAT">
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

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    class="mr-2"
                                    icon="pi pi-pencil"
                                    outlined
                                    rounded
                                    size="small"
                                    @click="router.visit(route('setting.package-prices.edit', data.id))"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeletePackagePriceRule(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ packageRules ? Object.keys(packageRules).length : 0 }}
                            Package Price Rules.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
