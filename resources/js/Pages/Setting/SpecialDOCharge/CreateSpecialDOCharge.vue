<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {push} from "notivue";
import {reactive, ref, watch} from "vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import RemovePriceRuleConfirmationModal from "@/Pages/Setting/Pricing/Partials/RemovePriceRuleConfirmationModal.vue";
import Card from "primevue/card";
import SelectButton from "primevue/selectbutton";
import Fieldset from "primevue/fieldset";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputNumber from 'primevue/inputnumber';
import Button from "primevue/button";

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

const form = useForm({
    agent_id: null,
    cargo_type: '',
    hbl_type: '',
    price_mode: '',
    priceRules: {},
});

const ruleList = ref([]);

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
        push.error('Please add at least one price rule.');
        return;
    }else{
        form.priceRules = ruleList.value;

        form.post(route("setting.special-do-charges.store"), {
            onSuccess: () => {
                form.reset();
                router.visit(route("setting.special-do-charges.index"));
                push.success('DO Charge created successfully!');
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

const showAddNewPriceRuleDialog = ref(false);
const editMode = ref(false);

const filteredPackageType = () => {
    return props.packageTypes.filter(pkg => pkg.branch_id === form.agent_id);
};
const showAddPriceRuleDialog = () => {
    showAddNewPriceRuleDialog.value = true;
};

const closeAddPriceRuleModal = () => {
    showAddNewPriceRuleDialog.value = false;
    restModalFields()
};



const showConfirmRemovePriceRuleModal = ref(false);
const ruleIndex = ref(null);
const confirmRemovePriceRule = (index) => {
    ruleIndex.value = index;
    showConfirmRemovePriceRuleModal.value = true;
};

const closePriceRuleRemoveModal = () => {
    showConfirmRemovePriceRuleModal.value = false;
};

const handleRemovePriceRule = () => {
    ruleList.value.splice(ruleIndex.value,1);
    closePriceRuleRemoveModal();
};

const editIndex = ref(null);
const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPriceRuleDialog.value = true;
    Object.assign(priceRuleItem, ruleList.value[index]);
};

const restModalFields = () => {
    priceRuleItem.condition = 0;
    priceRuleItem.collected = '';
    priceRuleItem.package_type = '';
    priceRuleItem.charge = 0;
};


const addPriceRuleData = () => {
  console.log(priceRuleItem);
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
}

</script>

<template>
    <AppLayout title="Create DO Charge">
        <template #header>Create DO Charge</template>

        <Breadcrumb/>
        <form @submit.prevent="handleDOChargeCreate">

            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                  Create DO Charge
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('special-do-charges'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                   class="space-x-2"
                                   type="submit"
                    >
                        <span>Create DO Charge</span>
                        <svg
                            class="size-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </PrimaryButton>
                </div>
            </div>

            <div class="grid grid-cols-1 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Basic Details
                            </h2>
                        </div>

                        <div class="ml--5 items-start p-4 my-2 rounded bg-white border border-indigo-400 overflow-auto grid grid-cols-1 sm:grid-cols-2">
                            <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
                                <Card>
                                    <template #content>
                                        <Fieldset legend="Type">
                                            <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type" />
                                            <InputError :message="form.errors.hbl_type"/>
                                        </Fieldset>
                                    </template>
                                </Card>
                                <Card>
                                    <template #content>
                                        <Fieldset legend="Cargo Type">
                                            <SelectButton v-model="form.cargo_type" :options="cargoModes" name="Cargo Type">
                                                <template #option="slotProps">
                                                    <div class="flex items-center">
                                                        <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                                        <i v-else class="ti ti-plane mr-2"></i>
                                                        <span>{{ slotProps.option }}</span>
                                                    </div>
                                                </template>
                                            </SelectButton>
                                            <InputError :message="form.errors.cargo_type"/>
                                        </Fieldset>
                                    </template>
                                </Card>
                                <Card class="col-span-2">
                                    <template #content>
                                        <Fieldset legend="Agent">
                                            <SelectButton v-model="form.agent_id" :options="branches" name="Agent" option-label="name" option-value="id"/>
                                            <InputError :message="form.errors.agent_id" />
                                        </Fieldset>
                                    </template>
                                </Card>
                            </div>
                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-4 my-4 gap-4">
                            <div class="sm:col-span-4">
                                <div class="p-1" style="height: 100%">
                                    <div class="mt-4 flex justify-between items-center">
                                        <div class="flex items-center space-x-2">
                                            <h2
                                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                                            >
                                                DO Charges
                                            </h2>
                                        </div>
                                        <PrimaryOutlineButton
                                            type="button"
                                            @click="showAddPriceRuleDialog"
                                            :disabled="form.agent_id === null && form.cargo_type === '' && form.hbl_type === ''"
                                        >
                                            New Do Charge <i class="fas fa-plus fa-fw fa-fw"></i>
                                        </PrimaryOutlineButton>
                                    </div>

                                    <div class="mt-5">
                                        <div
                                            v-if="ruleList.length > 0"
                                            class="is-scrollbar-hidden min-w-full overflow-x-auto"
                                        >
                                            <table class="is-zebra w-full text-left">
                                                <thead>
                                                <tr>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-center"
                                                    >
                                                        <span class="hidden">Actions</span>
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Condition
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Collected
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Quantity Basis
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                      Charge
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(rule, index) in ruleList">
                                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5 space-x-2">
                                                        <button
                                                            class="btn size-9 p-0 font-medium text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25"
                                                            @click.prevent="openEditModal(index)"
                                                        >
                                                            <i class="fa-solid fa-edit"></i>
                                                        </button>
                                                        <button
                                                            v-if="rule.condition !== '>0'"
                                                            class="btn size-9 p-0 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                                                            @click.prevent="confirmRemovePriceRule(index)"
                                                        >
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                        {{ rule.condition }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                        {{ rule.collected }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                        {{ rule.package_type }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                        {{rule.charge}}
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div v-if="ruleList.length === 0"
                                             class="text-center">
                                            <div class="text-center mb-8">
                                                <svg
                                                    class="w-24 h-24 mx-auto mb-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        d="M12 9l-2 2-2-2m4 2h4a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8a2 2 0 012-2h4m4-2l2 2 2-2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                    ></path>
                                                </svg>
                                                <p class="text-gray-600">
                                                    No price rules. Please add price rules to view data.
                                                </p>
                                            </div>
                                            <PrimaryOutlineButton
                                                type="button"
                                                @click="showAddPriceRuleDialog"
                                                :disabled="form.agent_id ==null && form.cargo_type === '' && form.hbl_type === ''"
                                            >
                                                New Price Rule <i class="fas fa-plus fa-fw fa-fw"></i>
                                            </PrimaryOutlineButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <Dialog v-model:visible="showAddNewPriceRuleDialog" :header="editMode ? `Edit Price Rule` : `Add New Price Rule`" :style="{ width: '60rem' }" block-scroll maximizable modal position="bottom" @hide="onDialogHide" @show="onDialogShow">
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
                        placeholder="Select a Collection Method"
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



        <RemovePriceRuleConfirmationModal
            :show="showConfirmRemovePriceRuleModal"
            @close="closePriceRuleRemoveModal"
            @removePriceRule="handleRemovePriceRule"
        />


    </AppLayout>
</template>
