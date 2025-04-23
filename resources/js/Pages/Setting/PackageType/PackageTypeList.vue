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

defineProps({
    packageTypes: {
        type: Object,
        default: () => {
        },
    },
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);
const showPackageTypeCreateDialog = ref(false);
const showPackageTypeEditDialog = ref(false);
const selectedExceptionName = ref({});

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const handleEditDialog = (data) => {
    selectedExceptionName.value = data;
    showPackageTypeEditDialog.value = true;
}

const confirmDeletePackageType = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete package type?',
        header: 'Delete Package Type?',
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
            router.delete(route("setting.package-types.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Package Type Deleted Successfully!");
                    router.visit(route("setting.package-types.index"));
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
</script>

<template>
    <AppLayout title="Package Types">
        <template #header>Package Types</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(packageTypes).length"
                        :value="packageTypes"
                        dataKey="id"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Package Types
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="showPackageTypeCreateDialog = !showPackageTypeCreateDialog"
                                    >
                                        Create New Package Type
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

                        <template #empty> No Package Types found.</template>

                        <Column field="name" header="Name" sortable></Column>

                        <Column field="description" header="Description"></Column>

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
                                    @click="confirmDeletePackageType(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ packageTypes ? Object.keys(packageTypes).length : 0 }}
                            Package Types.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
