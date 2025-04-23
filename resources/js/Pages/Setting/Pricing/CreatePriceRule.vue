<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import RadioButton from "@/Components/RadioButton.vue";
import {reactive, ref, watch} from "vue";
import Button from "primevue/button";
import Card from 'primevue/card';
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Dialog from "primevue/dialog";
import Checkbox from 'primevue/checkbox';
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import {useConfirm} from "primevue/useconfirm";

defineProps({
    cargoModes: {
        type: Array,
        default: () => []
    },
    hblTypes: {
        type: Array,
        default: () => []
    },
    branches: {
        type: Object,
        default: () => {}
    },
})

const showAddNewPriceRuleDialog = ref(false);
const editMode = ref(false);
const ruleList = ref([]);
const priceModes = ['Weight', 'Volume'];
const editIndex = ref(null);
const confirm = useConfirm();

const form = useForm({
    destination_branch_id: null,
    cargo_mode: '',
    hbl_type: '',
    price_mode: '',
    priceRules: {},
});

// Define the priceRuleItem reactive object
const priceRuleItem = reactive({
    condition: ruleList.value.length <= 0 ? '>0' : '',
    true_action: '',
    false_action: '0',
    bill_price: null,
    bill_vat: null,
    volume_charges: '',
    per_package_charges: '',
    is_editable: false,
});

const handlePriceRuleCreate = () => {
    form.priceRules = ruleList.value;
    if (form.priceRules.length <= 0) {
        push.error('Please add at least one price rule.');
        return;
    }else{
        form.priceRules = ruleList.value;

        form.post(route("setting.prices.store"), {
            onSuccess: () => {
                form.reset();
                router.visit(route("setting.prices.index"));
                push.success('Price rule created successfully!');
            },
            onError: () => {
                push.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
    }
}

watch([() => form.cargo_mode], ([newCargoMode]) => {
    console.log(newCargoMode);
    if (newCargoMode === "Sea Cargo") {
        form.price_mode = "Volume";
    } else if (newCargoMode === "Air Cargo") {
        form.price_mode = "Weight";
    }
});

const showAddPriceRuleDialog = () => {
    showAddNewPriceRuleDialog.value = true;
};

const closeAddPriceRuleModal = () => {
    showAddNewPriceRuleDialog.value = false;
    restModalFields()
};

const confirmRemovePriceRule = (index) => {
    confirm.require({
        message: 'Would you like to remove this price rule?',
        header: 'Remove Price Rule?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Remove Package',
            severity: 'danger'
        },
        accept: () => {
            handleRemovePriceRule(index)
        },
        reject: () => {
        }
    });
};

const handleRemovePriceRule = (index) => {
    if (index !== null) {
        ruleList.value.splice(index,1);
    }
};

const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPriceRuleDialog.value = true;
    Object.assign(priceRuleItem, ruleList.value[index]);
};

const restModalFields = () => {
    priceRuleItem.condition = '';
    priceRuleItem.true_action = '';
    priceRuleItem.false_action = '0';
    priceRuleItem.bill_price = null;
    priceRuleItem.bill_vat = null;
    priceRuleItem.volume_charges = '';
    priceRuleItem.per_package_charges = '';
    priceRuleItem.is_editable = false;
};

const addPriceRuleData = () => {
    if (
        !priceRuleItem.condition ||
        !priceRuleItem.true_action ||
        priceRuleItem.bill_price <= 0 ||
        priceRuleItem.bill_vat < 0 ||
        !priceRuleItem.volume_charges ||
        !priceRuleItem.per_package_charges
    ) {
        push.error("Please fill all required data");
        return;
    }

    if (editMode.value) {
        ruleList.value.splice(editIndex.value, 1, {...priceRuleItem});
        showAddNewPriceRuleDialog.value = false;
        restModalFields()
    }else{
        const newItem = {...priceRuleItem};
        ruleList.value.push(newItem);
        showAddNewPriceRuleDialog.value = false;
        restModalFields()
    }
};

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
};
</script>

