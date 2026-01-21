<script setup>
import {computed, onMounted, ref, watch} from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Select from "primevue/select";
import InputIcon from "primevue/inputicon";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import Panel from "primevue/panel";
import Button from "primevue/button";
import ContextMenu from "primevue/contextmenu";
import Tag from "primevue/tag";
import IconField from "primevue/iconfield";
import DataTable from "primevue/datatable";
import Card from "primevue/card";
import Column from "primevue/column";
import DatePicker from "primevue/datepicker";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import CallFlagModal from "@/Pages/HBL/Partials/CallFlagModal.vue";
import IssueTokenDialog from "@/Pages/CallCenter/HBL/Components/IssueTokenDialog.vue";
import DetainDialog from "@/Pages/Common/Dialog/DetainDialog.vue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {},
    },
    paymentStatus: {
        type: Object,
        default: () => {},
    },
    warehouses: {
        type: Object,
        default: () => {
        },
    },
    shipments: {
        type: Array,
        default: () => [],
    },
});

const baseUrl = '/call-center/reception/appointments-data';
const loading = ref(true);
const hbls = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const showConfirmViewHBLModal = ref(false);
const cm = ref();
const selectedHBL = ref(null);
const selectedHBLData = ref(null);
const selectedHBLID = ref(null);
const selectedHblSummary = ref({});
const confirm = useConfirm();
const dt = ref();
const fromDate = ref(moment(new Date()).toISOString().split("T")[0]);
const toDate = ref(moment(new Date()).toISOString().split("T")[0]);
const warehouses = ref(['COLOMBO', 'NINTAVUR',]);
const hblTypes = ref(['UPB', 'Door to Door', 'Gift']);
const cargoTypes = ref(['Sea Cargo', 'Air Cargo']);
const showConfirmViewCallFlagModal = ref(false);
const showIssueTokenDialog = ref(false);
const hblName = ref("");
const showDetainDialog = ref(false);
const detainDialogMode = ref('detain');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    user: {value: null, matchMode: FilterMatchMode.EQUALS},
    payments: {value: null, matchMode: FilterMatchMode.EQUALS},
    shipment: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const menuModel = ref([
    {
        label: "View",
        icon: "pi pi-fw pi-search",
        command: () => confirmViewHBL(selectedHBL),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.show"),
    },
    {
        label: "Issue Token",
        icon: "pi pi-fw pi-tag",
        command: () => confirmIssueToken(selectedHBL),
        visible: () =>  selectedHBL.value?.hbl,
    },
    {
        label: "Call Flag",
        icon: "pi pi-fw pi-flag",
        command: () => confirmViewCallFlagModal(selectedHBL),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.call flag"),
    },
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => router.visit(route("hbls.edit", selectedHBL.value.hbl.id)),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.edit"),
    },
    {
        label: computed(() => (selectedHBL.value?.hbl?.is_hold ? 'Release' : 'Hold')),
        icon: computed(() => (selectedHBL.value?.hbl?.is_hold ? 'pi pi-fw pi-play-circle' : 'pi pi-fw pi-pause-circle')) ,
        command: () => confirmHBLHold(selectedHBL),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.hold and release"),
    },
    {
        label: "Download",
        icon: "pi pi-fw pi-download",
        url: () => route("hbls.download", selectedHBL.value.hbl.id),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.download pdf"),
    },
    {
        label: "Invoice",
        icon: "pi pi-fw pi-receipt",
        url: () => route("hbls.download.invoice", selectedHBL.value.hbl.id),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.download invoice"),
    },
    {
        label: "Stream Invoice",
        icon: "pi pi-fw pi-file-pdf",
        url: () => route("hbls.streamCashierReceipt", selectedHBL.value.hbl.id),
        target: "_blank",
        visible: () => selectedHBL.value?.hbl,
    },
    {
        label: "Download Invoice",
        icon: "pi pi-fw pi-download",
        url: () => route("hbls.getCashierReceipt", selectedHBL.value.hbl.id),
        visible: () => selectedHBL.value?.hbl,
    },
    {
        label: "Print Token",
        icon: "pi pi-fw pi-print",
        url: () => selectedHBL.value?.hbl?.tokens?.id ? route("call-center.hbls.print-token", {token: selectedHBL.value.hbl.tokens.id}) : '#',
        target: "_blank",
        visible: () => selectedHBL.value?.hbl?.tokens && !selectedHBL.value.hbl.tokens.is_cancelled && selectedHBL.value.hbl.tokens.id,
    },
    {
        label: "Download Token",
        icon: "pi pi-fw pi-download",
        url: () => selectedHBL.value?.hbl?.tokens?.id ? route("call-center.hbls.download-token", {token: selectedHBL.value.hbl.tokens.id}) : '#',
        visible: () => selectedHBL.value?.hbl?.tokens && !selectedHBL.value.hbl.tokens.is_cancelled && selectedHBL.value.hbl.tokens.id,
    },
    {
        label: "Download Baggage PDF",
        icon: "pi pi-fw pi-shopping-bag",
        url: () => route("hbls.download.baggage", selectedHBL.value.hbl.id),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.download pdf"),
    },
    {
        label: "Barcode",
        icon: "pi pi-fw pi-barcode",
        url: () => route("hbls.download.barcode", selectedHBL.value.hbl.id),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.download barcode"),
    },
    {
        label: "Set As RTF",
        icon: "pi pi-fw pi-lock",
        command: () => handleRTFHBL(selectedHBL),
        visible: () => selectedHBL.value?.hbl && !selectedHBL.value?.hbl?.is_rtf,
    },
    {
        label: "Lift RTF",
        icon: "pi pi-fw pi-unlock",
        command: () => handleUndoRTFHBL(selectedHBL),
        visible: () => selectedHBL.value?.hbl && selectedHBL.value?.hbl?.is_rtf,
    },
    {
        label: "Detain HBL",
        icon: "pi pi-fw pi-lock",
        command: () => openDetainDialog(selectedHBL),
        visible: computed(() => {
            const isDetained = selectedHBL.value?.hbl?.latest_detain_record?.is_rtf ?? false;
            return selectedHBL.value?.hbl && usePage().props.user.permissions.includes("set_rtf") && !isDetained;
        }),
    },
    {
        label: "Lift Detain",
        icon: "pi pi-fw pi-unlock",
        command: () => openLiftDetainDialog(selectedHBL),
        visible: computed(() => {
            const isDetained = selectedHBL.value?.hbl?.latest_detain_record?.is_rtf ?? false;
            return selectedHBL.value?.hbl && usePage().props.user.permissions.includes("lift_rtf") && isDetained;
        }),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmHBLDelete(selectedHBL),
        visible: () => selectedHBL.value?.hbl && usePage().props.user.permissions.includes("hbls.delete"),
    },
]);


