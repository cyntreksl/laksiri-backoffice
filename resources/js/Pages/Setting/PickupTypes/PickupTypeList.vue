<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import {computed, onMounted, ref, watch} from "vue";
import { push } from "notivue";
import {debounce} from "lodash";
import {FilterMatchMode} from "@primevue/core/api";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import ContextMenu from "primevue/contextmenu";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import {useConfirm} from "primevue/useconfirm";
import axios from "axios";
import Dialog from "primevue/dialog";
import InputError from "@/Components/InputError.vue";

defineProps({
    pickupTypes: {
        type: Object,
        default: () => {},
    },
});

const pickupType = ref({});
const cm = ref();
const confirm = useConfirm();
const baseUrl = ref("pickup-types/list");
const loading = ref(true);
const pickupTypes = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const selectedPickupType = ref(null);
const selectedPickupTypeId = ref(null);
const isDialogVisible = ref(false);
const showUpdatePickupTypeDialog = ref(false);
const showDeletePickupTypeDialog = ref(false);

const filters  = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    warehouse: { value: null, matchMode: FilterMatchMode.EQUALS },
    hbl_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    cargo_type: { value: null, matchMode: FilterMatchMode.EQUALS },
    is_hold: { value: null, matchMode: FilterMatchMode.EQUALS },
    user: {value: null, matchMode: FilterMatchMode.EQUALS},
    payments: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const form = useForm({
    pickup_type_name: "",
})

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => confirmUpdatePickupType(selectedPickupType),
        disabled: !usePage().props.user.permissions.includes("pickup-type.edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-trash",
        command: () => confirmPickupTypeDelete(selectedPickupType),
        disabled: !usePage().props.user.permissions.includes("pickup-type.delete"),
    },
]);

const confirmUpdatePickupType = (pickupType) => {
   form.pickup_type_name = pickupType.value.pickup_type_name;
   selectedPickupTypeId.value = pickupType.value.id;
    showUpdatePickupTypeDialog.value = true;
    isDialogVisible.value = true;

};

const fetchPickupTypes = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
    loading.value = true;
    try{
    const response = await axios.get(baseUrl.value, {
        params: {
            page,
            per_page: perPage.value,
            search,
            sort_field: sortField,
            sort_order: sortOrder === 1 ? "desc" : "asc",
        },
    });
    pickupTypes.value = response.data.data;
    totalRecords.value = response.data.meta.total;
    currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching pickup types:", error);
    } finally {
        loading.value = false;
    }
}

const onPageChange = (event) => {
  perPage.value = event.rows;
  currentPage.value = event.page + 1;
  fetchPickupTypes(currentPage.value);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const onSort = (event) => {
    fetchPickupTypes(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchPickupTypes();
});

const  debouncedFetchPickupTypes = debounce((searchValue) => {
    fetchPickupTypes(1, searchValue);
}, 1000);

watch (() => filters.value.global.value, (newValue) => {
    debouncedFetchPickupTypes(newValue);
});

const exportURL = computed(() => {
    const params = new URLSearchParams({

    }).toString();
    return `/warehouses/export?${params}`;
});

const showAddNewPickupTypeDialog =  ref (false);

const confirmViewAddNewPickupType = () => {
    showAddNewPickupTypeDialog.value = true;
    isDialogVisible.value = true;
};

const closeAddNewPickupTypeModal = () => {
    form.pickup_type_name = "";
    showAddNewPickupTypeDialog.value = false;
    showUpdatePickupTypeDialog.value = false;
    isDialogVisible.value = false;
};

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
};

const handleAddNewPickupType = async () => {
    form.post(route("setting.pickup-types.store"), {
        onSuccess: () => {
           closeAddNewPickupTypeModal();
           form.reset();
           fetchPickupTypes();
           push.success("Pickup Type Created Successfully!");
        },
        onError: () => {
            push.error("Error creating pickup type");
        },
        preserveScroll: true,
        preserveState: true,
    });
};