<template>
    <AppLayout title="Create Price Rule">
        <template #header>Create Price Rule</template>

        <Breadcrumb/>

        <form @submit.prevent="handlePriceRuleCreate">

            <div class="flex items-center justify-end p-2 my-5 space-x-2">
                <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('setting.prices.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create Price Rule" type="submit" />
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card style="overflow: hidden">
                        <template #header>
                            <div class="flex flex-wrap md:flex-nowrap items-start p-4 my-2 rounded bg-white overflow-auto gap-4">
                                <div class="bg-green-100 p-5 rounded-lg w-full md:w-1/4">
                                    <InputLabel class="mb-2" value="Cargo Mode" />
                                    <div class="flex flex-wrap gap-4">
                                        <label
                                            v-for="cargoType in cargoModes"
                                            :key="cargoType"
                                            class="flex space-x-2 items-center"
                                        >
                                            <RadioButton
                                                v-model="form.cargo_mode"
                                                :label="cargoType"
                                                :value="cargoType"
                                                name="cargoType"
                                            />
                                            <i v-if="cargoType === 'Air Cargo'" class="ti ti-plane-tilt"></i>
                                            <i v-else class="ti ti-sailboat"></i>
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.cargo_mode"/>
                                </div>

                                <div class="bg-blue-100 p-5 rounded-lg w-full md:w-1/4">
                                    <InputLabel class="mb-2" value="HBL Type" />
                                    <div class="flex flex-wrap gap-4">
                                        <label
                                            v-for="hblType in hblTypes"
                                            :key="hblType"
                                            class="flex space-x-2 items-center"
                                        >
                                            <RadioButton
                                                v-model="form.hbl_type"
                                                :label="hblType"
                                                :value="hblType"
                                                name="hblType"
                                            />
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.hbl_type"/>
                                </div>

                                <div class="bg-amber-100 p-5 rounded-lg w-full md:w-1/4">
                                    <InputLabel class="mb-2" value="Branch" />
                                    <div class="flex flex-wrap gap-4">
                                        <label
                                            v-for="branch in branches"
                                            :key="branch"
                                            class="flex space-x-2 items-center"
                                        >
                                            <RadioButton
                                                v-model="form.destination_branch_id"
                                                :label="branch.name"
                                                :value="branch.id"
                                                name="warehouse"
                                            />
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.destination_branch_id"/>
                                </div>

                                <div class="bg-fuchsia-100 p-5 rounded-lg w-full md:w-1/4">
                                    <InputLabel class="mb-2" value="Price Mode" />
                                    <div class="flex flex-wrap gap-4">
                                        <label
                                            v-for="price_mode in priceModes"
                                            :key="price_mode"
                                            class="flex space-x-2 items-center"
                                        >
                                            <RadioButton
                                                v-model="form.price_mode"
                                                :label="price_mode"
                                                :value="price_mode"
                                                disabled
                                                name="price_mode"
                                            />
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.price_mode"/>
                                </div>
                            </div>
                        </template>
                        <template #title>
                            <div class="flex justify-between">
                                <div>Price Rules</div>
                                <Button  :disabled="form.destination_branch_id == null && form.cargo_mode === '' && form.hbl_type === ''"
                                         icon="pi pi-plus" iconPos="left" label="New Price Rule" severity="info" type="button" variant="outlined" @click="showAddPriceRuleDialog"/>
                            </div>
                        </template>
                        <template #content>
                            <div class="mt-5">
                                <DataTable v-if="ruleList.length > 0" :value="ruleList" tableStyle="min-width: 50rem">
                                    <Column header="Actions">
                                        <template #body="slotProps">
                                            <Button v-if="slotProps.data.condition !== '>0'" icon="pi pi-times" rounded severity="danger" size="small" variant="text" @click.prevent="confirmRemovePriceRule(slotProps.index)" />

                                            <Button icon="pi pi-pencil" rounded size="small" variant="text" @click.prevent="openEditModal(slotProps.index)"  />
                                        </template>
                                    </Column>

                                    <Column field="condition" header="Condition"></Column>

                                    <Column field="true_action" header="True Action"></Column>

                                    <Column class="!text-right" field="bill_price" header="Bill Price">
                                        <template #body="slotProps">
                                            {{ slotProps.data.bill_price.toFixed(2) }}
                                        </template>
                                    </Column>
                                    <Column field="bill_vat" header="Bill VAT">
                                        <template #body="slotProps">
                                            {{ slotProps.data.bill_vat }} %
                                        </template>
                                    </Column>
                                    <Column class="!text-right" field="volume_charges" header="Volume Charges">
                                        <template #body="slotProps">
                                            {{ slotProps.data.volume_charges.toFixed(2) }}
                                        </template>
                                    </Column>
                                    <Column class="!text-right" field="per_package_charges" header="Per Package Charges">
                                        <template #body="slotProps">
                                            {{ slotProps.data.per_package_charges.toFixed(2) }}
                                        </template>
                                    </Column>
                                    <Column field="is_editable" header="Editable">
                                        <template #body="{ data }">
                                            <i :class="{ 'pi-times-circle text-red-500': !data.is_editable, 'pi-check-circle text-green-400': data.is_editable }" class="pi"></i>
                                        </template>
                                    </Column>
                                </DataTable>
                                <div v-if="ruleList.length === 0"
                                     class="text-center">
                                    <div class="text-center mb-8">
                                        <div class="text-center mb-4">
                                            <i class="pi pi-money-bill text-orange-300 animate-slow-bounce" style="font-size: 8rem"></i>
                                            <p class="text-gray-600">
                                                No price rules. Please add price rules to view data.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>

    <Dialog v-model:visible="showAddNewPriceRuleDialog" :header="editMode ? `Edit Price Rule` : `Add New Price Rule`" :style="{ width: '60rem' }" block-scroll maximizable modal position="bottom" @hide="onDialogHide" @show="onDialogShow">

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 md:col-span-1">
                <InputLabel>
                    Condition
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputText v-model="priceRuleItem.condition" :disabled="ruleList.length <= 0" class="w-full" placeholder="Type Condition"/>
            </div>

            <div class="col-span-2 md:col-span-1">
                <InputLabel>
                    True Action
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputText v-model="priceRuleItem.true_action" class="w-full" placeholder="Set True Action"/>
            </div>

            <div class="col-span-2 md:col-span-1">
                <InputLabel>
                    Bill Price
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="priceRuleItem.bill_price" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" min="0.00" placeholder="0.00" step="0.01"/>
            </div>

            <div class="col-span-2 md:col-span-1">
                <InputLabel>
                    Bill VAT (%)
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="priceRuleItem.bill_vat" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" min="0.00" placeholder="0.00" step="any"/>
            </div>

            <div class="col-span-2 md:col-span-1">
                <InputLabel>
                    Volume Charge
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="priceRuleItem.volume_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" min="0.00" placeholder="Set Volume Charges" step="any"/>
            </div>

            <div class="col-span-2 md:col-span-1">
                <InputLabel>
                    Per Package Charges
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="priceRuleItem.per_package_charges" :maxFractionDigits="2" :minFractionDigits="2" class="w-full" min="0.00" placeholder="Set Per Package Charges" step="any"/>
            </div>

            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2">
                    <Checkbox v-model="priceRuleItem.is_editable" :checked="priceRuleItem.is_editable" binary inputId="is-editable"/>
                    <label for="is-editable"> Editable </label>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" text @click="closeAddPriceRuleModal" />
            <Button :label="editMode ? `Edi Price Rule` : `Add Price Rule`" severity="help" @click="addPriceRuleData" />
        </template>

    </Dialog>
</template>