const fetchHBLs = async (page = 1, search = "", sortField = 'created_at', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl, {
            params: {
                page,
                per_page: perPage.value,
                search,
                warehouse: filters.value.warehouse.value || "",
                deliveryType: filters.value.hbl_type.value || "",
                cargoMode: filters.value.cargo_type.value || "",
                isHold: filters.value.is_hold.value || false,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "asc" : "desc",
                createdBy: filters.value.user.value || "",
                paymentStatus: filters.value.payments.value || [],
                shipment: filters.value.shipment.value || "",
                fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
                toDate: moment(toDate.value).format("YYYY-MM-DD"),
            }
        });
        hbls.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;

    } catch (error) {
        console.error("Error fetching HBLs:", error);
    } finally {
        loading.value = false;
    }
};

const debouncedFetchHBLs = debounce((searchValue) => {
    fetchHBLs(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchHBLs(newValue);
    }
});

watch(() => filters.value.warehouse.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.hbl_type.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.cargo_type.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.is_hold.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.user.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.payments.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => filters.value.shipment.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => fromDate.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

watch(() => toDate.value, () => {
    fetchHBLs(1, filters.value.global.value);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchHBLs(currentPage.value, filters.value.global.value);
};

const onSort = (event) => {
    fetchHBLs(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchHBLs();
});

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

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const confirmViewHBL = (hbl) => {
    selectedHBLID.value = hbl.value.hbl.id;
    showConfirmViewHBLModal.value = true;
};

const closeModal = () => {
    showConfirmViewHBLModal.value = false;
    selectedHBLID.value = null;
};

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
        hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
        is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
        user: {value: null, matchMode: FilterMatchMode.EQUALS},
        payments: {value: null, matchMode: FilterMatchMode.EQUALS},
        shipment: {value: null, matchMode: FilterMatchMode.EQUALS},
    };
    fromDate.value = moment(new Date()).toISOString().split("T")[0];
    toDate.value = moment(new Date()).toISOString().split("T")[0];
    fetchHBLs(1);
};

