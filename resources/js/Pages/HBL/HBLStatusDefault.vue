<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {router, usePage} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import FloatLabel from 'primevue/floatlabel';
import {useConfirm} from "primevue/useconfirm";
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Card from "primevue/card";
import ContextMenu from 'primevue/contextmenu';
import Panel from 'primevue/panel';
import DatePicker from 'primevue/datepicker';
import Button from "primevue/button";
import Tag from 'primevue/tag';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import axios from "axios";
import {FilterMatchMode} from '@primevue/core/api';
import moment from "moment";
import {debounce} from "lodash";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import {push} from "notivue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {
        },
    },
    paymentStatus: {
        type: Array,
        default: () => [],
    },
    warehouses: {
        type: Object,
        default: () => {
        },
    },
});

const loading = ref(false);
const hblNumber = ref("");
const hblData = ref(null);
const showConfirmViewHBLModal = ref(false);
const cm = ref();
const selectedHBL = ref(null);
const selectedHBLID = ref(null);
const confirm = useConfirm();
const searchError = ref("");
const hblStatusData = ref({});

const menuModel = ref([
    {
        label: "View",
        icon: "pi pi-fw pi-search",
        command: () => confirmViewHBL(selectedHBL),
        disabled: !usePage().props.user.permissions.includes("hbls.show"),
    },
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("hbls.edit", selectedHBL.value.id)),
        disabled: !usePage().props.user.permissions.includes("hbls.edit"),
    },
    {
        label: computed(() => (selectedHBL.value?.is_hold ? 'Release' : 'Hold')),
        icon: computed(() => (selectedHBL.value?.is_hold ? 'pi pi-fw pi-play-circle' : 'pi pi-fw pi-pause-circle')) ,
        command: () => confirmHBLHold(selectedHBL),
        disabled: !usePage().props.user.permissions.includes("hbls.hold and release"),
    },
    {
        label: "Download",
        icon: "pi pi-fw pi-download",
        url: () => route("hbls.download", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes("hbls.download pdf") || selectedHBL.value?.is_third_party,
    },
    {
        label: "Invoice",
        icon: "pi pi-fw pi-receipt",
        url: () => route("hbls.download.invoice", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes("hbls.download invoice") || selectedHBL.value?.is_third_party,
    },
    {
        label: "Barcode",
        icon: "pi pi-fw pi-barcode",
        url: () => route("hbls.download.barcode", selectedHBL.value.id),
        disabled: !usePage().props.user.permissions.includes("hbls.download barcode"),
    },
]);

const searchHBL = async () => {
    if (!hblNumber.value.trim()) {
        searchError.value = "Please enter an HBL number";
        return;
    }

    loading.value = true;
    searchError.value = "";
    hblData.value = null;
    hblStatusData.value = {};

    try {
        const response = await axios.get(`/get-hbl-by-reference/${hblNumber.value.trim()}`);
        
        if (response.data) {
            // Transform the response to match the HBL list format
            hblData.value = [response.data];
            
            // Fetch HBL status for the found HBL
            await fetchHBLStatus(response.data.id);
        } else {
            searchError.value = "HBL not found";
        }
    } catch (error) {
        console.error("Error searching HBL:", error);
        if (error.response && error.response.status === 404) {
            searchError.value = "HBL not found with the provided number";
        } else {
            searchError.value = "An error occurred while searching for the HBL";
        }
    } finally {
        loading.value = false;
    }
};

