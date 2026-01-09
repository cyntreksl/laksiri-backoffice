<script setup>
import {router, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {onMounted, ref, watch, computed} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import DataView from 'primevue/dataview';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import moment from "moment";
import Tag from "primevue/tag";
import CancelTokenDialog from "@/Pages/Token/Partials/CancelTokenDialog.vue";
import { push } from 'notivue';

const baseUrl = ref("/call-center/token-list");
const loading = ref(true);
const tokens = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const fromDate = ref(moment(new Date()).subtract(1, "month").toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);

// Cancel token dialog state
const showCancelDialog = ref(false);
const selectedToken = ref(null);

// Permission checking
const page = usePage();
const can = (perm) => {
    const isSuperAdmin = page.props.auth?.user?.roles?.some(role =>
        (typeof role === 'string' ? role : role?.name)?.toLowerCase() === 'super-admin'
    );
    return isSuperAdmin || page.props.user?.permissions?.includes(perm);
};

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const fetchTokens = async (page = 1, search = "", sortField = 'created_at', sortOrder = -1) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });
        tokens.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching tokens:", error);
    } finally {
        loading.value = false;
    }
};

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
    fetchTokens(currentPage.value, filters.value.global.value);
};

const onSort = (event) => {
    fetchTokens(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchTokens();
});

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
    fromDate.value = moment(new Date()).subtract(24, "months").toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchTokens(currentPage.value);
};

// Open cancel dialog
const openCancelDialog = (token) => {
    selectedToken.value = token;
    showCancelDialog.value = true;
};

// Handle token cancelled event
const handleTokenCancelled = (cancelledToken) => {
    // 1. Update token status in local state
    const tokenIndex = tokens.value.findIndex(t => t.id === cancelledToken.id);
    if (tokenIndex !== -1) {
        tokens.value[tokenIndex] = {
            ...tokens.value[tokenIndex],
            is_cancelled: true,
            cancelled_at: new Date().toISOString(),
            can_be_cancelled: false
        };
    }

    // 2. Show success notification
    push.success('Token cancelled successfully');

    // 3. Refresh the token list to get updated data from server
    fetchTokens(currentPage.value, filters.value.global.value);
};

// Check if cancel button should be shown
const canCancelToken = (token) => {
    return can('tokens.cancel') && token.can_be_cancelled && !token.is_cancelled;
};
</script>

<template>
    <AppLayout title="Tokens">
        <template #header>Tokens</template>

        <Breadcrumb/>

        <Card class="my-5">
            <template #content>
                <DataTable v-model:filters="filters"
                           :loading="loading"
                           :rows="perPage"
                           :rowsPerPageOptions="[5, 10, 20, 50]"
                           :totalRecords="totalRecords"
                           :value="tokens"
                           data-key="id"
                           filter-display="menu"
                           lazy
                           paginator
                           removable-sort
                           row-hover
                           :sortOrder="-1"
                           sortField="created_at"
                           tableStyle="min-width: 50rem"
                           :rowClass="(data) => data.is_cancelled ? 'bg-red-50 opacity-75' : ''"
                           @page="onPageChange"
                           @sort="onSort"
                >
                    <template #header>
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                            <div class="text-lg font-medium">
                                Tokens
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <!-- Button Group -->
                            <div class="flex flex-col sm:flex-row gap-2">
                                <Button
                                    icon="pi pi-filter-slash"
                                    label="Clear Filters"
                                    outlined
                                    severity="contrast"
                                    size="small"
                                    type="button"
                                    @click="clearFilter()"
                                />
                            </div>

                            <!-- Search Field -->
                            <IconField class="w-full sm:w-auto">
                                <InputIcon>
                                    <i class="pi pi-search" />
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

                    <Column field="token" header="Token">
                        <template #body="slotProps">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center text-2xl">
                                    <i class="ti ti-tag mr-1 text-blue-500"></i>
                                    {{ slotProps.data.token }}
                                </div>
                                <Tag v-if="slotProps.data.is_cancelled" class="w-fit" severity="danger" value="CANCELLED" />
                            </div>
                        </template>
                    </Column>
                    <Column field="customer" header="Customer"></Column>
                    <Column field="hbl.reference" header="Reference">
                        <template #body="slotProps">
                            <div>
                                {{ slotProps.data.hbl.reference }}
                                <div class="text-gray-400 italic">
                                    {{slotProps.data.hbl.hbl_number}}
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="package_count" header="Packages">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <i class="ti ti-package mr-1 text-blue-500" style="font-size: 1rem"></i>
                                {{ slotProps.data.package_count }}
                            </div>
                        </template>
                    </Column>

                    <Column :sortField="'created_at'" field="created_at" header="Created At" sortable></Column>

                    <Column field="cancelled_at" header="Cancelled At">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.is_cancelled && slotProps.data.cancelled_at" class="text-red-600">
                                {{ slotProps.data.cancelled_at }}
                            </span>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>

                    <Column field="" style="width: 10%">
                        <template #body="{ data }">
                            <Button
                                class="mr-2"
                                icon="pi pi-eye"
                                rounded
                                severity="info"
                                variant="text"
                                size="small"
                                v-tooltip.top="data.is_cancelled ? 'Cannot view cancelled token' : 'View token'"
                                :disabled="data.is_cancelled"
                                @click.prevent="() => !data.is_cancelled && router.visit(route('call-center.tokens.show', data.id))"
                            />
                            <Button
                                v-if="canCancelToken(data)"
                                v-tooltip.top="'Cancel Token'"
                                class="mr-2"
                                icon="pi pi-times"
                                rounded
                                severity="danger"
                                size="small"
                                variant="text"
                                @click.prevent="() => openCancelDialog(data)"
                            />
                        </template>
                    </Column>
                    <template #footer> In total there are {{ totalRecords }} tokens.</template>
                </DataTable>
            </template>
        </Card>

        <!-- Cancel Token Dialog -->
        <CancelTokenDialog
            v-model:visible="showCancelDialog"
            :token="selectedToken"
            @token-cancelled="handleTokenCancelled"
        />
    </AppLayout>
</template>