const confirmHBLDelete = (hbl) => {
    selectedHBLID.value = hbl.value.hbl.id;
    confirm.require({
        message: 'Would you like to delete this hbl record?',
        header: 'Delete HBL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route("hbls.destroy", selectedHBLID.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("HBL record Deleted Successfully!");
                    fetchHBLs(currentPage.value);
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedHBLID.value = null;
        },
        reject: () => {
        }
    });
};

const confirmHBLHold = (hbl) => {
    selectedHBLID.value = hbl.value.hbl.id;
    confirm.require({
        message: `Would you like to ${hbl.value.hbl.is_hold ? 'Release' : 'Hold'} this hbl?`,
        header: `${hbl.value.hbl.is_hold ? 'Release' : 'Hold'} HBL?`,
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: `${hbl.value.is_hold ? 'Release' : 'Hold'}`,
            severity: 'warn'
        },
        accept: () => {
            router.put(
                route("hbls.toggle-hold", selectedHBLID.value),
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        push.success(`Operation Successfully!`);
                        fetchHBLs(currentPage.value);
                    },
                    onError: () => {
                        push.error("Something went to wrong!");
                    },
                }
            );
            selectedHBLID.value = null;
        },
        reject: () => {
        }
    });
};

const handleRTFHBL = (hbl) => {
    selectedHBLID.value = hbl.value.hbl.id;
    confirm.require({
        message: 'Would you like to RTF this HBL?',
        header: 'RTF HBL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Sure, RTF',
            severity: 'warn'
        },
        accept: () => {
            router.post(route("hbls.set.rtf", selectedHBLID.value), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    push.success('HBL going to RTF');
                    fetchHBLs(currentPage.value);
                },
                onError: () => {
                    push.error('Something went to wrong!');
                }
            })
            selectedHBLID.value = null;
        },
        reject: () => {
            selectedHBLID.value = null;
        }
    })
}

const handleUndoRTFHBL = (hbl) => {
    selectedHBLID.value = hbl.value.hbl.id;
    confirm.require({
        message: 'Would you like to Undo RTF for this HBL?',
        header: 'Undo RTF HBL?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Sure, Remove RTF',
            severity: 'warn'
        },
        accept: () => {
            router.post(route("hbls.unset.rtf", selectedHBLID.value), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    push.success('Undo RTF for this HBL successfully!');
                    fetchHBLs(currentPage.value);
                },
                onError: () => {
                    push.error('Something went to wrong!');
                }
            })
            selectedHBLID.value = null;
        },
        reject: () => {
            selectedHBLID.value = null;
        }
    })
}

const confirmViewCallFlagModal = (hbl) => {
    selectedHBLID.value = hbl.value.hbl.id;
    hblName.value = hbl.value.hbl.hbl_name;
    showConfirmViewCallFlagModal.value = true;
};

const closeCallFlagModal = () => {
    showConfirmViewCallFlagModal.value = false;
    selectedHBLID.value = null;
    hblName.value = "";
};

const confirmIssueToken = (hbl) => {
    console.log('hbl',hbl.value)
    selectedHBLData.value = hbl.value.hbl;
    showIssueTokenDialog.value = true;
};

const closeIssueTokenDialog = () => {
    showIssueTokenDialog.value = false;
    selectedHBLData.value = null;
};

const onTokenIssued = (result) => {
    // Refresh the HBL list to show updated token information
    fetchHBLs(currentPage.value, filters.value.global.value);

    // Reset selected HBL
    selectedHBL.value = null;
    selectedHblSummary.value = {};
};

const openDetainDialog = (hbl) => {
    console.log('openDetainDialog called with:', hbl);
    // Store the HBL data before the context menu closes
    if (hbl && hbl.value && hbl.value.hbl) {
        selectedHBLID.value = hbl.value.hbl.id;
        console.log('Stored HBL ID:', selectedHBLID.value);
    }
    detainDialogMode.value = 'detain';
    showDetainDialog.value = true;
};

