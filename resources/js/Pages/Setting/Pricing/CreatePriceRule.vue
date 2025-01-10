<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {router, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import {push} from "notivue";
import Checkbox from "@/Components/Checkbox.vue";
import RadioButton from "@/Components/RadioButton.vue";
import {reactive, ref, watch} from "vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import RemovePackageConfirmationModal from "@/Pages/HBL/Partials/RemovePackageConfirmationModal.vue";
import RemovePriceRuleConfirmationModal from "@/Pages/Setting/Pricing/Partials/RemovePriceRuleConfirmationModal.vue";

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

const form = useForm({
    destination_branch_id: null,
    cargo_mode: '',
    hbl_type: '',
    price_mode: '',
    priceRules: {},
});

const ruleList = ref([]);

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

const priceModes = ['Weight', 'Volume'];
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
const showAddNewPriceRuleDialog = ref(false);
const editMode = ref(false);
const showAddPriceRuleDialog = () => {
    showAddNewPriceRuleDialog.value = true;
};

const closeAddPriceRuleModal = () => {
    showAddNewPriceRuleDialog.value = false;
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
        priceRuleItem.bill_vat <= 0 ||
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

</script>

<template>
    <AppLayout title="Create Price Rule">
        <template #header>Pricing</template>

        <Breadcrumb/>
        <form @submit.prevent="handlePriceRuleCreate">

            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Create Price Rule
                </h2>

                <div class="flex justify-end bottom-0 space-x-5">
                    <DangerOutlineButton @click="router.visit(route('setting.prices.index'))">Cancel</DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                   class="space-x-2"
                                   type="submit"
                    >
                        <span>Create Price Rule</span>
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

                        <div class="ml--5 flex flex-nowrap items-start p-4 my-2 rounded bg-white border border-indigo-400 overflow-auto">
                            <div class="bg-green-100 p-5 rounded-lg w-1/4">
                                <InputLabel value="Cargo Mode" class="mb-2" />
                                <div class="flex space-x-4">
                                    <label
                                        v-for="cargoType in cargoModes"
                                        :key="cargoType"
                                        class="flex space-x-2 items-center"
                                    >
                                        <RadioButton
                                            v-model="form.cargo_mode"
                                            :label="cargoType"
                                            name="cargoType"
                                            :value="cargoType"
                                        />
                                        <svg
                                            v-if="cargoType === 'Air Cargo'"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-plane"
                                            fill="none"
                                            height="15"
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                            width="15"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none" />
                                            <path
                                                d="M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-ship mr-2"
                                            fill="none"
                                            height="15"
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                            width="15"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none" />
                                            <path
                                                d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1"
                                            />
                                            <path d="M4 18l-1 -5h18l-2 4" />
                                            <path d="M5 13v-6h8l4 6" />
                                            <path d="M7 7v-4h-1" />
                                        </svg>
                                    </label>
                                </div>
                                <InputError :message="form.errors.cargo_mode"/>
                            </div>

                            <div class="bg-blue-100 p-5 rounded-lg w-1/4 ml-2">
                                <InputLabel value="HBL Type" class="mb-2" />
                                <div class="flex space-x-4">
                                    <label
                                        v-for="hblType in hblTypes"
                                        :key="hblType"
                                        class="flex space-x-2 items-center"
                                    >
                                        <RadioButton
                                            v-model="form.hbl_type"
                                            :label="hblType"
                                            name="hblType"
                                            :value="hblType"
                                        />
                                    </label>
                                </div>
                                <InputError :message="form.errors.hbl_type"/>
                            </div>

                            <div class="bg-amber-100 p-5 rounded-lg w-1/4 ml-2">
                                <InputLabel value="Branch" class="mb-2" />
                                <div class="flex space-x-4">
                                    <label
                                        v-for="branch in branches"
                                        :key="branch"
                                        class="flex space-x-2 items-center"
                                    >
                                        <RadioButton
                                            v-model="form.destination_branch_id"
                                            :label="branch.name"
                                            name="warehouse"
                                            :value="branch.id"
                                        />
                                    </label>
                                </div>
                                <InputError :message="form.errors.destination_branch_id"/>
                            </div>

                            <div class="bg-green-100 p-5 rounded-lg w-1/4 ml-2">
                                <InputLabel value="Price Mode" class="mb-2" />
                                <div class="flex space-x-4">
                                    <label
                                        v-for="price_mode in priceModes"
                                        :key="price_mode"
                                        class="flex space-x-2 items-center"
                                    >
                                        <RadioButton
                                            disabled
                                            v-model="form.price_mode"
                                            :label="price_mode"
                                            name="price_mode"
                                            :value="price_mode"
                                        />
                                    </label>
                                </div>
                                <InputError :message="form.errors.price_mode"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 my-4 gap-4">
                            <div class="sm:col-span-4">
                                <div class="card p-1" style="height: 100%">
                                    <div class="mt-4 flex justify-between items-center">
                                        <div class="flex items-center space-x-2">
                                            <h2
                                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                                            >
                                                Price Rules
                                            </h2>
                                        </div>
                                        <PrimaryOutlineButton type="button"
                                                              @click="showAddPriceRuleDialog">
                                            New Price Rule <i class="fas fa-plus fa-fw fa-fw"></i>
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
                                                        True Action
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Bill Price
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Bill VAT (%)
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Volume Charges
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Per Package Charges
                                                    </th>
                                                    <th
                                                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                                    >
                                                        Editable
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
                                                            {{ rule.true_action }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                            {{ rule.bill_price }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                            {{ rule.bill_vat }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                            {{ rule.volume_charges }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                            {{ rule.per_package_charges }}
                                                        </td>
                                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                                            <template v-if="rule.is_editable">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-5 w-5 text-green-500"
                                                                    viewBox="0 0 20 20"
                                                                    fill="currentColor"
                                                                >
                                                                    <path
                                                                        fill-rule="evenodd"
                                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                        clip-rule="evenodd"
                                                                    />
                                                                </svg>
                                                            </template>
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
                                            <PrimaryOutlineButton type="button"
                                                                  @click="showAddPriceRuleDialog">
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

        <div
            v-if="showAddNewPriceRuleDialog"
            class="fixed px-2 inset-0 z-[100] flex flex-col items-center justify-center overflow-y-auto"
            role="dialog"
        >
            <div
                class="absolute inset-0 bg-slate-900/60 transition-opacity duration-300"
                x-show="true"
                @click="false"
            ></div>

            <div
                class="relative w-auto sm:w-1/2 h-auto sm:h-1/5 md:h-fit lg:h-fit rounded-lg bg-white transition-opacity duration-300 dark:bg-navy-700"
            >
                <div
                    class="flex justify-between rounded-t-lg bg-slate-200 px-4 py-3 dark:bg-navy-800 sm:px-5"
                >
                    <h3 class="text-base font-medium text-slate-700 dark:text-navy-100">
                        {{ editMode ? "Edit Package" : "Add New Price Rule" }}
                    </h3>
                    <button
                        class="btn -mr-1.5 size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        @click="closeAddPriceRuleModal"
                    >
                        <svg
                            class="size-4.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M6 18L18 6M6 6l12 12"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-4 sm:px-5">
                    <div class="mt-4 space-y-4">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Condition
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="priceRuleItem.condition"
                                        :disabled="ruleList.length <= 0"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Type Condition"
                                        type="text"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >True Action
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="priceRuleItem.true_action"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        type="text"
                                        placeholder="Set True Action"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Bill Price
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="priceRuleItem.bill_price"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0"
                                        placeholder="0.00"
                                        type="number"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Bill VAT (%)
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="priceRuleItem.bill_vat"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0"
                                        placeholder="0.00"
                                        step="any"
                                        type="number"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Volume Charge
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="priceRuleItem.volume_charges"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Set Volume Charges"
                                        type="text"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Per Package Charges
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="priceRuleItem.per_package_charges"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Set Per Package Charges"
                                        type="text"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Is Editable
                                  </span
                                  >
                                    <Checkbox class="ml-4" v-model="priceRuleItem.is_editable" :checked="priceRuleItem.is_editable"/>
                                </label>
                            </div>
                        </div>
                        <div class="space-x-2 text-right">
                            <SecondaryButton
                                class="min-w-[7rem]"
                                @click="closeAddPriceRuleModal"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                class="min-w-[7rem]"
                                type="button"
                                @click="addPriceRuleData"
                            >
                                {{ editMode ? "Edit" : "Add" }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <RemovePriceRuleConfirmationModal
            :show="showConfirmRemovePriceRuleModal"
            @close="closePriceRuleRemoveModal"
            @removePriceRule="handleRemovePriceRule"
        />


    </AppLayout>
</template>
