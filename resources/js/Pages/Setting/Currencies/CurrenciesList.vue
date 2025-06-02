<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { onMounted, ref, watch} from "vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Column from "primevue/column";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import {useConfirm} from "primevue/useconfirm";
import {FilterMatchMode} from "@primevue/core/api";
import axios from "axios";
import {debounce} from "lodash";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    branchCurrencies: {
        type: Array,
        default: () => [],
    }
})

const confirm = useConfirm();
const baseUrl = ref("currencies/list");
const loading = ref(true);
const currencies = ref([]);
const latestCurrencyRates = ref([]);
const totalRecords = ref(0);
const perPage = ref(10);
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

const confirmViewEditCurrency = (currency) => {
    form.currency_name = currency.data.currency_name;
    form.currency_symbol = currency.data.currency_symbol;
    form.sl_rate = currency.data.sl_rate;
    selectedCurrencyId.value = currency.data.id;
    showEditCurrencyDialog.value = true;
    isDialogVisible.value = true;
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

        // Filter only the latest per currency_name (based on created_at or id)
        // const latestMap = new Map();
        //
        // currencies.value.forEach(item => {
        //     const key = item.currency_name;
        //     if (!latestMap.has(key)) {
        //         latestMap.set(key, item);
        //     } else {
        //         const existing = latestMap.get(key);
        //         const isNewer = new Date(item.created_at) > new Date(existing.created_at);
        //         if (isNewer) {
        //             latestMap.set(key, item);
        //         }
        //     }
        // });
        //
        // latestCurrencyRates.value = Array.from(latestMap.values());

        const grouped = {};
        currencies.value.forEach(item => {
            const key = item.currency_name;
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(item);
        });

        // For each group, sort by created_at descending, pick the latest and previous, and calculate % change
        const result = Object.values(grouped).map(records => {
            records.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            const latest = records[0];
            const previous = records[1];

            if (latest && previous) {
                const diff = latest.sl_rate - previous.sl_rate;
                const percentChange = (diff / previous.sl_rate) * 100;
                // latest.rate_change_percent = parseFloat(percentChange.toFixed(2));
                latest.change_flag = diff > 0 ? 'increase' : diff < 0 ? 'decrease' : 'no-change';
            } else {
                latest.rate_change_percent = null; // No comparison possible
            }

            return latest;
        });

        latestCurrencyRates.value = result;

        console.log(latestCurrencyRates.value)
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
    form.reset();
    form.clearErrors();
    showAddNewCurrencyDialog.value = false;
    showEditCurrencyDialog.value = false;
    isDialogVisible.value = false;
}

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    form.reset();
    form.clearErrors();
    document.body.classList.remove('p-overflow-hidden');
};

