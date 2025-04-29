<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import {reactive, ref, watch} from "vue";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputNumber from 'primevue/inputnumber';
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import RadioButton from "@/Components/RadioButton.vue";
import {useConfirm} from "primevue/useconfirm";

const props = defineProps({
    cargoModes: {
        type: Array,
        default: () => []
    },
    hblTypes: {
        type: Array,
        default: () => []
    },
    branches: {
        type: Array,
        default: () => []
    },
    packageTypes: {
        type: Array,
        default: () => []
    },
});

const collectionMethods = ref([
    { name: 'Destination', value: 'Destination' },
    { name: 'Departure', value: 'Departure' }
]);
const showAddDOChargeDialog = ref(false);
const editMode = ref(false);
const editIndex = ref(null);
const ruleList = ref([]);
const confirm = useConfirm();

const form = useForm({
    agent_id: null,
    cargo_type: '',
    hbl_type: '',
    price_mode: '',
    priceRules: {},
});

// Define the priceRuleItem reactive object
const priceRuleItem = reactive({
    condition: 0,
    collected: '',
    quantity_basis: 1,
    package_type: '',
    charge: 0,
});

const handleDOChargeCreate = () => {
    form.priceRules = ruleList.value;
    if (form.priceRules.length <= 0) {
        push.error('Please add at least one DO Charge record.');
        return;
    }else{
        form.priceRules = ruleList.value;

        form.post(route("setting.special-do-charges.store"), {
            onSuccess: () => {
                form.reset();
                router.visit(route("setting.special-do-charges.index"));
                push.success('DO Charge records created successfully!');
            },
            onError: () => {
                push.error('Something went to wrong!');
            },
            preserveScroll: true,
            preserveState: true,
        });
    }
}

watch([() => form.agent_id], ([newAgent]) => {
    filteredPackageType();
});

const filteredPackageType = () => {
    return props.packageTypes.filter(pkg => pkg.branch_id === form.agent_id);
};

const showAddPriceRuleDialog = () => {
    showAddDOChargeDialog.value = true;
};

const closeAddPriceRuleModal = () => {
    showAddDOChargeDialog.value = false;
    restModalFields()
};

