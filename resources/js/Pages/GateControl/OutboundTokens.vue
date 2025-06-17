<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Button from "primevue/button";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {router} from "@inertiajs/vue3";
import {push} from "notivue";
import {useConfirm} from "primevue/useconfirm";

const baseUrl = ref("/call-center/examination/gate-pass/list");
const loading = ref(true);
const tokens = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const dt = ref();
const confirm = useConfirm();

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchTokens = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
            }
        });

        // Get today's date in YYYY-MM-DD format
        const today = new Date().toISOString().split('T')[0];

        // Filter tokens where `created_at` matches today's date
        tokens.value = response.data.data.filter(token => {
            const tokenDate = new Date(token.created_at).toISOString().split('T')[0];
            return tokenDate === today;
        });

        totalRecords.value = tokens.value.length;
        currentPage.value = 1;
    } catch (error) {
        console.error("Error fetching tokens:", error);
    } finally {
        loading.value = false;
    }
};

const markAsDeparted = async (id) => {
    confirm.require({
        message: `Would you like to mark the token as departed from the warehouse?`,
        header: `Mark as Departed?`,
        icon: 'pi pi-directions-alt',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Mark as Departed',
            severity: 'success'
        },
        accept: () => {
            router.put(
                route("gate-control.outbound-gate-passes.mark-as-departed", id),
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success(`Operation Successfully!`);
                        fetchTokens(currentPage.value);
                    },
                    onError: () => {
                        push.error("Something went to wrong!");
                    },
                }
            );
        },
        reject: () => {
        }
    });
}

const debouncedFetchTokens = debounce((searchValue) => {
    fetchTokens(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchTokens(newValue);
    }
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchTokens(currentPage.value);
};

const onSort = (event) => {
    fetchTokens(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchTokens();
});

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>

<template>
    <AppLayout title="Gate Pass Tokens">
        <template #header>Gate Pass Tokens</template>

        <Breadcrumb />

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        ref="dt"
                        v-model:filters="filters"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="tokens"
                        data-key="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Outbound Gate Pass Tokens
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <Button
                                    icon="pi pi-external-link"
                                    label="Export"
                                    severity="contrast"
                                    size="small"
                                    @click="exportCSV($event)"
                                />
                            </div>
                        </template>

                        <template #empty>No tokens found.</template>

                        <template #loading>Loading tokens data. Please wait.</template>

                        <Column field="token" header="Token">
                            <template #body="slotProps">
                                <div class="flex items-center text-2xl">
                                    <i class="ti ti-tag mr-1 text-blue-500"></i>
                                    {{ slotProps.data.token }}
                                </div>
                            </template>
                        </Column>

                        <Column field="hbl" header="HBL"></Column>

                        <Column field="customer" header="Customer"></Column>

                        <Column field="reception" header="Reception"></Column>

                        <Column field="package_count" header="Packages">
                            <template #body="slotProps">
                                <div class="flex items-center">
                                    <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                    {{ slotProps.data.package_count }}
                                </div>
                            </template>
                        </Column>

                        <Column field="released_at" header="Released At"></Column>

                        <Column field="released_by" header="Released By"></Column>

                        <Column header="">
                            <template #body="slotProps">
                                <Button v-if="slotProps.data.departed_at === null" aria-label="Info" icon="pi pi-eye" rounded severity="info" size="small" type="button" variant="text" @click="markAsDeparted(slotProps.data.id)" />

                                <div v-else class="space-y-1">
                                    <!-- Status indicator with icon -->
                                    <div class="flex items-center gap-1.5 text-green-600">
                                        <i class="ti ti-check text-lg"></i>
                                        <span class="font-medium">Departed From Warehouse</span>
                                    </div>

                                    <!-- Departed timestamp -->
                                    <div class="text-sm text-gray-500">
                                        <span>Departed at: </span>
                                        <span class="font-mono">{{ slotProps.data.departed_at }}</span>
                                    </div>

                                    <!-- Time in warehouse -->
                                    <div class="text-sm text-gray-500 italic">
                                        <span>Time in warehouse: </span>
                                        <span class="font-medium text-gray-600 not-italic">
                                            {{ slotProps.data.total_time }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <template #footer> In total there are {{ tokens ? totalRecords : 0 }} tokens.</template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