const openLiftDetainDialog = (hbl) => {
    console.log('openLiftDetainDialog called with:', hbl);
    // Store the HBL data before the context menu closes
    if (hbl && hbl.value && hbl.value.hbl) {
        selectedHBLID.value = hbl.value.hbl.id;
        console.log('Stored HBL ID:', selectedHBLID.value);
    }
    detainDialogMode.value = 'lift';
    showDetainDialog.value = true;
};

const confirmDetainAction = (data) => {
    console.log('confirmDetainAction called with data:', data);
    console.log('selectedHBL:', selectedHBL.value);
    console.log('selectedHBLID:', selectedHBLID.value);

    // Use selectedHBLID instead of selectedHBL.value.id
    if (!selectedHBLID.value) {
        console.error('No HBL ID stored');
        return;
    }

    const hblId = selectedHBLID.value;
    console.log('HBL ID:', hblId);
    console.log('Mode:', detainDialogMode.value);

    if (detainDialogMode.value === 'detain') {
        console.log('Posting detain request to:', route("hbls.set.detain", hblId));
        router.post(
            route("hbls.set.detain", hblId),
            data,
            {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Detain success');
                    push.success(`HBL detained by ${data.detain_type}`);
                    showDetainDialog.value = false;
                    selectedHBLID.value = null;
                    fetchHBLs(currentPage.value, filters.value.global.value);
                },
                onError: (errors) => {
                    console.error('Detain error:', errors);
                    push.error(errors?.message || 'Something went wrong!');
                }
            }
        );
    } else {
        console.log('Posting lift detain request to:', route("hbls.unset.detain", hblId));
        router.post(
            route("hbls.unset.detain", hblId),
            data,
            {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Lift detain success');
                    push.success('HBL detain lifted successfully!');
                    showDetainDialog.value = false;
                    selectedHBLID.value = null;
                    fetchHBLs(currentPage.value, filters.value.global.value);
                },
                onError: (errors) => {
                    console.error('Lift detain error:', errors);
                    push.error(errors?.message || 'Something went wrong!');
                }
            }
        );
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};
</script>

