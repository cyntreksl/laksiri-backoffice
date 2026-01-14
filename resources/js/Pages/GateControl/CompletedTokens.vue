<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import { ref, onMounted } from "vue";
import axios from "axios";

const tokens = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const search = ref('');

const lazyParams = ref({
    page: 1,
    per_page: 10,
    sort_field: 'completed_at',
    sort_order: 'desc',
    search: ''
});

const loadTokens = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/gate-control/completed-tokens/list', {
            params: lazyParams.value
        });
        tokens.value = response.data.data;
        totalRecords.value = response.data.meta.total;
    } catch (error) {
        console.error('Error loading completed tokens:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    lazyParams.value.page = event.page + 1;
    lazyParams.value.per_page = event.rows;
    loadTokens();
};

const onSort = (event) => {
    lazyParams.value.sort_field = event.sortField;
    lazyParams.value.sort_order = event.sortOrder === 1 ? 'asc' : 'desc';
    loadTokens();
};

const onSearch = () => {
    lazyParams.value.search = search.value;
    lazyParams.value.page = 1;
    loadTokens();
};

const viewTokenDetails = (token) => {
    window.location.href = `/gate-control/complete-token?token=${token.token}`;
};

onMounted(() => {
    loadTokens();
});
</script>

<template>
    <AppLayout title="Completed Tokens">
        <template #header>Completed Tokens</template>

        <Breadcrumb />

        <div class="mt-5">
            <Card>
                <template #title>
                    <div class="flex justify-between items-center">
                        <span>Completed Tokens List</span>
                        <div class="flex gap-2">
                            <InputText
                                v-model="search"
                                class="w-80"
                                placeholder="Search by token, reference, or customer..."
                                @keyup.enter="onSearch"
                            />
                            <Button
                                :disabled="loading"
                                icon="pi pi-search"
                                @click="onSearch"
                            />
                        </div>
                    </div>
                </template>
                <template #content>
                    <DataTable
                        :loading="loading"
                        :rows="lazyParams.per_page"
                        :rowsPerPageOptions="[10, 20, 50, 100]"
                        :sortField="lazyParams.sort_field"
                        :sortOrder="lazyParams.sort_order === 'asc' ? 1 : -1"
                        :totalRecords="totalRecords"
                        :value="tokens"
                        class="p-datatable-sm"
                        lazy
                        paginator
                        sortMode="single"
                        @page="onPage"
                        @sort="onSort"
                    >
                        <Column field="token" header="Token" sortable>
                            <template #body="slotProps">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="pi pi-tag mr-2 text-blue-500"></i>
                                    {{ slotProps.data.token }}
                                </div>
                            </template>
                        </Column>

                        <Column field="reference" header="Reference" sortable />

                        <Column field="customer" header="Customer" sortable />

                        <Column field="package_summary" header="Package Summary">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.package_summary" class="flex flex-wrap gap-2">
                                    <Tag
                                        :value="`Total: ${slotProps.data.package_summary.total}`"
                                        severity="secondary"
                                    />
                                    <Tag
                                        v-if="slotProps.data.package_summary.released > 0"
                                        :value="`Released: ${slotProps.data.package_summary.released}`"
                                        severity="success"
                                    />
                                    <Tag
                                        v-if="slotProps.data.package_summary.held > 0"
                                        :value="`Held: ${slotProps.data.package_summary.held}`"
                                        severity="warning"
                                    />
                                    <Tag
                                        v-if="slotProps.data.package_summary.returned_to_bond > 0"
                                        :value="`Returned: ${slotProps.data.package_summary.returned_to_bond}`"
                                        severity="info"
                                    />
                                </div>
                                <span v-else class="text-gray-400">No packages</span>
                            </template>
                        </Column>

                        <Column field="completed_at" header="Completed At" sortable>
                            <template #body="slotProps">
                                {{ slotProps.data.completed_at ? new Date(slotProps.data.completed_at).toLocaleString() : '-' }}
                            </template>
                        </Column>

                        <Column field="departed_at" header="Departed At" sortable>
                            <template #body="slotProps">
                                {{ slotProps.data.departed_at ? new Date(slotProps.data.departed_at).toLocaleString() : '-' }}
                            </template>
                        </Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag
                                    :value="slotProps.data.status"
                                    severity="success"
                                />
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 120px">
                            <template #body="slotProps">
                                <Button
                                    v-tooltip="'View Details'"
                                    icon="pi pi-eye"
                                    rounded
                                    severity="info"
                                    size="small"
                                    text
                                    @click="viewTokenDetails(slotProps.data)"
                                />
                            </template>
                        </Column>

                        <template #empty>
                            <div class="text-center p-4">
                                No completed tokens found.
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