const handleUpdatePickupType = async () => {
    form.put (route("setting.pickup-types.update", selectedPickupTypeId.value),{
        onSuccess: () => {
            closeAddNewPickupTypeModal();
            form.reset();
            fetchPickupTypes();
            push.success("Pickup Type Updated Successfully!");
        },
        onError: () => {
            push.error("Error updating pickup type");
        },
        preserveScroll: true,
        preserveState: true,
    })
};

const confirmPickupTypeDelete = (pickupType) => {
    selectedPickupTypeId.value = pickupType.value.id;
    confirm.require({
        message: "Are you sure you want to delete this pickup type?",
        header: "Delete Pickup Type",
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
            router.delete(route("setting.pickup-types.destroy", selectedPickupTypeId.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Pickup Type Deleted Successfully!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedPickupTypeId.value = null;
        },
        reject: () => {
            selectedPickupTypeId.value = null;
        },
    })

};

</script>

<template>
    <AppLayout title="Pickup Types">
        <template #header>Pickup Types</template>
        <Breadcrumb />
        <div>
            <card class="my-5">
                <template #content>

                    <DataTable
                        v-model:contextMenuSelection="selectedPickupType"
                        v-model:selection="selectedPickupType"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="pickupTypes"
                        context-menu
                        data-key="id"
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
                                    Pickup Types
                                </div>
                                <div>
                                    <PrimaryButton
                                        v-if="usePage().props.user.permissions.includes('pickup-type.create')"
                                        class="w-full"
                                        @click="confirmViewAddNewPickupType()"
                                    >
                                        Create Pickup Type
                                    </PrimaryButton>
                                </div>
                            </div>

                        </template>
                        <template #empty> No Pickup Types found. </template>

                        <template #loading> Loading Pickup Type data. Please wait.</template>


                        <Column field="pickup_type_name" header="Name" sortable></Column>

                        <column  header="Action" >
                            <template #body="{ data }">
                                <Button
                                    icon="pi pi-pencil"
                                    outlined
                                    rounded
                                    class="mr-2"
                                    @click="confirmUpdatePickupType({ value: data })"
                                    :disabled="!usePage().props.user.permissions.includes('pickup-type.edit')"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    @click="confirmPickupTypeDelete({ value: data })"
                                    :disabled="!usePage().props.user.permissions.includes('pickup-type.delete')"
                                />

                            </template>
                        </column>


                        <template #footer> In total there are {{ pickupType ? totalRecords : 0 }} Pickup Types.</template>

                    </DataTable>

                </template>
            </card>
            <!-- Add New Pickup Type Dialog -->
            <Dialog
                v-model:visible="isDialogVisible"
                modal
                :header="showAddNewPickupTypeDialog ? 'Add New Pickup Type' : 'Edit Pickup Type'"
                :style="{ width: '90%', maxWidth: '450px' }"
                :block-scroll="true"
                @hide="onDialogHide"
                @show="onDialogShow"
                >
                <div class="mt-4">
                    <InputText
                        v-model="form.pickup_type_name"
                        classs ="w-full p-inputtext"
                        placeholder="Pickup Type Name"
                        required
                        type="text"
                    />
                    <InputError :message="form.errors.pickup_type_name"/>
                </div>
                <template #footer>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Button label="Cancel" class="p-button-text" @click="closeAddNewPickupTypeModal" />
                        <Button
                            :label="showAddNewPickupTypeDialog ? 'Add New Pickup Type' : 'Update Pickup Type'"
                            class="p-button-primary"
                            :icon="showAddNewPickupTypeDialog ? 'pi pi-plus' : 'pi pi-check'"
                            @click.prevent="showAddNewPickupTypeDialog ? handleAddNewPickupType():handleUpdatePickupType()" />
                    </div>
                </template>

            </Dialog>
        </div>

    </AppLayout>
</template>
