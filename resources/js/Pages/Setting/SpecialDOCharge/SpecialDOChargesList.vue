<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, onMounted, ref, watch} from "vue";
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import ContextMenu from "primevue/contextmenu";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import InputError from "@/Components/InputError.vue";
import ToggleSwitch from 'primevue/toggleswitch';
import InputLabel from "@/Components/InputLabel.vue";
import Select from "primevue/select";

const cm = ref();
const confirm = useConfirm();
const baseUrl = ref("taxes/list");
const loading = ref(true);
const taxes = ref([]);
const totalRecords = ref(0);
const perPage = ref(100);
const currentPage = ref(1);
const selectedDOCharge = ref(null);
const selectedTaxId = ref(null);
const isDialogVisible = ref(false);
const showEditTaxDialog = ref(false);
const showDeleteTaxDialog = ref(false);
const checked = ref(false);

const props = defineProps({
    hblTypes: {
        type: Object,
        default: () => {
        },
    },
    branches: {
        type: Array,
        default: () => [],
    },
    packageTypes: {
        type: Object,
        default: () => {
        },
    },
})
const hblTypeArray = computed(() => Object.values(props.hblTypes));
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
    agent_id: "",
    hbl_type: "",
    collected: "",
    quantity_basis: "",
    package_type: "",
    is_active: false,
})
const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => confirmViewEditTax(selectedDOCharge),
        disabled: !usePage().props.user.permissions.includes("tax.destination tax edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmDeleteTax(selectedDOCharge),
        disabled: !usePage().props.user.permissions.includes("tax.destination tax delete"),
    },
]);
const confirmViewEditTax = (tax) => {
    form.name = tax.value.name;
    form.rate = tax.value.rate;
    form.is_active = tax.value.is_active;
    checked.value = tax.value.is_active;
    selectedTaxId.value = tax.value.id;
    showEditTaxDialog.value = true;
    isDialogVisible.value = true;
};
const fetchTaxes = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
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
        taxes.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Taxes:", error);
    } finally {
        loading.value = false;
    }
};
const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchTaxes(currentPage.value);
};
const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};
const onSort = (event) => {
    fetchTaxes(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};
onMounted(() => {
    fetchTaxes();
});
const debouncedFetchTaxes = debounce((searchValue) => {
    fetchTaxes(1, searchValue);
}, 1000);
watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchTaxes(newValue);
    }
});

const showAddNewDOChargeDialog = ref(false);
const confirmViewAddNewDOCharge = () => {
    showAddNewDOChargeDialog.value = true;
    isDialogVisible.value = true;
};
const closeAddNewTaxModal = () => {
    form.name = "";
    showAddNewTaxDialog.value = false;
    showEditTaxDialog.value = false;
    isDialogVisible.value = false;
}
const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};
const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
};
const handleAddNewTax = async () => {
    form.post(route("setting.taxes.store"), {
        onSuccess: () => {
            closeAddNewTaxModal();
            form.reset();
            fetchTaxes();
            push.success('Tax created Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
const handleEditTax = async () => {
    form.put(route("setting.taxes.update", selectedTaxId.value), {
        onSuccess: () => {
            closeAddNewTaxModal();
            form.reset();
            fetchTaxes();
            push.success('Tax Updated Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
const confirmDeleteTax = (tax) => {
    selectedTaxId.value = tax.value.id;
    confirm.require({
        message: 'Are you sure you want to delete tax?',
        header: 'Delete Tax?',
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
            router.delete(route("setting.taxes.destroy", selectedTaxId.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Tax Deleted Successfully!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedTaxId.value = null;
        },
        reject: () => {
            selectedTaxId.value = null;
        }
    });
};
</script>
<template>
    <AppLayout title="Special DO Charges">
        <template #header>Special DO Charges</template>
        <Breadcrumb/>
        <div>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" :model="menuModel"  @hide="selectedDOCharge = null"/>
                    <DataTable
                        v-model:contextMenuSelection="selectedDOCharge"
                        v-model:selection="selectedDOCharge"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="taxes"
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
                                    Tax Rates
                                </div>
                                <div>
                                    <Link v-if="usePage().props.user.permissions.includes('tax.destination tax create')" :href="route('setting.special-do-charges.create')">
                                        <PrimaryButton class="w-full">Create DO Charge</PrimaryButton>
                                    </Link>
<!--                                    <PrimaryButton-->
<!--                                        v-if="usePage().props.user.permissions.includes('tax.destination tax create')"-->
<!--                                        class="w-full"-->
<!--                                        @click="confirmViewAddNewDOCharge()"-->
<!--                                    >-->
<!--                                        Create DO Charge-->
<!--                                    </PrimaryButton>-->
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
                        <template #empty> No Tax found. </template>
                        <template #loading> Loading Tax data. Please wait.</template>
                        <Column field="id" header="ID" sortable></Column>
                        <Column field="name" header="Name" sortable></Column>
                        <Column field="rate" header="Rate" sortable></Column>
                        <Column field="is_active" header="Active">
                            <template #body="{ data }">
                                <i :class="{ 'pi-check-circle text-green-500': data.is_active, 'pi-times-circle text-red-400': !data.is_active }" class="pi"></i>
                            </template>
                        </Column>
                        <template #footer> In total there are {{ taxes ? totalRecords : 0 }} Air Lines.</template>
                    </DataTable>
                </template>
            </Card>
            <!-- Add New Air Line Dialog -->
            <Dialog
                v-model:visible="isDialogVisible"
                modal
                :header="showAddNewDOChargeDialog ? 'Add New DO Charge' : 'DO Charge'"
                :style="{ width: '60rem' }"
                :block-scroll="true"
                @hide="onDialogHide"
                @show="onDialogShow"
            >
                <div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <InputLabel value="Agent" />
                            <Select
                                v-model="form.agent_id"
                                :options="branches"
                                class="w-full"
                                filter
                                option-label="name"
                                option-value="id"
                                placeholder="Select Agent"
                            />
                            <InputError :message="form.errors.agent_id" />
                        </div>

                        <!-- Tax Rate -->
                        <div>
                            <InputLabel value="Agent" />
                            <Select
                                v-model="form.hbl_type"
                                :options="hblTypeArray"
                                class="w-full"
                                filter
                                option-label="name"
                                option-value="name"
                                placeholder="Select Agent"
                            />
                            <InputError :message="form.errors.hbl_type" />
                        </div>

                        <!-- Is Active Toggle -->
                        <div class="flex items-center gap-3">
                            <span class="font-medium">Is Active</span>
                            <ToggleSwitch v-model="form.is_active">
                                <template #handle="{ checked }">
                                    <i :class="['!text-xs pi', { 'pi-check': checked, 'pi-times': !checked }]" />
                                </template>
                            </ToggleSwitch>
                        </div>
                    </div>
                </div>

                <!-- Dialog Footer -->
                <template #footer>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Button
                            label="Cancel"
                            class="p-button-text"
                            @click="closeAddNewTaxModal"
                        />
                        <Button
                            :label="showAddNewTaxDialog ? 'Add Tax' : 'Update Tax'"
                            class="p-button-primary"
                            :icon="showAddNewTaxDialog ? 'pi pi-plus' : 'pi pi-check'"
                            @click.prevent="showAddNewTaxDialog ? handleAddNewTax() : handleEditTax()"
                        />
                    </div>
                </template>
            </Dialog>

        </div>
    </AppLayout>
</template>