const fetchHBLStatus = async (hblId) => {
    try {
        const response = await axios.get(`/get-hbl-status/${hblId}`, {
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (response.data && response.data.status) {
            hblStatusData.value[hblId] = response.data.status;
        }
    } catch (error) {
        console.error("Error fetching HBL status:", error);
    }
};

const getLatestHBLStatus = (hblId) => {
    const statuses = hblStatusData.value[hblId];
    if (statuses && statuses.length > 0) {
        return statuses[statuses.length - 1];
    }
    return null;
};

const hblStatusColor = (status) => {
    switch (status) {
        case 'HBL Preparation by warehouse':
        case 'HBL Preparation by driver':
            return 'bg-primary';
        case 'Cash Received by Accountant':
            return 'bg-secondary';
        case 'Container Loading':
            return 'bg-success';
        case 'Container Shipped':
            return 'bg-error';
        case 'Container Arrival':
            return 'bg-slate-500';
        case 'Blocked By RTF':
            return 'bg-red-500';
        case 'Revert To Cash Settlement':
            return 'bg-amber-400';
        case 'Container Unloaded in Colombo':
            return 'bg-gray-400';
        case 'Container Loading in Colombo':
            return 'bg-success';
        case 'Container Unloaded in Nintavur':
            return 'bg-red-600';
        case 'Container In Transit':
            return 'bg-cyan-600';
        case 'Container Reached Destination':
            return 'bg-emerald-600';
        default:
            return 'bg-gray-400';
    }
};

const clearSearch = () => {
    hblNumber.value = "";
    hblData.value = null;
    searchError.value = "";
    hblStatusData.value = {};
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmViewHBL = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    showConfirmViewHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
    selectedHBLID.value = null;
};

const confirmHBLHold = (hbl) => {
    selectedHBLID.value = hbl.value.id;
    confirm.require({
        message: `Would you like to ${hbl.value.is_hold ? 'Release' : 'Hold'} this hbl?`,
        header: `${hbl.value.is_hold ? 'Release' : 'Hold'} HBL?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: hbl.value.is_hold ? 'Release' : 'Hold',
            severity: 'warn'
        },
        accept: () => {
            toggleHBL();
        },
        reject: () => {
            selectedHBLID.value = null;
        }
    });
};

const toggleHBL = async () => {
    try {
        await axios.put(route('hbls.toggle-hold', selectedHBLID.value));
        push.success('HBL status updated successfully!');
        // Refresh the search results
        if (hblData.value) {
            searchHBL();
        }
    } catch (error) {
        console.error('Error updating HBL:', error);
        push.error('Failed to update HBL status');
    } finally {
        selectedHBLID.value = null;
    }
};

const resolveHBLType = (hbl) => {
    switch (hbl.hbl_type) {
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

const resolveCargoType = (hbl) => {
    switch (hbl.cargo_type) {
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

const resolveWarehouse = (hbl) => {
    switch (hbl.warehouse.toUpperCase()) {
        case 'COLOMBO':
            return 'info';
        case 'NINTAVUR':
            return 'danger';
        default:
            return null;
    }
};

const resolvePaymentStatus = (hbl) => {
    switch (hbl.status) {
        case 'Partial Paid':
            return {
                icon: "pi pi-chart-pie",
                color: "warn",
            };
        case 'Not Paid':
            return {
                icon: "pi pi-times",
                color: "danger",
            };
        case 'Full Paid':
            return {
                icon: "pi pi-check",
                color: "success",
            };
        default:
            return {
                icon: "pi pi-exclamation-triangle",
                color: "secondary",
            };
    }
};

// Handle Enter key press for search
const handleKeyPress = (event) => {
    if (event.key === 'Enter') {
        searchHBL();
    }
};
</script>

<template>
    <AppLayout title="HBL Status Default">
        <template #header>HBL Status Default</template>

        <Breadcrumb />

        <div class="mt-5">
            <!-- Search Section -->
            <Card class="mb-5">
                <template #title>
                    <div class="flex items-center gap-3">
                        <i class="ti ti-search text-2xl text-primary"></i>
                        <span>Search HBL by Number</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-8">
                            <FloatLabel>
                                <InputText 
                                    id="hbl-number"
                                    v-model="hblNumber" 
                                    class="w-full"
                                    :class="{ 'p-invalid': searchError }"
                                    @keypress="handleKeyPress"
                                />
                            </FloatLabel>
                            <small v-if="searchError" class="text-red-500 mt-1 block">{{ searchError }}</small>
                        </div>
                        <div class="md:col-span-2">
                            <Button 
                                :loading="loading"
                                @click="searchHBL"
                                class="w-full"
                                icon="pi pi-search"
                                label="Search"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <Button 
                                @click="clearSearch"
                                severity="secondary"
                                outlined
                                class="w-full"
                                icon="pi pi-times"
                                label="Clear"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Results Section -->
            <Card v-if="hblData || loading">
                <template #title>
                    <div class="flex items-center gap-3">
                        <i class="ti ti-list text-2xl text-primary"></i>
                        <span>HBL Details</span>
                    </div>
                </template>
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedHBL = null" />
                    <DataTable
                        v-if="hblData"
                        v-model:contextMenuSelection="selectedHBL"
                        :loading="loading"
                        :value="hblData"
                        context-menu
                        dataKey="id"
                        row-hover
                        tableStyle="min-width: 50rem"
                        @row-contextmenu="onRowContextMenu"
                    >
                        <template #empty>
                            <div class="text-center py-8">
                                <i class="ti ti-search-off text-4xl text-gray-400 mb-3 block"></i>
                                <p class="text-gray-500">No HBL found with the provided number</p>
                            </div>
                        </template>

                        <template #loading>
                            <div class="text-center py-8">
                                <i class="pi pi-spin pi-spinner text-4xl text-primary mb-3 block"></i>
                                <p class="text-gray-500">Searching for HBL...</p>
                            </div>
                        </template>

                        <Column field="hbl_number" header="HBL Number" sortable>
                            <template #body="slotProps">
                                <div class="font-semibold text-primary">
                                    {{ slotProps.data.hbl_number }}
                                </div>
                            </template>
                        </Column>

                        <Column field="reference" header="Reference" sortable>
                            <template #body="slotProps">
                                <div class="font-medium">
                                    {{ slotProps.data.reference }}
                                </div>
                            </template>
                        </Column>

                        <Column field="hbl_name" header="Customer Name" sortable>
                            <template #body="slotProps">
                                <div>
                                    <div class="font-medium">{{ slotProps.data.hbl_name }}</div>
                                    <div class="text-sm text-gray-500">{{ slotProps.data.contact_number }}</div>
                                </div>
                            </template>
                        </Column>

                        <Column field="consignee_name" header="Consignee" sortable>
                            <template #body="slotProps">
                                <div>
                                    <div class="font-medium">{{ slotProps.data.consignee_name }}</div>
                                    <div class="text-sm text-gray-500">{{ slotProps.data.consignee_contact }}</div>
                                </div>
                            </template>
                        </Column>

                        <Column field="is_hold" header="Hold Status" sortable>
                            <template #body="slotProps">
                                <Tag 
                                    :severity="slotProps.data.is_hold ? 'danger' : 'success'" 
                                    :value="slotProps.data.is_hold ? 'On Hold' : 'Active'" 
                                />
                            </template>
                        </Column>

                        <Column field="latest_status" header="Latest Status" sortable>
                            <template #body="slotProps">
                                <div v-if="getLatestHBLStatus(slotProps.data.id)" class="flex items-center">
                                    <div 
                                        :class="`w-3 h-3 rounded-full mr-2 ${hblStatusColor(getLatestHBLStatus(slotProps.data.id).status)}`"
                                    ></div>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-sm">{{ getLatestHBLStatus(slotProps.data.id).status }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ moment(getLatestHBLStatus(slotProps.data.id).created_at).format('MMM DD, YYYY HH:mm') }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="text-gray-400 text-sm">
                                    No status available
                                </div>
                            </template>
                        </Column>
                       
                    </DataTable>
                </template>
            </Card>

            <!-- Empty State -->
            <Card v-if="!hblData && !loading">
                <template #content>
                    <div class="text-center py-12">
                        <i class="ti ti-search text-6xl text-gray-300 mb-4 block"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Search for HBL</h3>
                        <p class="text-gray-500 mb-4">Enter an HBL number above to view detailed information</p>
                        <div class="text-sm text-gray-400">
                            You can search by HBL number or reference number
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- HBL Detail Modal -->
        <HBLDetailModal
            v-if="showConfirmViewHBLModal"
            :hbl-id="selectedHBLID"
            :show="showConfirmViewHBLModal"
            @close="closeModal"
            @update:show="showConfirmViewHBLModal = $event"
        />
    </AppLayout>
</template>

<style scoped>
.p-invalid {
    border-color: #e24c4c;
}
</style>
