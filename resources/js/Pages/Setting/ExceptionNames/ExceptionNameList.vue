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
import CreateExceptionNameForm from "@/Pages/Setting/ExceptionNames/CreateExceptionNameForm.vue";

defineProps({
    exceptionNames: {
        type: Object,
        default: () => {
        },
    },
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);
const showExceptionNameCreateDialog = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const confirmDeleteExceptionName = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete exception name?',
        header: 'Delete Exception Name?',
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
            router.delete(route("setting.exception-names.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Exception Name Deleted Successfully!");
                    router.visit(route("setting.exception-names.index"));
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
    <AppLayout title="Exception Names">
        <template #header>Exception Names</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(exceptionNames).length"
                        :value="exceptionNames"
                        dataKey="id"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Exception Names
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="showExceptionNameCreateDialog = !showExceptionNameCreateDialog"
                                    >
                                        Create New Exception Name
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

                        <template #empty> No Exception Names found.</template>

                        <Column field="name" header="Name" sortable></Column>

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    class="mr-2"
                                    icon="pi pi-pencil"
                                    outlined
                                    rounded
                                    size="small"
                                    @click="confirmViewEditAirLine({ data })"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteExceptionName(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ exceptionNames ? Object.keys(exceptionNames).length : 0 }}
                            Exception Names.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <CreateExceptionNameForm :visible="showExceptionNameCreateDialog"
                             @close="showExceptionNameCreateDialog = false"
                             @update:visible="showExceptionNameCreateDialog = $event" />
</template>
