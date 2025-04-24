<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import {ref} from "vue";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Button from "primevue/button";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import IconField from "primevue/iconfield";
import Select from "primevue/select";
import Tag from "primevue/tag";
import CreateOfficerDialog from "@/Pages/Setting/ShippersConsignees/CreateOfficerDialog.vue";
import EditOfficerDialog from "@/Pages/Setting/ShippersConsignees/EditOfficerDialog.vue";

defineProps({
    allOfficers: {
        type: Object,
        default: () => {
        },
    },
    countryCodes: {
        type: Array,
        default: () => [],
    }
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);
const showOfficerCreateDialog = ref(false);
const showOfficerEditDialog = ref(false);
const selectedOfficer = ref({});
const types = ref(['shipper', 'consignee']);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    type: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const handleEditDialog = (data) => {
    selectedOfficer.value = data;
    showOfficerEditDialog.value = true;
}

const confirmDeleteOfficer = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete officer?',
        header: 'Delete Officer?',
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
            router.delete(route("setting.shipper-consignees.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Officer Deleted Successfully!");
                    router.visit(route("setting.shipper-consignees.index"));
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

const resolveType = (type) => {
    switch (type) {
        case 'consignee':
            return 'success'
        case 'shipper':
            return 'info'
        default:
            return 'secondary';
    }
};
</script>

<template>
    <AppLayout title="Officers">
        <template #header>Officers</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(allOfficers).length"
                        :value="allOfficers"
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
                                    Shipper & Consignee Officers
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="showOfficerCreateDialog = !showOfficerCreateDialog"
                                    >
                                        Create New Officer
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

                        <template #empty> No Officers found.</template>

                        <Column field="name" header="Name">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.name }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.email }}</div>
                                <div class="text-gray-500 text-sm">{{ slotProps.data.mobile_number }}</div>
                            </template>
                        </Column>

                        <Column field="type" header="Type">
                            <template #body="slotProps">
                                <Tag :severity="resolveType(slotProps.data.type)"
                                     :value="slotProps.data.type.toUpperCase()" class="text-sm"></Tag>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <Select v-model="filterModel.value" :options="types" :showClear="true"
                                        placeholder="Select One" style="min-width: 12rem"/>
                            </template>
                        </Column>

                        <Column field="pp_or_nic_no" header="NIC"></Column>

                        <Column field="residency_no" header="Residency No"></Column>

                        <Column field="address" header="Address"></Column>

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    class="mr-2"
                                    icon="pi pi-pencil"
                                    outlined
                                    rounded
                                    size="small"
                                    @click="handleEditDialog(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteOfficer(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ allOfficers ? Object.keys(allOfficers).length : 0 }}
                            Officers.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <CreateOfficerDialog :country-codes="countryCodes" :visible="showOfficerCreateDialog"
                         @close="showOfficerCreateDialog = false"
                         @update:visible="showOfficerCreateDialog = $event"/>

    <EditOfficerDialog :country-codes="countryCodes" :officer="selectedOfficer" :visible="showOfficerEditDialog"
                       @close="showOfficerEditDialog = false"
                       @update:visible="showOfficerEditDialog = $event"/>
</template>