<template>
    <AppLayout title="Appointments">
        <template #header>Appointments</template>

        <Breadcrumb />

        <div>
            <Panel :collapsed="true" class="mt-5" header="Advance Filters" toggleable>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="fromDate" class="w-full" date-format="yy-mm-dd" input-id="from-date"/>
                        <label for="from-date">From Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <DatePicker v-model="toDate" class="w-full" date-format="yy-mm-dd" input-id="to-date"/>
                        <label for="to-date">To Date</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.payments.value" :options="paymentStatus" :showClear="true" class="w-full" input-id="payment-status" />
                        <label for="payment-status">Payment Status</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.user.value" :options="users" :showClear="true" class="w-full" input-id="user" option-label="name" option-value="id" />
                        <label for="user">Created By</label>
                    </FloatLabel>

                    <FloatLabel class="w-full" variant="in">
                        <Select v-model="filters.shipment.value" :options="props.shipments" :showClear="true" class="w-full" input-id="shipment" option-label="name" option-value="value" />
                        <label for="shipment">Shipments</label>
                    </FloatLabel>
                </div>
            </Panel>

            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel" @hide="selectedHBL = null" />
                    <DataTable
                        ref="dt"
                        v-model:contextMenuSelection="selectedHBL"
                        v-model:filters="filters"
                        :globalFilterFields="['hbl.reference', 'hbl.hbl', 'hbl.hbl_name', 'hbl.email', 'hbl.address', 'hbl.contact_number', 'hbl.consignee_name', 'hbl.consignee_address', 'hbl.consignee_contact', 'hbl.cargo_type', 'hbl.hbl_type', 'hbl.warehouse', 'hbl.status', 'hbl.hbl_number', 'appointment_notes', 'notes']"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="hbls"
                        context-menu
                        dataKey="id"
                        filter-display="menu"
                        lazy
                        paginator
                        removable-sort
                        row-hover
                        tableStyle="min-width: 50rem"
                        @page="onPageChange"
                        @rowContextmenu="onRowContextMenu"
                        @sort="onSort">

                        <template #header>
                            <div class="flex flex-col sm:flex-row justify-between items-center mb-2">
                                <div class="text-lg font-medium">
                                    Appointments
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

                                    <Button
                                        icon="pi pi-external-link"
                                        label="Export"
                                        severity="contrast"
                                        size="small"
                                        @click="exportCSV($event)"
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

                        <template #empty> No appointments found. </template>

                        <template #loading> Loading appointment data. Please wait.</template>

                        <Column field="appointment_date" header="Appointment Date" sortable>
                            <template #body="slotProps">
                                <div class="flex flex-col gap-1">
                                    <div class="font-medium">{{ moment(slotProps.data.appointment_date).format('DD MMM YYYY') }}</div>
                                    <Tag v-if="moment(slotProps.data.appointment_date).format('YYYY-MM-DD') === moment().format('YYYY-MM-DD')"
                                         severity="success"
                                         size="small"
                                         value="Today"></Tag>
                                    <Tag v-else-if="moment(slotProps.data.appointment_date).isBefore(moment())"
                                         severity="danger"
                                         size="small"
                                         value="Past"></Tag>
                                    <Tag v-else
                                         severity="info"
                                         size="small"
                                         value="Upcoming"></Tag>
                                </div>
                            </template>
                        </Column>

                        <Column field="hbl.hbl_number" header="HBL" sortable>
                            <template #body="slotProps">
                                <div v-if="slotProps.data.hbl" class="flex items-center space-x-2">
                                    <i v-if="slotProps.data.hbl.latest_detain_record?.is_rtf"
                                       v-tooltip.left="`Detained by ${slotProps.data.hbl.latest_detain_record?.detain_type || 'RTF'}`"
                                       class="ti ti-lock-square-rounded-filled text-2xl text-red-500"></i>
                                    <i v-else-if="slotProps.data.hbl.is_rtf"
                                       v-tooltip.left="`RTF`"
                                       class="ti ti-lock-square-rounded-filled text-2xl text-red-500"></i>
                                    <div>
                                        <div class="font-medium">{{ slotProps.data.hbl.hbl_number ?? slotProps.data.hbl.hbl }}</div>
                                        <div class="text-sm text-gray-500">{{ slotProps.data.hbl.reference }}</div>
                                        <br v-if="slotProps.data.hbl.is_short_loaded">
                                        <Tag v-if="slotProps.data.hbl.is_short_loaded" :severity="`warn`" :value="`Short Loaded`" icon="pi pi-exclamation-triangle" size="small"></Tag>
                                    </div>
                                </div>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column field="hbl.cargo_type" header="Cargo Type" sortable>
                            <template #body="slotProps">
                                <Tag v-if="slotProps.data.hbl && resolveCargoType(slotProps.data.hbl)"
                                    :icon="resolveCargoType(slotProps.data.hbl).icon"
                                    :severity="resolveCargoType(slotProps.data.hbl).color"
                                    :value="slotProps.data.hbl.cargo_type"
                                    class="text-sm">
                                </Tag>
                                <span v-else-if="slotProps.data.hbl">{{ slotProps.data.hbl.cargo_type }}</span>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column field="hbl.hbl_type" header="HBL Type" sortable>
                            <template #body="slotProps">
                                <Tag v-if="slotProps.data.hbl" :severity="resolveHBLType(slotProps.data.hbl)" :value="slotProps.data.hbl.hbl_type"></Tag>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column field="hbl.hbl_name" header="HBL Name">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.hbl">
                                    <a :href="`/hbls/get-hbls-by-user/${slotProps.data.hbl.hbl_name}`"
                                       class="inline-flex items-center hover:underline" target="_blank">
                                        <i class="pi pi-external-link mr-1" style="font-size: 0.75rem"></i>
                                        <span>{{ slotProps.data.hbl.hbl_name }}</span>
                                    </a>
                                    <div class="text-gray-500 text-sm">{{slotProps.data.hbl.email}}</div>
                                    <a :href="`/hbls/get-hbls-by-user/${slotProps.data.hbl.contact_number}`"
                                       class="inline-flex items-center text-gray-500 hover:underline text-sm" target="_blank">
                                        <i class="pi pi-external-link mr-1" style="font-size: 0.75rem"></i>
                                        <span>{{ slotProps.data.hbl.contact_number }}</span>
                                    </a>
                                </div>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column field="hbl.consignee_name" header="Consignee">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.hbl">
                                    <div>{{ slotProps.data.hbl.consignee_name }}</div>
                                    <div class="text-gray-500 text-sm">{{slotProps.data.hbl.consignee_email}}</div>
                                    <div class="text-gray-500 text-sm">{{slotProps.data.hbl.consignee_contact}}</div>
                                </div>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column field="hbl.consignee_address" header="Consignee Address">
                            <template #body="slotProps">
                                <span v-if="slotProps.data.hbl">{{ slotProps.data.hbl.consignee_address || '-' }}</span>
                                <span v-else class="text-gray-400">N/A</span>
                            </template>
                        </Column>

                        <Column field="appointment_notes" header="Appointment Notes">
                            <template #body="slotProps">
                                <div :title="slotProps.data.appointment_notes" class="max-w-xs truncate">
                                    {{ slotProps.data.appointment_notes || '-' }}
                                </div>
                            </template>
                        </Column>

                        <Column field="hbl.tokens.queue_type" header="Queue Type">
                            <template #body="slotProps">
                                <Tag v-if="slotProps.data.hbl?.tokens && slotProps.data.hbl.tokens.is_cancelled"
                                     class="text-sm whitespace-nowrap"
                                     severity="danger"
                                     value="Cancelled" />
                                <Tag v-else-if="slotProps.data.hbl?.tokens && slotProps.data.hbl.tokens.queue_type"
                                     :value="slotProps.data.hbl.tokens.queue_type"
                                     class="text-sm whitespace-nowrap"
                                     severity="info" />
                                <span v-else>-</span>
                            </template>
                        </Column>

                        <Column field="hbl.tokens.token" header="Token Number">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.hbl?.tokens && slotProps.data.hbl.tokens.token" class="flex flex-col items-center gap-1">
                                    <span
                                        :class="slotProps.data.hbl.tokens.is_cancelled
                                            ? 'inline-flex items-center justify-center w-8 h-8 text-sm font-semibold text-white bg-red-500 rounded-full line-through'
                                            : 'inline-flex items-center justify-center w-8 h-8 text-sm font-semibold text-white bg-blue-500 rounded-full'">
                                        {{ slotProps.data.hbl.tokens.token }}
                                    </span>
                                    <Tag v-if="slotProps.data.hbl.tokens.is_cancelled"
                                         class="text-xs"
                                         severity="danger"
                                         size="small"
                                         value="Cancelled" />
                                </div>
                                <span v-else>-</span>
                            </template>
                        </Column>

                        <Column field="hbl.finance_status" header="Finance Status">
                            <template #body="slotProps">
                                <Tag v-if="slotProps.data.hbl?.finance_status" :value="slotProps.data.hbl.finance_status" class="text-sm" severity="success"></Tag>
                                <span v-else>-</span>
                            </template>
                        </Column>



                        <template #footer> In total there are {{ hbls ? totalRecords : 0 }} appointments. </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>

    <HBLDetailModal
        :hbl-id="selectedHBLID"
        :show="showConfirmViewHBLModal"
        @close="closeModal"
        @update:show="showConfirmViewHBLModal = $event"
    />

    <CallFlagModal
        :caller-name="hblName"
        :hbl-id="selectedHBLID"
        :visible="showConfirmViewCallFlagModal"
        @close="closeCallFlagModal"
        @update:visible="showConfirmViewCallFlagModal = $event"/>

    <IssueTokenDialog
        :hbl="selectedHBLData"
        :visible="showIssueTokenDialog"
        @update:visible="closeIssueTokenDialog"
        @token-issued="onTokenIssued"/>

    <DetainDialog
        :entity-name="selectedHBL?.value?.hbl?.hbl_number || selectedHBL?.value?.hbl?.hbl || ''"
        :mode="detainDialogMode"
        :visible="showDetainDialog"
        entity-type="hbl"
        @confirm="confirmDetainAction"
        @update:visible="showDetainDialog = $event"
    />
</template>