const confirmRemovePriceRule = (index) => {
    confirm.require({
        message: 'Would you like to remove this DO charge record?',
        header: 'Remove DO charge?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Remove',
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
    showAddDOChargeDialog.value = true;
    Object.assign(priceRuleItem, ruleList.value[index]);
};

const restModalFields = () => {
    priceRuleItem.condition = 0;
    priceRuleItem.collected = '';
    priceRuleItem.package_type = '';
    priceRuleItem.charge = 0;
};

const addPriceRuleData = () => {
    if (
        priceRuleItem.condition < 0||
        !priceRuleItem.collected ||
        !priceRuleItem.package_type ||
        priceRuleItem.charge < 0
    ) {
        push.error("Please fill all required data");
        return;
    }
  const isDuplicate = ruleList.value.some((item, index) => {
    // Skip the current item being edited
    if (editMode.value && index === editIndex.value) return false;
    return (
        item.condition === priceRuleItem.condition &&
        item.collected === priceRuleItem.collected &&
        item.package_type === priceRuleItem.package_type
    );
  });

  if (isDuplicate) {
    push.error("A similar charge rule already exists.");
    return;
  }

    if (editMode.value) {
        ruleList.value.splice(editIndex.value, 1, {...priceRuleItem});
        showAddDOChargeDialog.value = false;
        restModalFields()
    }else{
        const newItem = {...priceRuleItem};
        ruleList.value.push(newItem);
        showAddDOChargeDialog.value = false;
        restModalFields()
    }
};

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
}

</script>

<template>
    <AppLayout title="Create DO Charge">
        <template #header>Create DO Charge</template>

        <Breadcrumb/>

        <form @submit.prevent="handleDOChargeCreate">

            <div class="flex items-center justify-end p-2 my-5 space-x-2">
                <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('setting.special-do-charges.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create DO Charge" type="submit" />
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <Card style="overflow: hidden">
                        <template #header>
                            <div class="flex flex-wrap md:flex-nowrap items-start p-4 my-2 rounded bg-white overflow-auto gap-4">
                                <div class="bg-green-100 p-5 rounded-lg w-full md:w-1/4">
                                    <InputLabel class="mb-2" value="Cargo Mode" />
                                    <div class="flex flex-wrap gap-4 mb-1">
                                        <label
                                            v-for="cargoType in cargoModes"
                                            :key="cargoType"
                                            class="flex space-x-2 items-center"
                                        >
                                            <RadioButton
                                                v-model="form.cargo_type"
                                                :label="cargoType"
                                                :value="cargoType"
                                                name="cargoType"
                                            />
                                            <i v-if="cargoType === 'Air Cargo'" class="ti ti-plane-tilt"></i>
                                            <i v-else class="ti ti-sailboat"></i>
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.cargo_type"/>
                                </div>

                                <div class="bg-blue-100 p-5 rounded-lg w-full md:w-1/4">
                                    <InputLabel class="mb-2" value="HBL Type" />
                                    <div class="flex flex-wrap gap-4 mb-1">
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

                                <div class="bg-amber-100 p-5 rounded-lg w-full md:w-2/4">
                                    <InputLabel class="mb-2" value="Agent" />
                                    <div class="flex flex-wrap gap-4 mb-1">
                                        <label
                                            v-for="branch in branches"
                                            :key="branch"
                                            class="flex space-x-2 items-center"
                                        >
                                            <RadioButton
                                                v-model="form.agent_id"
                                                :label="branch.name"
                                                :value="branch.id"
                                                name="agent"
                                            />
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.agent_id"/>
                                </div>
                            </div>
                        </template>
                        <template #title>
                            <div class="flex justify-between">
                                <div>DO Charges</div>
                                <Button  :disabled="form.agent_id === null || form.cargo_type === '' || form.hbl_type === ''"
                                         icon="pi pi-plus" iconPos="left" label="New Do Charge" severity="info" type="button" variant="outlined" @click="showAddPriceRuleDialog"/>
                            </div>
                        </template>
                        <template #content>
                            <div class="mt-5">
                                <DataTable v-if="ruleList.length > 0" :value="ruleList" tableStyle="min-width: 50rem">
                                    <Column header="Actions">
                                        <template #body="slotProps">
                                            <Button icon="pi pi-times" rounded severity="danger" size="small" variant="text" @click.prevent="confirmRemovePriceRule(slotProps.index)" />

                                            <Button icon="pi pi-pencil" rounded size="small" variant="text" @click.prevent="openEditModal(slotProps.index)"  />
                                        </template>
                                    </Column>

                                    <Column field="condition" header="Condition"></Column>

                                    <Column field="collected" header="Collected"></Column>

                                    <Column field="package_type" header="Package Type"></Column>

                                    <Column class="!text-right" field="charge" header="Charge">
                                        <template #body="slotProps">
                                            {{ slotProps.data.charge.toFixed(2) }}
                                        </template>
                                    </Column>
                                </DataTable>
                                <div v-if="ruleList.length === 0"
                                     class="text-center">
                                    <div class="text-center mb-8">
                                        <div class="text-center mb-4">
                                            <i class="pi pi-money-bill text-orange-300 animate-slow-bounce" style="font-size: 8rem"></i>
                                            <p class="text-gray-600">
                                                No DO Charges. Please add DO Charges to view data.
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

        <Dialog v-model:visible="showAddDOChargeDialog" :header="editMode ? `Edit Price Rule` : `Add New Price Rule`" :style="{ width: '60rem' }" block-scroll maximizable modal position="bottom" @hide="onDialogHide" @show="onDialogShow">
            <div class="grid grid-cols-2 gap-1">
                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Condition
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <IconField>
                        <InputIcon class="pi pi-angle-right" />
                        <InputText v-model="priceRuleItem.condition" class="w-full" min="0" placeholder="0" step="1"/>
                    </IconField>
                </div>
                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Collection Method
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <Select v-model="priceRuleItem.collected" :options="collectionMethods" option-value="value" optionLabel="name" placeholder="Select a Collection Method" class="w-full" />
                </div>
                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Quantity Basis (Per)
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <Select
                        v-model="priceRuleItem.package_type"
                        :options="filteredPackageType()"
                        optionLabel="name"
                        option-value="name"
                        placeholder="Select a Quantity Basis"
                        class="w-full"
                    />
                </div>
                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Charge
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber v-model="priceRuleItem.charge" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0" placeholder="1" step="1"/>
                </div>
            </div>
            <template #footer>
              <Button label="Cancel" severity="secondary" text @click="closeAddPriceRuleModal" />
              <Button :label="editMode ? `Edit DO Charge` : `Add DO Charge`" severity="help" @click="addPriceRuleData" />
            </template>
        </Dialog>
    </AppLayout>
</template>
