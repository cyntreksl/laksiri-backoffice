<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, onMounted, ref, watch} from "vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import SimpleOverviewWidget from "@/Components/Widgets/SimpleOverviewWidget.vue";
import Card from "primevue/card";
import FloatLabel from "primevue/floatlabel";
import DataTable from "primevue/datatable";
import DatePicker from "primevue/datepicker";
import ContextMenu from "primevue/contextmenu";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import {useConfirm} from "primevue/useconfirm";
import moment from "moment";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    drivers: {
        type: Object,
        default: () => {
        },
    },
    officers: {
        type: Object,
        default: () => {
        },
    },
    paymentStatus: {
        type: Object,
        default: () => {
        },
    },
    warehouseZones: {
        type: Object,
        default: () => {
        },
    },
    warehouses: {
        type: Object,
        default: () => {
        },
    },
});

const cm = ref();
const confirm = useConfirm();
const baseUrl = ref("air-lines/list");
const loading = ref(true);
const airLines = ref([]);
const totalRecords = ref(0);
const perPage = ref(100);
const currentPage = ref(1);
const selectedAirLine = ref(null);
const selectedAirLineId = ref(null);

const isDialogVisible = ref(false);
const showEditAirLineDialog = ref(false);
const showDeleteAirLineDialog = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    user: {value: null, matchMode: FilterMatchMode.EQUALS},
    payments: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const form = useForm({
    name: "",
})

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => confirmViewEditAirLine(selectedAirLine),
        disabled: !usePage().props.user.permissions.includes("air-line.edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmDeleteAirLine(selectedAirLine),
        disabled: !usePage().props.user.permissions.includes("air-line.delete"),
    },
]);

const confirmViewEditAirLine = (airLine) => {
    form.name = airLine.value.name;
    selectedAirLineId.value = airLine.value.id;
    showEditAirLineDialog.value = true;
    isDialogVisible.value = true;
};

const confirmAirLineDelete = (airLine) => {
    selectedAirLineId.value = airLine.value.id;
    showDeleteAirLineDialog.value = true;
};

const fetchAirLines = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
    loading.value = true;
    try {
        const response = await axios.get(baseUrl.value, {
            params: {
                page,
                per_page: perPage.value,
                search,
                sort_field: sortField,
                sort_order: sortOrder === 1 ? "desc" : "asc",
            }
        });
        airLines.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching HBLs:", error);
    } finally {
        loading.value = false;
    }
};

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchAirLines(currentPage.value);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const onSort = (event) => {
    fetchAirLines(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchAirLines();
});

const debouncedFetchAirLines = debounce((searchValue) => {
    fetchAirLines(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchAirLines(newValue);
    }
});

const exportURL = computed(() => {
    const params = new URLSearchParams({
        warehouse: filters.value.warehouse.value,
        deliveryType: filters.value.hbl_type.value,
        cargoMode: filters.value.cargo_type.value,
        isHold: filters.value.is_hold.value,
        officers: filters.value.user.value,
        paymentStatus: filters.value.payments.value,
        fromDate: moment(fromDate.value).format("YYYY-MM-DD"),
        toDate: moment(toDate.value).format("YYYY-MM-DD"),
    }).toString();

    return `/warehouses/export?${params}`;
});


const showAddNewAirLineDialog = ref(false);

const confirmViewAddNewAirLine = () => {
    showAddNewAirLineDialog.value = true;
    isDialogVisible.value = true;
};

const closeAddNewAirLineModal = () => {
    form.name = "";
    showAddNewAirLineDialog.value = false;
    showEditAirLineDialog.value = false;
    isDialogVisible.value = false;
}

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
};

const handleAddNewAirLine = async () => {
    form.post(route("setting.air-lines.store"), {
        onSuccess: () => {
            closeAddNewAirLineModal();
            form.reset();
            fetchAirLines();
            push.success('Air Line Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const handleEditAirLine = async () => {
    form.put(route("setting.air-lines.update", selectedAirLineId.value), {
        onSuccess: () => {
            closeAddNewAirLineModal();
            form.reset();
            fetchAirLines();
            push.success('Air Line Updated Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const confirmDeleteAirLine = (airLine) => {
    selectedAirLineId.value = airLine.value.id;
    confirm.require({
        message: 'Are you sure you want to delete air line?',
        header: 'Delete Air Line?',
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
            router.delete(route("setting.air-lines.destroy", selectedAirLineId.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Air Line Deleted Successfully!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedAirLineId.value = null;
        },
        reject: () => {
            selectedAirLineId.value = null;
        }
    });
};
</script>
<template>
    <AppLayout title="Air Lines">
        <template #header>Air Lines</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel"  @hide="selectedAirLine = null"/>
                    <DataTable
                        v-model:contextMenuSelection="selectedAirLine"
                        v-model:selection="selectedAirLine"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="airLines"
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
                                    Air Lines
                                </div>
                                <div>
                                    <PrimaryButton
                                        v-if="usePage().props.user.permissions.includes('air-line.create')"
                                        class="w-full"
                                        @click="confirmViewAddNewAirLine()"
                                    >
                                       Create Air Line
                                    </PrimaryButton>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
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

                        <template #empty> No Air Line found. </template>

                        <template #loading> Loading Air Lines data. Please wait.</template>

                        <Column field="id" header="ID" sortable></Column>

                        <Column field="name" header="Name" sortable></Column>

                        <template #footer> In total there are {{ airLines ? totalRecords : 0 }} Air Lines.</template>
                    </DataTable>
                </template>
            </Card>
            <!-- Add New Air Line Dialog -->
            <Dialog
                v-model:visible="isDialogVisible"
                modal
                :header="showAddNewAirLineDialog ? 'Add New Air Line' : 'Edit Air Line'"
                :style="{ width: '90%', maxWidth: '450px' }"
                :block-scroll="true"
                @hide="onDialogHide"
                @show="onDialogShow"
            >
                <div class="mt-4">
                    <InputText
                        v-model="form.name"
                        class="w-full p-inputtext"
                        placeholder="Enter Air Line"
                        required
                        type="text"
                    />
                    <InputError :message="form.errors.name"/>
                </div>

                <template #footer>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Button label="Cancel" class="p-button-text" @click="closeAddNewAirLineModal" />
                        <Button
                            :label="showAddNewAirLineDialog ? 'Add Air Line' : 'Update Air Line'"
                            class="p-button-primary"
                            :icon="showAddNewAirLineDialog ? 'pi pi-plus' : 'pi pi-check'"
                            @click.prevent="showAddNewAirLineDialog ? handleAddNewAirLine() : handleEditAirLine()"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
    </AppLayout>

</template>
