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

defineProps({
    branches: {
        type: Object,
        default: () => {
        },
    },
});

const confirm = useConfirm();
const perPage = ref(10);
const currentPage = ref(1);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
};

const confirmDeleteBranch = (id) => {
    confirm.require({
        message: 'Are you sure you want to delete this branch? This action will permanently remove all associated data.',
        header: 'Delete Branch?',
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
            router.delete(route("branches.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Branch Deleted Successfully!");
                    router.visit(route("branches.index"));
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

const parseJson = (value) => {
    try {
        return JSON.parse(value) || [];
    } catch {
        return [];
    }
}
</script>

<template>
    <AppLayout title="Branches">
        <template #header>Branches</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        v-model:filters="filters"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="Object.keys(branches).length"
                        :value="branches"
                        dataKey="id"
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Branches
                                </div>
                                <div>
                                    <Button
                                        class="w-full"
                                        @click="router.visit(route('branches.create'))"
                                    >
                                        Create New Branch
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

                        <template #empty> No Branches found.</template>

                        <Column field="name" header="Name" sortable>
                            <template #body="slotProps">
                                <div>{{ slotProps.data.name }}</div>
                                <div class="text-blue-400 text-sm">{{ slotProps.data.branch_code }}</div>
                            </template>
                        </Column>

                        <Column field="package_types" header="Package Types">
                            <template #body="slotProps">
                                <div class="flex space-x-1">
                                    <Tag v-for="(ptype, index) in parseJson(slotProps.data.package_types)" :key="index" :value="ptype" severity="secondary"></Tag>
                                </div>
                            </template>
                        </Column>

                        <Column field="type" header="Type"></Column>

                        <Column field="is_prepaid" header="Price Type">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.is_prepaid ? 'PrePaid' : 'PostPaid' }}</div>
                            </template>
                        </Column>

                        <Column field="delivery_types" header="Delivery Types">
                            <template #body="slotProps">
                                <div class="flex space-x-1">
                                    <Tag v-for="(dtype, index) in parseJson(slotProps.data.delivery_types)" :key="index" :value="dtype" severity="info"></Tag>
                                </div>
                            </template>
                        </Column>

                        <Column field="currency_name" header="Currency">
                            <template #body="slotProps">
                                <div>{{ slotProps.data.currency_name }}</div>
                                <div class="text-blue-400 text-sm">{{ slotProps.data.currency_symbol }}</div>
                            </template>
                        </Column>

                        <Column field="cargo_modes" header="Cargo Modes">
                            <template #body="slotProps">
                                <div class="flex space-x-1">
                                    <Tag v-for="(cmode, index) in parseJson(slotProps.data.cargo_modes)" :key="index" :value="cmode"></Tag>
                                </div>
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
                                    @click="router.visit(route('branches.edit', data.id))"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteBranch(data.id)"
                                />
                            </template>
                        </Column>

                        <template #footer> In total there are {{ branches ? Object.keys(branches).length : 0 }}
                            Branches.
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