const handleAddNewCurrency = async () => {
    form.post(route("setting.currencies.store"), {
        onSuccess: () => {
            closeAddNewCurrencyModal();
            form.reset();
            form.clearErrors();
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
    showChangeCurrencyRateDialog.value = false;
    confirmUpdateRates();
}

const confirmUpdateRates = () => {
    const idList = selectedCurrencies.value.map((item) => item.id);
    confirm.require({
        message: 'Are you sure you to change currency rates?',
        header: 'Change Currency Rate?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'warn',
            outlined: true
        },
        acceptProps: {
            label: 'Update',
            severity: 'success'
        },
        accept: async () => {
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
                router.visit(route(currentRoute, route().params));
            }
        },
        reject: () => {
            newCurrencyRate.value = "";
        },
        onHide: () => {
            newCurrencyRate.value = "";
        }
    });
};


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

watch(() => form.currency_name, (newVal) => {
    const selected = props.branchCurrencies.find(
        currency => currency.currency_name === newVal
    );
    form.currency_symbol = selected ? selected.currency_symbol : '';
});
</script>
<template>
    <AppLayout title="Currency Rates">
        <template #header>Currency Rates</template>

        <Breadcrumb/>

        <div>
            <Card class="my-5">
                <template #content>
                    <DataTable
                        :loading="loading"
                        :rows="perPage"
                        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
                        :totalRecords="totalRecords"
                        :value="currencies"
                        dataKey="id"
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
                                    Currency Rates
                                </div>
                                <div class="flex items-center gap-3">
                                    <Button
                                        v-if="usePage().props.user.permissions.includes('currencies.create')"
                                        @click="confirmViewAddNewCurrency()"
                                    >
                                        Create Currency Rate
                                    </Button>
<!--                                    <Button-->
<!--                                        type="button"-->
<!--                                        v-if="usePage().props.user.permissions.includes('currencies.edit')"-->
<!--                                        label="Change Currency Rate" icon="pi pi-refresh"-->
<!--                                        :disabled="selectedCurrencies.length === 0"-->
<!--                                        @click="showChangeCurrencyRateDialog = true" />-->
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

                            <div id="widget" class="grid gap-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
                                <div
                                    v-for="(rate, index) in latestCurrencyRates"
                                    v-if="latestCurrencyRates"
                                    :key="index"
                                    class="h-48 rounded-lg bg-white p-4 flex flex-col items-center justify-center"
                                >
                                    <div class="flex flex-col space-x-2">
                                        <i v-if="rate.change_flag === 'increase'" class="ti ti-trending-up text-3xl text-center text-green-500"></i>
                                        <i v-else-if="rate.change_flag === 'decrease'" class="ti ti-trending-down text-3xl text-center text-red-500"></i>
                                        <i v-else class="ti ti-trending-up text-3xl text-center text-green-500"></i>
                                        <div class="text-center text-4xl font-bold">{{rate?.sl_rate.toFixed(2)}}</div>
                                        <div class="my-2 text-center text-gray-500">
                                            {{rate?.currency_name}}
                                        </div>
<!--                                        <div class="flex items-center">-->
<!--                                            <div class="mx-auto flex items-center">-->
<!--                                                <i v-if="rate.change_flag === 'increase'" class="ti ti-arrow-up-right text-lg text-green-500"></i>-->
<!--                                                <i v-else-if="rate.change_flag === 'decrease'" class="ti ti-arrow-down-left text-lg text-red-500"></i>-->
<!--                                                <i v-else class="ti ti-arrow-up-right text-lg text-green-500"></i>-->
<!--                                                <span>{{rate?.rate_change_percent || 0}}%</span>-->
<!--                                            </div>-->
<!--                                        </div>-->
                                    </div>
                                </div>
                            </div>

                        </template>

                        <template #empty> No Currency Rates found. </template>

                        <template #loading> Loading Currency Rates data. Please wait.</template>

                        <Column field="currency_name" header="Currency Name" sortable></Column>

                        <Column field="currency_symbol" header="Currency Symbol" sortable></Column>

                        <Column field="sl_rate" header="SL Rate" header-class="!flex justify-end" sortable>
                            <template #body="slotProps">
                                <div class="flex justify-end">
                                    {{ slotProps.data.sl_rate.toFixed(2) }}
                                </div>
                            </template>
                        </Column>

                        <Column field="created_at" header="Created At" sortable></Column>

                        <Column field="updated_by" header="Updated By" sortable />

<!--                        <Column field="" header="Actions" style="width: 10%">-->
<!--                            <template #body="{ data }">-->
<!--                                <Button-->
<!--                                    icon="pi pi-pencil"-->
<!--                                    outlined-->
<!--                                    rounded-->
<!--                                    size="small"-->
<!--                                    class="p-1 text-xs h-3 w-3 mr-1"-->
<!--                                    @click="confirmViewEditCurrency({ data })"-->
<!--                                    :disabled="!usePage().props.user.permissions.includes('currencies.edit')"-->
<!--                                />-->
<!--                                <Button-->
<!--                                    icon="pi pi-trash"-->
<!--                                    outlined-->
<!--                                    rounded-->
<!--                                    severity="danger"-->
<!--                                    size="small"-->
<!--                                    @click="confirmDeleteCurrency({ data })"-->
<!--                                    :disabled="!usePage().props.user.permissions.includes('currencies.delete')"-->
<!--                                />-->

<!--                            </template>-->
<!--                        </Column>-->

                        <template #footer> In total there are {{ currencies ? totalRecords : 0 }} Currency Rates.</template>
                    </DataTable>
                </template>
            </Card>
            <!-- Add New Currency Dialog -->
            <Dialog
                v-model:visible="isDialogVisible"
                modal
                :header="showAddNewCurrencyDialog ? 'New Currency Rate' : 'Edit Currency Rate'"
                :style="{ width: '90%', maxWidth: '450px' }"
                :block-scroll="true"
                @hide="onDialogHide"
                @show="onDialogShow"
            >
                <div class="mt-4">
                    <Select
                        v-model="form.currency_name"
                        :options="branchCurrencies"
                        class="w-full"
                        option-label="currency_name"
                        option-value="currency_name"
                        placeholder="Currency Name"
                    />
                    <InputError :message="form.errors.currency_name"/>
                </div>
                <div class="mt-2">
                    <InputText
                        v-model="form.currency_symbol"
                        class="w-full p-inputtext"
                        disabled
                        required
                        placeholder="Currency Symbol"
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
                            :label="showAddNewCurrencyDialog ? 'Add Currency Rate' : 'Update Currency Rate'"
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
