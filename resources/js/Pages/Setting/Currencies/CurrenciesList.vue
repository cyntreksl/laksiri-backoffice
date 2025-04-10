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
import InputNumber from "primevue/inputnumber";

const cm = ref();
const confirm = useConfirm();
const baseUrl = ref("currencies/list");
const loading = ref(true);
const currencies = ref([]);
const totalRecords = ref(0);
const perPage = ref(100);
const currentPage = ref(1);
const selectedCurrency = ref(null);
const selectedCurrencyId = ref(null);
const selectedCurrencies = ref([]);

const isDialogVisible = ref(false);
const showEditCurrencyDialog = ref(false);
const showDeleteCurrencyDialog = ref(false);
const showChangeCurrencyRateDialog = ref(false);
const newCurrencyRate = ref("");

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
    currency_name: "",
    currency_symbol: "",
    sl_rate: "",
})

const menuModel = ref([
    {
        label: "Edit",
        icon: "pi pi-fw pi-pencil",
        command: () => confirmViewEditCurrency(selectedCurrency),
        disabled: !usePage().props.user.permissions.includes("currencies.edit"),
    },
    {
        label: "Delete",
        icon: "pi pi-fw pi-times",
        command: () => confirmDeleteCurrency(selectedCurrency),
        disabled: !usePage().props.user.permissions.includes("currencies.delete"),
    },
]);

const confirmViewEditCurrency = (currency) => {
    form.currency_name = currency.data.currency_name;
    form.currency_symbol = currency.data.currency_symbol;
    form.sl_rate = currency.data.sl_rate;
    selectedCurrencyId.value = currency.data.id;
    showEditCurrencyDialog.value = true;
    isDialogVisible.value = true;
};

const confirmCurrencyDelete = (currency) => {
    selectedCurrencyId.value = currency.value.id;
    showDeleteCurrencyDialog.value = true;
};

const fetchCurrencies = async (page = 1, search = "", sortField = 'id', sortOrder = 0) => {
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
        currencies.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error fetching Currencies:", error);
    } finally {
        loading.value = false;
    }
};

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    fetchCurrencies(currentPage.value);
};

const onRowContextMenu = (event) => {
    cm.value.show(event.originalEvent);
};

const onSort = (event) => {
    fetchCurrencies(currentPage.value, filters.value.global.value, event.sortField, event.sortOrder);
};

onMounted(() => {
    fetchCurrencies();
});

const debouncedFetchCurrencies = debounce((searchValue) => {
    fetchCurrencies(1, searchValue);
}, 1000);

watch(() => filters.value.global.value, (newValue) => {
    if (newValue !== null) {
        debouncedFetchCurrencies(newValue);
    }
});

const showAddNewCurrencyDialog = ref(false);

const confirmViewAddNewCurrency = () => {
    showAddNewCurrencyDialog.value = true;
    isDialogVisible.value = true;
};

const closeAddNewCurrencyModal = () => {
    form.name = "";
    showAddNewCurrencyDialog.value = false;
    showEditCurrencyDialog.value = false;
    isDialogVisible.value = false;
}

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
};

