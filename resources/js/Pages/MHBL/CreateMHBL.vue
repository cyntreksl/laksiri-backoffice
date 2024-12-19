<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, reactive, ref, watch} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import RemovePackageConfirmationModal from "@/Pages/HBL/Partials/RemovePackageConfirmationModal.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import {push} from "notivue";
import DialogModal from "@/Components/DialogModal.vue";
import hblImage from "../../../../resources/images/illustrations/hblimage.png";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import {float} from "quill/ui/icons.js";

const props = defineProps({
    hblTypes: {
        type: Object,
        default: () => {
        },
    },
    cargoTypes: {
        type: Object,
        default: () => {
        },
    },
    warehouses: {
        type: Object,
        default: () => {
        },
    },
    selectedCargoType: {
        type: String,
        required: true,
    },
    selectedHblType: {
        type: String,
        required: true,
    },
    selectedWarehouse: {
        type: String,
        required: true,
    },
    priceRules: {
        type: Object,
        default: () => {
        },
    },
    packageTypes: {
        type: Array,
        default: () => [],
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },
    shippers: {
        type: Array,
        default: () => [],
    },
    consignees: {
        type: Array,
        default: () => [],
    },
    packages: {
        type: Array,
        default: () => [],
    },
    hblIds: {
        type: Array,
        default: () => [],
    },
    grandVolume: {
        type: float,
        required: true,
    },
    grandWeight: {
        type: float,
        required: true,
    },
    grandTotal: {
        type: float,
        required: true,
    },
});


const packageList = ref(props.packages);

//branch set
const currentBranch = usePage().props?.auth.user.active_branch_name;

const findCountryCodeByBranch = (country) => {
    return usePage().props.currentBranch.country_code;
};

const countryCode = ref(findCountryCodeByBranch(currentBranch));
const consignee_countryCode = ref('+94');
const contactNumber = ref("");
const consignee_contact = ref("");

const splitNumber = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            countryCode.value = code;
            contactNumber.value = fullNumber.slice(code.length);
            break;
        }
    }
}

const splitNumberConsignee = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            consignee_countryCode.value = code;
            consignee_contact.value = fullNumber.slice(code.length);
            break;
        }
    }
}

const form = useForm({
    hbls: props.hblIds,
    hbl_name: "",
    email: "",
    contact_number: computed(() => countryCode.value + contactNumber.value),
    nic: "",
    iq_number: "",
    address: "",
    consignee_name: "",
    consignee_nic: "",
    consignee_contact: computed(
        () => consignee_countryCode.value + consignee_contact.value
    ),
    consignee_address: "",
    consignee_note: "",
    cargo_type: props.selectedCargoType,
    hbl_type: props.selectedHblType,
    warehouse: props.selectedWarehouse,
    grand_volume: props.grandVolume,
    grand_weight: props.grandWeight,
    grand_total: props.grandTotal,
    packages: {},
    is_active_package: false,
    shipper_id: 0,
    consignee_id: 0,
});

const splitCountryCode = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            return code;
        }
    }
}

const splitContactNumber = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            return fullNumber.slice(code.length);
        }
    }
}

watch(
    [() => form.hbl_name],
    ([newShipper]) => {
        // Filter shipper based on form.hbl_name
        const filteredShipper = props.shippers.find(
            shipper => shipper.name.toLowerCase() === newShipper.toLowerCase()
        );
        form.shipper_id = filteredShipper['id'];
        form.email = filteredShipper['email'];
        form.nic = filteredShipper['pp_or_nic_no'];
        form.iq_number = filteredShipper['residency_no'];
        form.address = filteredShipper['address'];
        countryCode.value = splitCountryCode(filteredShipper['mobile_number'])
        contactNumber.value = splitContactNumber(filteredShipper['mobile_number'])
    }
);

watch(
    [() => form.consignee_name],
    ([newConsignee]) => {
        // Filter shipper based on form.hbl_name
        const filteredConsignee = props.consignees.find(
            consignee => consignee.name.toLowerCase() === newConsignee.toLowerCase()
        );
        form.consignee_id = filteredConsignee['id'];
        form.consignee_address = filteredConsignee['address'];
        form.consignee_nic = filteredConsignee['pp_or_nic_no'];
        consignee_countryCode.value = splitCountryCode(filteredConsignee['mobile_number'])
        consignee_contact.value = splitContactNumber(filteredConsignee['mobile_number'])
    }
);

const handleMHBLCreate = () => {
    form.packages = packageList.value;
    form.post(route("mhbls.store"), {
        onSuccess: (page) => {
            form.reset();
            push.success("MHBL Created Successfully!");
        },
        onError: () => console.log("error"),
        preserveScroll: true,
        preserveState: true,
    });
};

