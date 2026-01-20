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
import Panel from 'primevue/panel';
import FloatLabel from 'primevue/floatlabel';
import Divider from 'primevue/divider';
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import Select from "primevue/select";
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

// Filter options
const statusOptions = ref(['ONGOING', 'COMPLETED', 'CANCELLED', 'DUE']);

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
    status: {value: null, matchMode: FilterMatchMode.EQUALS},
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
                status: filters.value.status.value,
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

// Watch date filters
watch([fromDate, toDate], () => {
    fetchTokens(currentPage.value, filters.value.global.value);
});

// Watch advanced filters
watch([
    () => filters.value.status.value,
], () => {
    fetchTokens(1, filters.value.global.value);
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
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
    fromDate.value = moment(new Date()).subtract(1, "month").toISOString().split("T")[0];
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
            can_be_cancelled: false,
            status: 'CANCELLED',
            status_label: 'Cancelled',
            status_color: 'secondary'
        };
    }

    // 2. Show success notification
    push.success('Token cancelled successfully');

    // 3. Refresh the token list to get updated data from server
    fetchTokens(currentPage.value, filters.value.global.value);
};

// Check if cancel button should be shown
const canCancelToken = (token) => {
    return can('tokens.cancel') && token.can_be_cancelled && token.status !== 'CANCELLED';
};
</script>

<template>
    <AppLayout title="Tokens">
        <template #header>Tokens</template>

        <Breadcrumb/>

        <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <FloatLabel class="w-full" variant="in">
                    <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date"/>
                    <label for="from-date">From Date</label>
                </FloatLabel>

                <FloatLabel class="w-full" variant="in">
                    <DatePicker v-model="toDate" class="w-full" date-format="yy-mm-dd" input-id="to-date"/>
                    <label for="to-date">To Date</label>
                </FloatLabel>

                <FloatLabel class="w-full" variant="in">
                    <Select v-model="filters.status.value" :options="statusOptions" :showClear="true" class="w-full" input-id="status" />
                    <label for="status">Status</label>
                </FloatLabel>
            </div>
        </Panel>

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
                           :rowClass="(data) => {
                               if (data.status === 'CANCELLED') return 'bg-red-50 opacity-75';
                               if (data.status === 'DUE') return 'bg-orange-50';
                               if (data.status === 'COMPLETED') return 'bg-green-50';
                               return '';
                           }"
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
                                <div class="flex gap-2">
                                    <Tag
                                        :severity="slotProps.data.status_color"
                                        :value="slotProps.data.status_label"
                                        class="w-fit"
                                    />
                                </div>
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

                    <Column field="latest_queue_type" header="Latest Status">
                        <template #body="slotProps">
                            <div v-if="slotProps.data.latest_queue_type" class="text-gray-700">
                                <div class="font-semibold">{{ slotProps.data.latest_queue_type }}</div>
                                <div class="text-sm text-gray-500">{{ slotProps.data.created_at }}</div>
                            </div>
                            <div v-else class="text-gray-400 italic">
                                No queue assigned
                            </div>
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
                                v-tooltip.top="data.status === 'CANCELLED' ? 'Cannot view cancelled token' : 'View token'"
                                :disabled="data.status === 'CANCELLED'"
                                @click.prevent="() => data.status !== 'CANCELLED' && router.visit(route('call-center.tokens.show', data.id))"
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