const handleAddNewCurrency = async () => {
    form.post(route("setting.currencies.store"), {
        onSuccess: () => {
            closeAddNewCurrencyModal();
            form.reset();
            fetchCurrencies();
            push.success('Create Currency Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const handleEditCurrency = async () => {
    form.put(route("setting.currencies.update", selectedCurrencyId.value), {
        onSuccess: () => {
            closeAddNewCurrencyModal();
            form.reset();
            fetchCurrencies();
            push.success('Currency Updated Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}

const closeUpdateRatesModal = () => {
    selectedCurrencies.value = [];
    newCurrencyRate.value = "";
    showChangeCurrencyRateDialog.value = false;
}

const handleUpdateRates = async () => {
    const idList = selectedCurrencies.value.map((item) => item.id);
    const response = await fetch("/currencies/update-currency-rates", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            currency_ids: idList,
            sl_rate: newCurrencyRate.value
        }),
    });

    if (!response.ok) {
        throw new Error("Network response was not ok.");
    } else {
        await fetchCurrencies();
        showChangeCurrencyRateDialog.value = false;
        selectedCurrencies.value = [];
        newCurrencyRate.value = "";
        push.success("Currency Rates Updated successfully!");
    }
}


const confirmDeleteCurrency = (currency) => {
    selectedCurrencyId.value = currency.data.id;
    confirm.require({
        message: 'Are you sure you want to delete currency?',
        header: 'Delete Currency?',
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
            router.delete(route("setting.currencies.destroy", selectedCurrencyId.value), {
                preserveScroll: true,
                onSuccess: () => {
                    push.success("Currency Deleted Successfully!");
                    const currentRoute = route().current();
                    router.visit(route(currentRoute, route().params));
                },
                onError: () => {
                    push.error("Something went to wrong!");
                },
            });
            selectedCurrencyId.value = null;
        },
        reject: () => {
            selectedCurrencyId.value = null;
        }
    });
};
</script>
<template>
    <AppLayout title="Currencies">
        <template #header>Currencies</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <ContextMenu ref="cm" @hide="selectedCurrency = null"/>
                    <DataTable
                        v-model:contextMenuSelection="selectedCurrency"
                        v-model:selection="selectedCurrencies"
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="currencies"
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
                                    Currencies
                                </div>
                                <div>
                                    <PrimaryButton
                                        v-if="usePage().props.user.permissions.includes('currencies.create')"
                                        class="w-full"
                                        @click="confirmViewAddNewCurrency()"
                                    >
                                        Create Currency
                                    </PrimaryButton>
                                    <PrimaryButton
                                        v-if="usePage().props.user.permissions.includes('currencies.edit')"
                                        :disabled="selectedCurrencies.length === 0"
                                        class="w-full mt-3"
                                        @click="showChangeCurrencyRateDialog = true"
                                    >
                                        Change currency
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

                        <template #empty> No Currency found. </template>

                        <template #loading> Loading Currencies data. Please wait.</template>

                        <Column headerStyle="width: 3rem" selectionMode="multiple"></Column>

                        <Column field="currency_name" header="Currency Name" sortable></Column>

                        <Column field="currency_symbol" header="Currency Symbol" sortable></Column>

                        <Column field="sl_rate" header="SL Rate" sortable></Column>

                        <Column field="" header="Actions" style="width: 10%">
                            <template #body="{ data }">
                                <Button
                                    icon="pi pi-pencil"
                                    outlined
                                    rounded
                                    size="small"
                                    class="p-1 text-xs h-3 w-3 mr-1"
                                    @click="confirmViewEditCurrency({ data })"
                                    :disabled="!usePage().props.user.permissions.includes('currencies.edit')"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    outlined
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteCurrency({ data })"
                                    :disabled="!usePage().props.user.permissions.includes('currencies.delete')"
                                />

                            </template>
                        </Column>

                        <template #footer> In total there are {{ currencies ? totalRecords : 0 }} Currencies.</template>
                    </DataTable>
                </template>
            </Card>
            <!-- Add New Air Line Dialog -->
            <Dialog
                v-model:visible="isDialogVisible"
                modal
                :header="showAddNewCurrencyDialog ? 'Add New Currency' : 'Edit Currency'"
                :style="{ width: '90%', maxWidth: '450px' }"
                :block-scroll="true"
                @hide="onDialogHide"
                @show="onDialogShow"
            >
                <div class="mt-4">
                    <InputText
                        v-model="form.currency_name"
                        class="w-full p-inputtext"
                        placeholder="Enter Currency Name"
                        required
                        type="text"
                    />
                    <InputError :message="form.errors.currency_name"/>
                </div>
                <div class="mt-2">
                    <InputText
                        v-model="form.currency_symbol"
                        class="w-full p-inputtext"
                        placeholder="Enter Currency Symbol"
                        required
                        type="text"
                    />
                    <InputError :message="form.errors.currency_symbol"/>
                </div>
                <div class="mt-2">
                    <InputText
                        v-model="form.sl_rate"
                        class="w-full p-inputtext"
                        placeholder="Enter Rate(LKR)"
                        required
                        type="text"
                        :maxFractionDigits="5" :minFractionDigits="2" min="0" step="any" variant="filled"
                    />
                    <InputError :message="form.errors.sl_rate"/>
                </div>

                <template #footer>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Button label="Cancel" class="p-button-text" @click="closeAddNewCurrencyModal" />
                        <Button
                            :label="showAddNewCurrencyDialog ? 'Add Currency' : 'Update Currency'"
                            class="p-button-primary"
                            :icon="showAddNewCurrencyDialog ? 'pi pi-plus' : 'pi pi-check'"
                            @click.prevent="showAddNewCurrencyDialog ? handleAddNewCurrency() : handleEditCurrency()"
                        />
                    </div>
                </template>
            </Dialog>

            <Dialog
                v-model:visible="showChangeCurrencyRateDialog"
                modal
                :header="'Edit Currency Rate'"
                :style="{ width: '90%', maxWidth: '450px' }"
                :block-scroll="true"
                @hide="onDialogHide"
                @show="onDialogShow"
            >
                <div class="mt-2">
                    <InputText
                        v-model="newCurrencyRate"
                        class="w-full p-inputtext"
                        placeholder="Enter Rate(LKR)"
                        required
                        type="text"
                        :maxFractionDigits="5" :minFractionDigits="2" min="0" step="any" variant="filled"
                    />
                </div>

                <template #footer>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Button label="Cancel" class="p-button-text" @click="closeUpdateRatesModal" />
                        <Button
                            :label="'Update Currency Rate'"
                            class="p-button-primary"
                            :icon="'pi pi-check'"
                            @click.prevent="handleUpdateRates()"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
    </AppLayout>

</template>
