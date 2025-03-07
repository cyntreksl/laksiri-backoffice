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
import InputLabel from "@/Components/InputLabel.vue";

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
    packageTypes: {
        type: Array,
        default: () => [],
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },
    courierAgents: {
        type: Object,
        default: () => {
        },
    },
});

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
    cargo_type: "",
    hbl_type: "",
    courier_agent: null,
    name: "",
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
    packages: {},
    is_active_package: false,
});

const resetCreateForm = () => {
    packageList.value = [];
    countryCode.value = findCountryCodeByBranch(currentBranch);
    consignee_countryCode.value = '+94';
    contactNumber.value = "";
     consignee_contact.value = "";
}

const handleCourierCreate = () => {
    if(Object.keys(form.packages).length <= 0){
        push.error("Please add at least one package.");
        return;
    }else{
        form.post(route("couriers.store"), {
            onSuccess: (page) => {
                form.reset();
                resetCreateForm();
                push.success("Courier Created Successfully!");
            },
            onError: () => console.log("error"),
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const showAddNewPackageDialog = ref(false);
const editMode = ref(false);
const showPackageDialog = () => {
    showAddNewPackageDialog.value = true;
    if (!editMode.value) {
        selectedType.value = "";
    }
};

const packageList = ref([]);

const packageItem = reactive({
    type: props.packageTypes.find(
        type => type.name.toLowerCase() === 'carton'.toLowerCase()
    )?.name || "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 1,
    volume: 0,
    totalWeight: 0,
    remarks: "",
    measure_type: "cm",
});

const grandTotalWeight = ref(0);
const grandTotalVolume = ref(0);

const addPackageData = () => {
    if (
        !packageItem.type ||
        packageItem.length <= 0 ||
        packageItem.width <= 0 ||
        packageItem.height <= 0 ||
        packageItem.quantity <= 0 ||
        packageItem.volume <= 0
    ) {
        push.error("Please fill all required data");
        return;
    }
    packageItem.length = packageItemLength.value;
    packageItem.width = packageItemWidth.value;
    packageItem.height = packageItemHeight.value;
    packageItem.volume = packageItemVolume.value;


    if (editMode.value) {
        packageList.value.splice(editIndex.value, 1, {...packageItem});
        grandTotalWeight.value = packageList.value.reduce(
            (accumulator, currentValue) => accumulator + parseFloat(currentValue.totalWeight),
            0
        );
        grandTotalVolume.value = packageList.value.reduce(
            (accumulator, currentValue) => accumulator + parseFloat(currentValue.volume),
            0
        );
    } else {
        const newItem = {...packageItem}; // Create a copy of packageItem
        packageList.value.push(newItem); // Add the new item to packageList
        form.packages = packageList.value;

        const volume = parseFloat(newItem.volume) || 0;
        grandTotalWeight.value += parseFloat(newItem.totalWeight);
        grandTotalVolume.value += parseFloat(volume.toFixed(3));
    }
    closeAddPackageModal();
};

const packageItemVolume = ref(0);

// Watch for changes in length, width, height, or quantity to update volume and totalWeight
watch(
    [
        () => packageItem.length,
        () => packageItem.width,
        () => packageItem.height,
        () => packageItem.quantity,
        () => packageItem.measure_type,
    ],
    ([newLength, newWidth, newHeight, newQuantity, newMeasureType]) => {
        // Convert dimensions from cm to meters
        const lengthMeters = newLength / 100; // 1 cm = 0.01 meters
        const widthMeters = newWidth / 100;
        const heightMeters = newHeight / 100;

        // Calculate volume in cubic meters (m³)
        const volumeCubicMeters =
            lengthMeters * widthMeters * heightMeters * newQuantity;

        // Assuming weight is directly proportional to volume
        // Convert weight from grams to kilograms
        const totalWeightKg = (volumeCubicMeters * newQuantity) / 1000; // 1 gram = 0.001 kilograms

        // Update reactive properties
        packageItem.volume = (newLength*newWidth*newHeight*newQuantity).toFixed(3);
        if (packageItem.measure_type === 'cm') {
            // Convert cm³ to m³ by dividing by 1,000,000
            packageItemVolume.value = (packageItem.volume / 1000000).toFixed(3);
        } else if (packageItem.measure_type === 'in') {
            // Convert from inches to cubic centimeters (1 inch = 16.387 cm³)
            packageItemVolume.value = (packageItem.volume * 16.387 / 1000000).toFixed(3);  // Convert to m³
        } else if (packageItem.measure_type === 'ft') {
            // Convert from cubic feet to cubic meters (1 ft³ = 0.0283 m³)
            packageItemVolume.value = (packageItem.volume * 0.0283).toFixed(3);
        } else {
            // Assume volume is already in cubic meters if no unit conversion is needed
            packageItemVolume.value = packageItem.volume;
        }

        // packageItem.totalWeight = totalWeightKg;
    }
);

const vat = ref(0);

const selectedType = ref("");

const isChecked = ref(false);

const resetConsigneeDetails = () => {
    form.consignee_name = "";
    consignee_contact.value = "";
    form.consignee_nic = "";
    form.consignee_address = "";
};

const updateTypeDescription = () => {
    packageItem.type = (packageItem.type ? " " : "") + selectedType.value;
};
const currency = ref(usePage().props.currentBranch.currency_symbol || "SAR");

const showConfirmRemovePackageModal = ref(false);
const packageIndex = ref(null);

// remove package
const confirmRemovePackage = (index) => {
    packageIndex.value = index;
    showConfirmRemovePackageModal.value = true;
};

const closeModal = () => {
    showConfirmRemovePackageModal.value = false;
};

const closeViewModal = () => {
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
    router.visit(route("hbls.create"));
};

const handleRemovePackage = () => {
    if (packageIndex.value !== null) {
        grandTotalVolume.value -= packageList.value[packageIndex.value].volume;
        grandTotalWeight.value -= packageList.value[packageIndex.value].totalWeight;
        packageList.value.splice(packageIndex.value, 1);
        closeModal();
    }
};

// edit package
const closeAddPackageModal = () => {
    showAddNewPackageDialog.value = false;
    editIndex.value = null;
    editMode.value = false;
    restModalFields();
};

const restModalFields = () => {
    packageItem.type = props.packageTypes.find(
        type => type.name.toLowerCase() === 'carton'.toLowerCase()
    )?.name || "";
    packageItem.length = 0;
    packageItem.width = 0;
    packageItem.height = 0;
    packageItem.quantity = 1;
    packageItem.volume = 0;
    packageItem.totalWeight = 0;
    packageItem.remarks = "";
    packageItem.packageRule = 0;
};

const editIndex = ref(null);

const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPackageDialog.value = true;
    // populate packageItem with existing data for editing
    Object.assign(packageItem, packageList.value[index]);
    const factor = conversionFactors[packageItem.measure_type] || 1;
    packageItem.length = packageItem.length/factor;
    packageItem.width = packageItem.width/factor;
    packageItem.height = packageItem.height/factor;
};

const copyFromHBLToShipperModalShow = ref(false);

const reference = ref(null);

const volumeMeasurements = {
    cm: 'cm.cu',
    m: 'm.cu',
    in: 'in.cu',
    ft: 'ft.cu',
};

const handleCopyShipper = () => {
    form.consignee_name = form.name;
    form.consignee_nic = form.nic;
}

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
0
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

const getSelectedPackage = () => {
    // Find the selected package from the packages array based on the selected ID
    const selectedRule = packageRulesData.value.find(pkg => pkg.id === packageItem.packageRule);
    if (selectedRule) {
        isPackageRuleSelected.value = true;
        packageItem.length = convertMeasurements(selectedRule.measure_type, selectedRule.length).toFixed(2);
        packageItem.width = convertMeasurements(selectedRule.measure_type, selectedRule.width).toFixed(2);
        packageItem.height = convertMeasurements(selectedRule.measure_type, selectedRule.height).toFixed(2);
        packageItem.measure_type = selectedRule.measure_type;
    }else {
        isPackageRuleSelected.value = false;
        packageItem.length = 0;
        packageItem.width = 0;
        packageItem.height = 0;
    }
};

const packageItemLength = ref(0);
const packageItemWidth = ref(0);
const packageItemHeight = ref(0);

const conversionFactors = {
    cm: 1,
    m: 100,
    in: 2.54,
    ft: 30.48,
};

function convertMeasurements(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return value / factor;
}

function convertMeasurementstocm(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return value * factor;
}

watch(
    () => packageItem.measure_type,
    (newMeasureType) => {
        packageItemLength.value = convertMeasurementstocm(newMeasureType, packageItem.length);
        packageItemWidth.value = convertMeasurementstocm(newMeasureType, packageItem.width);
        packageItemHeight.value = convertMeasurementstocm(newMeasureType, packageItem.height);
    }
);

watch(
    [() => packageItem.length],
    ([newLength]) => {
        packageItemLength.value = convertMeasurementstocm(packageItem.measure_type, newLength);
    }
);

watch(
    [() => packageItem.width],
    ([newWidth]) => {
        packageItemWidth.value = convertMeasurementstocm(packageItem.measure_type, newWidth);
    }
);

watch(
    [() => packageItem.height],
    ([newHeight]) => {
        packageItemHeight.value = convertMeasurementstocm(packageItem.measure_type, newHeight);
    }
);

const volumeUnit = computed(() => {
    const units = {
        cm: 'CM.CU',
        m: 'M.CU',
        in: 'IN.CU',
        ft: 'FT.CU',
    };
    return units[packageItem.measure_type] || 'M.CM';
});

const hblId = ref(null);
const showConfirmViewHBLModal = ref(false);

const confirmViewHBL = async (id) => {
    hblId.value = id;
    showConfirmViewHBLModal.value = true;
};
</script>

<template>
    <AppLayout title="Courier Create">
        <template #header>Courier Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handleCourierCreate">
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
                                Cargo Type
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
                        <div>
                            <h2
                                class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-0"
                            >
                                Type
                            </h2>
                        </div>
                        <div class="my-5">
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
                                    />
                                    <p>{{ hblType }}</p>
                                </label>
                            </div>
                            <InputError :message="form.errors.hbl_type"/>
                        </div>

                        <hr class="my-4 border-t border-slate-200 dark:border-navy-600">

                        <div>
                            <h2
                                class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-5"
                            >
                                Courier Agent
                            </h2>
                        </div>

                        <div class="my-5">
                            <div class="space-x-5">
                                <select
                                    v-model="form.courier_agent"
                                    class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option :value="null" disabled>Select Courier Agent</option>
                                    <option
                                        v-for="courierAgent in courierAgents"
                                        :key="courierAgent"
                                        :value="courierAgent.id"
                                    >
                                        {{ courierAgent.company_name }}
                                    </option>
                                </select>
                            </div>
                            <InputError :message="form.errors.courier_agent"/>
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

                        </div>

                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <span>Name</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.name"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Name"
                                        type="text"
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
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <InputError :message="form.errors.name"/>
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
                                <div class="grid grid-cols-1 sm:grid-cols-3">
                                    <InputLabel class="col-span-3" value="Mobile Number"/>
                                    <div>
                                        <select
                                            v-model="countryCode"
                                            x-init="$el._tom = new Tom($el)"
                                            class="w-full rounded-r-0"
                                        >
                                            <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                                {{ countryCode }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input
                                            id="telephone"
                                            v-model="contactNumber"
                                            class="rounded-l-lg form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                            placeholder="123 4567 890"
                                            type="text"
                                        />
                                    </div>
                                </div>
                                <InputError class="col-span-3" :message="form.errors.contact_number"/>
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
                                <a  v-if="form.name"  @click.prevent="handleCopyShipper"
                                    x-tooltip.placement.bottom="'Copy Shippier'"
                                    class="relative inline-flex items-center">
                                    <svg class="icon icon-tabler icons-tabler-outline icon-tabler-copy mr-2" fill="none"
                                         height="24" stroke="currentColor" stroke-linecap="round"
                                         stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                        <path
                                            d="M7 7m0 2.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z"/>
                                        <path
                                            d="M4.012 16.737a2.005 2.005 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <span>Name</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.consignee_name"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Name"
                                        type="text"
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
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>
                                </label>
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
                                <div class="grid grid-cols-1 sm:grid-cols-3">
                                    <InputLabel class="col-span-3" value="Mobile Number"/>
                                    <div>
                                        <select
                                            v-model="consignee_countryCode"
                                            x-init="$el._tom = new Tom($el)"
                                            class="w-full rounded-r-0"
                                        >
                                            <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                                {{ countryCode }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input
                                            id="telephone"
                                            v-model="consignee_contact"
                                            class="rounded-l-lg form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                            placeholder="123 4567 890"
                                            type="text"
                                        />
                                    </div>
                                </div>
                                <InputError class="col-span-3" :message="form.errors.consignee_contact"/>
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
                            <PrimaryOutlineButton type="button"
                                                  @click="showPackageDialog">
                                New Package <i class="fas fa-plus fa-fw fa-fw"></i>
                            </PrimaryOutlineButton>
                        </div>

                        <div class="mt-5">
                            <div
                                v-if="packageList.length > 0"
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
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5 space-x-2">
                                            <button
                                                class="btn size-9 p-0 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                                                @click.prevent="confirmRemovePackage(index)"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>

                                            <button
                                                class="btn size-9 p-0 font-medium text-success hover:bg-success/20 focus:bg-success/20 active:bg-success/25"
                                                @click.prevent="openEditModal(index)"
                                            >
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                        </td>
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
                            <div v-if="packageList.length === 0"
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
                                <PrimaryOutlineButton type="button" @click="showPackageDialog">
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
                                    Summary
                                </h2>
                            </div>
                            <div class="grid grid-cols-2 gap-5 mt-5">
                                <div class="mt-5 space-y-2.5 font-bold">
                                    <div class="flex justify-between">
                                        <p class="line-clamp-1">Packages</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ packageList.length }}
                                        </p>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="line-clamp-1">Weight</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ grandTotalWeight.toFixed(2) }}
                                        </p>
                                    </div>
                                    <div class="flex justify-between">
                                        <p class="line-clamp-1">Volume</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ grandTotalVolume.toFixed(2) }}
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
                    <DangerOutlineButton @click="router.visit(route('couriers.index'))">
                        Cancel
                    </DangerOutlineButton>
                    <PrimaryButton
                        :class="{ 'opacity-50': form.processing }"
                        class="space-x-2"
                        type="submit"
                    >
                        <span>Create a Courier</span>
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

        <div
            v-if="showAddNewPackageDialog"
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
                        {{ editMode ? "Edit Package" : "Add New Package" }}
                    </h3>
                    <button
                        class="btn -mr-1.5 size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        @click="closeAddPackageModal"
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
                            <div class="col-span-4 md:col-span-1">
                                <label class="block">
                                    <span>Type </span>
                                    <select
                                        v-model="selectedType"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                        @change="updateTypeDescription"
                                    >
                                        <option value="">Choose one</option>
                                        <option v-for="type in packageTypes" :key="type.name">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="block">
                                  <span
                                  >Type Description
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="packageItem.type"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Sofa set"
                                        type="text"
                                    />
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-1">
                                <label class="block">
                                  <span
                                  >Measure Type <span class="text-red-500 text-sm"
                                  >*<br/></span
                                  ></span>
                                    <select
                                        v-model="packageItem.measure_type"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
                                        <option value="in">in</option>
                                        <option value="ft">ft</option>
                                    </select>
                                </label>
                            </div>

                            <div class="col-span-4 md:col-span-1">
                                <label class="block">
                                  <span
                                  >Length (cm) <br/>
                                    <span class="text-red-500 text-sm">*</span></span
                                  >
                                    <input
                                        v-model="packageItem.length"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0.00"
                                        placeholder="1.00"
                                        step="0.01"
                                        type="number"
                                    />
                                    <span class="ml-2 text-red-500 text-sm">{{packageItemLength.toFixed(2)}} cm</span>
                                </label>
                            </div>
                            <div class="col-span-4 md:col-span-1">
                                <label class="block">
                                  <span
                                  >Width <br/><span class="text-red-500 text-sm">*</span>
                                  </span>

                                    <input
                                        v-model="packageItem.width"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0.00"
                                        placeholder="1.00"
                                        step="0.01"
                                        type="number"
                                    />
                                    <span class="ml-2 text-red-500 text-sm">{{packageItemWidth.toFixed(2)}} cm</span>
                                </label>
                            </div>

                            <div class="col-span-4 md:col-span-1">
                                <label class="block">
                  <span
                  >Height <br/><span class="text-red-500 text-sm"
                  >*<br/></span
                  ></span>
                                    <input
                                        v-model="packageItem.height"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0.00"
                                        placeholder="1.00"
                                        step="0.01"
                                        type="number"
                                    />
                                    <span class="ml-2 text-red-500 text-sm">{{packageItemHeight.toFixed(2)}} cm</span>
                                </label>
                            </div>

                            <!--                            <div class="col-span-4 md:col-span-1">-->
                            <!--                                -->
                            <!--                            </div>-->

                            <div class="col-span-4 md:col-span-1">
                                <label class="block">
                  <span
                  >Quantity <br/><span class="text-red-500 text-sm"
                  >*<br/></span
                  ></span>
                                    <input
                                        v-model="packageItem.quantity"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0"
                                        placeholder="1"
                                        step="1"
                                        type="number"
                                    />
                                </label>
                            </div>

                            <div class="col-span-4 md:col-span-3"></div>

                            <div class="col-span-2">
                                <label class="block">
                  <span
                  >Volume ({{volumeUnit }})
                    <span class="text-red-500 text-sm">*</span></span
                  >
                                    <input
                                        v-model="packageItem.volume"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="1.00"
                                        step="0.001"
                                        type="number"
                                    />
                                    <span class="ml-2 text-red-500 text-sm">{{packageItemVolume}} M.CU</span>
                                </label>
                            </div>
                            <div class="col-span-2">
                                <label class="block">
                                    <span>Total Weight</span>
                                    <input
                                        v-model="packageItem.totalWeight"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        min="0"
                                        placeholder="1"
                                        step="1"
                                        type="number"
                                    />
                                </label>
                            </div>

                            <div class="col-span-4">
                                <label class="block">
                                    <span>Remarks</span>
                                    <textarea
                                        v-model="packageItem.remarks"
                                        class="form-textarea mt-1.5 w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Enter Text"
                                        rows="4"
                                    ></textarea>
                                </label>
                            </div>
                        </div>

                        <div class="space-x-2 text-right">
                            <SecondaryButton
                                class="min-w-[7rem]"
                                @click="closeAddPackageModal"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                class="min-w-[7rem]"
                                type="button"
                                @click="addPackageData"
                            >
                                {{ editMode ? "Edit" : "Add" }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <RemovePackageConfirmationModal
            :show="showConfirmRemovePackageModal"
            @close="closeModal"
            @remove-package="handleRemovePackage"
        />

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeViewModal"
        />
    </AppLayout>
</template>