const showAddNewPackageDialog = ref(false);
const editMode = ref(false);
const showPackageDialog = () => {
    showAddNewPackageDialog.value = true;
    if (!editMode.value) {
        selectedType.value = "";
    }
};

const packageItem = reactive({
    id: 0,
    type: "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 1,
    volume: 0,
    totalWeight: 0,
    remarks: "",
    packageRule: 0,
    measure_type: "cm",
});

const grandTotalWeight = ref(0);
const grandTotalVolume = ref(0);


const resetConsigneeDetails = () => {
    form.consignee_name = "";
    consignee_contact.value = "";
    form.consignee_nic = "";
    form.consignee_address = "";
};

const copiedPackages = ref({});

const planeIcon = ref(`
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="24"
  height="24"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="icon icon-tabler icons-tabler-outline icon-tabler-plane"
>
  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
  <path d="M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z" />
</svg>
`);

const shipIcon = ref(`
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="24"
  height="24"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="icon icon-tabler icons-tabler-outline icon-tabler-ship"
>
  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
  <path d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1" />
  <path d="M4 18l-1 -5h18l-2 4" />
  <path d="M5 13v-6h8l4 6" />
  <path d="M7 7v-4h-1" />
</svg>
`);

</script>

<template>
    <AppLayout title="HBL Create">
        <template #header>HBL - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handleMHBLCreate">
            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-2 grid grid-rows gap-4">

                    <div class="card px-4 py-4 sm:px-5">
                        <!-- Primary Details -->
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Primary Details
                            </h2>
                        </div>

                        <!-- Cargo Type -->
                        <div>
                            <h2
                                class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-5"
                            >
                                Cargo Type <span class="text-xs text-gray-400">(Automatically Selected)</span>
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label
                                    v-for="cargoType in cargoTypes"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.cargo_type"
                                        :value="cargoType"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="cargo_type"
                                        type="radio"
                                        disabled
                                    />
                                    <p>{{ cargoType }}</p>
                                    <span v-if="cargoType == 'Sea Cargo'">
                    <div v-html="shipIcon"></div>
                  </span>
                                    <span v-if="cargoType == 'Air Cargo'">
                    <div v-html="planeIcon"></div>
                  </span>
                                </label>
                            </div>
                            <InputError :message="form.errors.cargo_type"/>
                        </div>

                        <hr class="my-4 border-t border-slate-200 dark:border-navy-600">

                        <!-- Type -->
                        <div hidden="true">
                            <h2
                                class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-0"
                            >
                                Type
                            </h2>
                        </div>
                        <div hidden="true" class="my-5">
                            <div class="space-x-5">
                                <label
                                    v-for="hblType in hblTypes"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.hbl_type"
                                        :value="hblType"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="hbl_type"
                                        type="radio"
                                        disabled
                                    />
                                    <p>{{ hblType }}</p>
                                </label>
                            </div>
                            <InputError :message="form.errors.hbl_type"/>
                        </div>

                        <hr hidden="true" class="my-4 border-t border-slate-200 dark:border-navy-600">

                        <!-- Warehouse -->
                        <div>
                            <h2
                                class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-0"
                            >
                                Warehouse <span class="text-xs text-gray-400">(Automatically Selected)</span>
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label
                                    v-for="warehouse in warehouses"
                                    :key="warehouse.id"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.warehouse"
                                        :value="warehouse.name"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="warehouse"
                                        type="radio"
                                        disabled
                                        @change="form.warehouse_id = warehouse.id"
                                    />
                                    <p>{{ warehouse.name }}</p>
                                </label>
                            </div>
                            <InputError :message="form.errors.warehouse"/>
                        </div>

                        <div class="flex justify-center">
                            <img :src="hblImage" class="mx-auto" style="width: 50%;">
                        </div>

                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="flex justify-between items-center">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Shipper Details
                            </h2>

                            <a @click.prevent="confirmShowingCopyFromHBLToShipperModal"
                               x-tooltip.placement.bottom="'Copy from HBL'">
                                <svg class="icon icon-paste text-[#64748b]" fill="none" stroke="#64748b"
                                     stroke-linecap="round"
                                     stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" height="24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <!-- Clipboard shape -->
                                    <path
                                        d="M9 3h6a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h1v-1a2 2 0 0 1 2 -2z"/>
                                    <!-- Horizontal line representing pasted content -->
                                    <path d="M9 7h6"/>
                                </svg>
                            </a>
                        </div>

                        <DialogModal :maxWidth="'xl'" :show="copyFromHBLToShipperModalShow"
                                     @close="closeCopyFromHBLToShipperModal">
                            <template #title>
                                Copy
                            </template>

                            <template #content>
                                <div class="mt-4">
                                    <TextInput
                                        v-model="reference"
                                        class="w-full"
                                        placeholder="Enter HBL Reference"
                                        required
                                        type="text"
                                    />
                                </div>
                            </template>

                            <template #footer>
                                <SecondaryButton @click="closeCopyFromHBLToShipperModal">
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton
                                    class="ms-3"
                                    @click.prevent="handleCopyFromHBLToShipper"
                                >
                                    Copy From HBL
                                </PrimaryButton>
                            </template>
                        </DialogModal>

                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>Name</span>
                                <select
                                    v-model="form.hbl_name"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option :value="null" disabled>Select shipper</option>
                                    <option
                                        v-for="shipper in shippers"
                                        :key="shipper"
                                        :value="shipper.name"
                                    >
                                        {{ shipper.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.hbl_name"/>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>Email</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.email"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Email"
                                        type="email"
                                    />
                                    <div
                                        class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                                    >
                                        <svg
                                            class="size-4.5 transition-colors duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <InputError :message="form.errors.email"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>Mobile Number</span>
                                <div class="flex -space-x-px">
                                    <select
                                        v-model="countryCode"
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                            {{ countryCode }}
                                        </option>
                                    </select>

                                    <input
                                        v-model="contactNumber"
                                        class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                        placeholder="123 4567 890"
                                        type="text"
                                    />
                                </div>
                                <InputError :message="form.errors.contact_number"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>PP or NIC No</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.nic"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="PP or NIC No"
                                        type="text"
                                    />
                                </label>
                                <InputError :message="form.errors.nic"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>Residency No</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.iq_number"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Residency No"
                                        type="text"
                                    />
                                </label>
                                <InputError :message="form.errors.iq_number"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>Address</span>
                                <label class="block">
                  <textarea
                      v-model="form.address"
                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      placeholder="Type address here..."
                      rows="4"
                  ></textarea>
                                </label>
                                <InputError :message="form.errors.address"/>
                            </div>
                        </div>
                        <div v-if="form.hbl_type === 'Door to Door'" class="col-span-2">
                            <Checkbox
                                v-model="isChecked"
                                @change="addToConsigneeDetails"
                            ></Checkbox>

                            <span class="ml-5">Same as Consignee Details</span>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 grid grid-rows">
                    <div class="card px-4 sm:px-5 p-4">
                        <div class="flex justify-between items-center">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Consignee Details
                            </h2>

                            <div class="flex space-x-1">
                                <a
                                    @click.prevent="confirmShowingCopyFromHBLToConsigneeModal"
                                    x-tooltip.placement.bottom="'Copy from HBL'"
                                >
                                    <svg class="icon icon-paste text-[#64748b] ml-1" fill="none" stroke="#64748b"
                                         stroke-linecap="round"
                                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" height="24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <!-- Clipboard shape -->
                                        <path
                                            d="M9 3h6a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h1v-1a2 2 0 0 1 2 -2z"/>
                                        <!-- Horizontal line representing pasted content -->
                                        <path d="M9 7h6"/>
                                    </svg>

                                </a>
                            </div>

                            <DialogModal :maxWidth="'xl'" :show="copyFromHBLToConsigneeModalShow"
                                         @close="closeCopyFromHBLToConsigneeModal">
                                <template #title>
                                    Copy
                                </template>

                                <template #content>
                                    <div class="mt-4">
                                        <TextInput
                                            v-model="reference"
                                            class="w-full"
                                            placeholder="Enter HBL Reference"
                                            required
                                            type="text"
                                        />
                                    </div>
                                </template>

                                <template #footer>
                                    <SecondaryButton @click="closeCopyFromHBLToConsigneeModal">
                                        Cancel
                                    </SecondaryButton>
                                    <PrimaryButton
                                        class="ms-3"
                                        @click.prevent="handleCopyFromHBLToConsignee"
                                    >
                                        Copy From HBL
                                    </PrimaryButton>
                                </template>
                            </DialogModal>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <span>Name</span>
                                <select
                                    v-model="form.consignee_name"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option :value="null" disabled>Select shipper</option>
                                    <option
                                        v-for="consignee in consignees"
                                        :key="consignee"
                                        :value="consignee.name"
                                    >
                                        {{ consignee.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.consignee_name"/>
                            </div>

                            <div class="col-span-2">
                                <span>PP or NIC No</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.consignee_nic"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="PP or NIC No"
                                        type="text"
                                    />
                                </label>
                                <InputError :message="form.errors.consignee_nic"/>
                            </div>

                            <div class="col-span-2">
                                <span>Mobile Number</span>
                                <div class="flex -space-x-px">
                                    <select
                                        v-model="consignee_countryCode"
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                            {{ countryCode }}
                                        </option>
                                    </select>

                                    <input
                                        v-model="consignee_contact"
                                        class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                        placeholder="123 4567 890"
                                        type="text"
                                    />
                                </div>
                                <InputError :message="form.errors.consignee_contact"/>
                            </div>

                            <div class="col-span-2">
                                <span>Address</span>
                                <label class="block">
                                  <textarea
                                      v-model="form.consignee_address"
                                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                      placeholder="Type address here..."
                                      rows="4"
                                  ></textarea>
                                </label>
                                <InputError :message="form.errors.consignee_address"/>
                            </div>

                            <div class="col-span-2">
                                <span>Note</span>
                                <label class="block">
                                  <textarea
                                      v-model="form.consignee_note"
                                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                      placeholder="Type note here..."
                                      rows="2"
                                  ></textarea>
                                </label>
                                <InputError :message="form.errors.consignee_note"/>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-4">
                    <div class="card p-1" style="height: 100%">
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <h2
                                    class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                                >
                                    Package Details
                                </h2>
                            </div>
                        </div>

                        <DialogModal :maxWidth="'xl'" :show="copyFromHBLToPackageModalShow"
                                     @close="closeCopyFromHBLToPackageModal">
                            <template #title>
                                Copy
                            </template>

                            <template #content>
                                <div class="mt-4">
                                    <TextInput
                                        v-model="reference"
                                        class="w-full"
                                        placeholder="Enter HBL Reference"
                                        required
                                        type="text"
                                    />
                                </div>
                            </template>

                            <template #footer>
                            </template>
                        </DialogModal>

                        <div class="mt-5">
                            <div
                                v-if="packageList.length > 0"
                                class="is-scrollbar-hidden min-w-full overflow-x-auto"
                            >
                                <table class="is-zebra w-full text-left">
                                    <thead>
                                    <tr>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Length (CM)
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Width
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Height
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Weight
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Volume (M.CU)
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Remark
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in packageList">

                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.type }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.length.toFixed(3) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.width.toFixed(3) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.height.toFixed(3) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.totalWeight.toFixed(3) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.volume }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.remarks }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                v-if="Object.keys(copiedPackages).length > 0"
                                class="is-scrollbar-hidden min-w-full overflow-x-auto"
                            >
                                <table class="is-zebra w-full text-left">
                                    <thead>
                                    <tr>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Length (CM)
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Width
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Height
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Quantity
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Weight
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Volume (M.CU)
                                        </th>
                                        <th
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            Remark
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(item, index) in copiedPackages">
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.package_type }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.length }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.width }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.height }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.weight.toFixed(3) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.volume.toFixed(3) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.remarks }}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-if="packageList.length === 0 && Object.values(copiedPackages).length === 0"
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
                                        No packages. Please add packages to view data.
                                    </p>
                                </div>
                                <PrimaryOutlineButton type="button" @click="showPackageDialog" :disabled="!isExistsRules">
                                    New Package <i class="fas fa-plus fa-fw fa-fw"></i>
                                </PrimaryOutlineButton>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="sm:col-span-2 grid-cols-2 grid gap-4 space-y-5">
                    <!-- Price & Payment -->
                    <div class="sm:col-span-2 space-y-5">
                        <div class="card px-4 sm:px-5 p-5">
                            <div class="flex justify-between items-center">
                                <h2
                                    class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                                >
                                    MHBL Summary
                                </h2>
                            </div>

                            <div class="mt-5">
                                <div class="col-start-2 mt-5 space-y-2.5 font-bold">
                                    <div class="flex justify-between">
                                        <p class="line-clamp-1">Packages</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ packageList.length }}
                                        </p>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="line-clamp-1">Weight</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ form.grand_weight.toFixed(2) }}
                                        </p>
                                    </div>
                                    <div class="flex justify-between mb-20">
                                        <p class="line-clamp-1">Volume</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ form.grand_volume.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-6 my-6 gap-4">
                <!-- Empty grid columns for spacing -->
                <div class="col-span-4"></div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-5 col-span-2">
                    <DangerOutlineButton @click="router.visit(route('hbls.index'))">
                        Cancel
                    </DangerOutlineButton>
                    <PrimaryButton
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                        class="space-x-2"
                        type="submit"
                    >
                        <span>Create a MHBL</span>
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

        </form>
    </AppLayout>
</template>
