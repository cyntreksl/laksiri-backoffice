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
import SoftPrimaryButton from "@/Components/SoftPrimaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import hblImage from "../../../../resources/images/illustrations/hblimage.png";

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
    hbl: "",
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
    cargo_type: "",
    hbl_type: "",
    warehouse: "",
    warehouse_id: "",
    freight_charge: 0,
    bill_charge: 0,
    other_charge: 0,
    destination_charges: 0,
    package_charges: 0,
    discount: 0,
    paid_amount: '',
    additional_charge: 0,
    grand_total: 0,
    packages: {},
    is_active_package: false,
});

const handleHBLCreate = () => {
    form.post(route("hbls.store"), {
        onSuccess: () => {
            router.visit(route("hbls.create"));
            form.reset();
            push.success("HBL Created Successfully!");
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
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

const packageList = ref([]);

const packageItem = reactive({
    type: "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 1,
    volume: 0,
    totalWeight: 0,
    remarks: "",
    packageRule: 0,
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
        packageItem.volume <= 0 ||
        (form.is_active_package && !packageItem.packageRule)
    ) {
        push.error("Please fill all required data");
        return;
    }

    if (form.cargo_type === 'Air Cargo') {
        if (packageItem.totalWeight <= 0) {
            push.error("Please fill the total weight");
            return;
        }
    }

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

        calculatePayment();
    } else {
        const newItem = {...packageItem}; // Create a copy of packageItem
        packageList.value.push(newItem); // Add the new item to packageList
        form.packages = packageList.value;

        const volume = parseFloat(newItem.volume) || 0;
        grandTotalWeight.value += parseFloat(newItem.totalWeight);
        grandTotalVolume.value += parseFloat(volume.toFixed(3));
        calculatePayment();
    }
    closeAddPackageModal();
};

// Watch for changes in length, width, height, or quantity to update volume and totalWeight
watch(
    [
        () => packageItem.length,
        () => packageItem.width,
        () => packageItem.height,
        () => packageItem.quantity,
    ],
    ([newLength, newWidth, newHeight, newQuantity]) => {
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
        packageItem.volume = volumeCubicMeters.toFixed(3);
        // packageItem.totalWeight = totalWeightKg;
    }
);

const vat = ref(0);

watch(
    [
        () => form.other_charge,
        () => form.discount,
        () => form.freight_charge,
        () => vat,
        () => form.additional_charge,
        () => form.bill_charge,
        () => form.destination_charges,
        () => form.package_charges,
    ],
    ([newOtherCharge, newDiscount, newFreightCharge]) => {
        // Convert dimensions from cm to meters
        hblTotal.value =
            parseFloat(form.freight_charge) +
            parseFloat(form.bill_charge) +
            parseFloat(form.package_charges) +
            parseFloat(form.destination_charges) +
            // parseFloat(form.other_charge) +
            parseFloat(vat.value) -
            form.discount +
            parseFloat(form.additional_charge);
        hblTotal.value = Number(hblTotal.value.toFixed(2))
        form.grand_total = hblTotal.value;
    }
);

watch([() => form.cargo_type], ([newCargoType]) => {
    calculatePayment();
    hblRules();
});

watch([() => form.hbl_type], ([newHBLType]) => {
    calculatePayment();
    hblRules();
});

watch([() => form.warehouse], ([newHBLType]) => {
    calculatePayment();
    hblRules();
});

const selectedType = ref("");

const isChecked = ref(false);

const addToConsigneeDetails = () => {
    if (isChecked.value) {
        form.consignee_name = form.hbl_name;
        consignee_contact.value = contactNumber.value;
        form.consignee_nic = form.nic;
        form.consignee_address = form.address;
    } else {
        resetConsigneeDetails();
    }
};

const resetConsigneeDetails = () => {
    form.consignee_name = "";
    consignee_contact.value = "";
    form.consignee_nic = "";
    form.consignee_address = "";
};

const updateTypeDescription = () => {
    packageItem.type = (packageItem.type ? " " : "") + selectedType.value;
};

const hblTotal = ref(0);
const currency = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const isEditable = ref(false);
const perPackageCharge = ref(0);
const perVolumeCharge = ref(0);
const perFreightCharge = ref(0);
const freightOperator = ref('');
const priceMode = ref('');

const freight_charge_operations = ref([]);

const calculatePayment = async () => {
    try {
        for (let pkg of packageList.value) {
            if (pkg.packageRule > 0) {
                form.is_active_package = true;
                break;
            } else form.is_active_package = false;
        }
        const response = await fetch(`/hbls/calculate-payment`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
                // "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({
                cargo_type: form.cargo_type,
                hbl_type: form.hbl_type,
                warehouse: form.warehouse,
                grand_total_volume: grandTotalVolume.value,
                grand_total_weight: grandTotalWeight.value,
                package_list_length: packageList.value.length,
                package_list: packageList.value,
                is_active_package: form.is_active_package,
            })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();

            form.freight_charge = data.freight_charge;
            form.bill_charge = data.bill_charge;
            form.other_charge = data.other_charge;
            form.package_charges = data.package_charges;
            form.destination_charges = data.destination_charges;
            isEditable.value = data.is_editable;
            vat.value = data.vat;
            perPackageCharge.value = data.per_package_charge;
            perVolumeCharge.value = data.per_volume_charge;
            perFreightCharge.value = data.per_freight_charge;
            freightOperator.value = data.freight_operator;
            priceMode.value = data.price_mode;
            freight_charge_operations.value = data.freight_charge_operations;
        }

    } catch (error) {
        console.log(error);
    }
}

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

const handleRemovePackage = () => {
    if (packageIndex.value !== null) {
        grandTotalVolume.value -= packageList.value[packageIndex.value].volume;
        grandTotalWeight.value -= packageList.value[packageIndex.value].totalWeight;
        packageList.value.splice(packageIndex.value, 1);
        calculatePayment();
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
    packageItem.type = "";
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
};

const copyFromHBLToShipperModalShow = ref(false);

const reference = ref(null);

const confirmShowingCopyFromHBLToShipperModal = () => {
    copyFromHBLToShipperModalShow.value = true;
}

const closeCopyFromHBLToShipperModal = () => {
    reference.value = null;
    copyFromHBLToShipperModalShow.value = false;
}

const handleCopyFromHBLToShipper = async () => {
    try {
        const response = await fetch(`/get-hbl-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            closeCopyFromHBLToShipperModal()
            push.error('HBL Missing or Invalid Reference Number');
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            closeCopyFromHBLToShipperModal()

            form.hbl_name = data.hbl_name;
            form.email = data.email;
            form.nic = data.nic;
            form.iq_number = data.iq_number;
            form.address = data.address;

            splitNumber(data.contact_number);

            push.success('Copied!');
        }

    } catch (error) {
        console.log(error);
    }
}

const copyFromHBLToConsigneeModalShow = ref(false);

const confirmShowingCopyFromHBLToConsigneeModal = () => {
    copyFromHBLToConsigneeModalShow.value = true;
}

const closeCopyFromHBLToConsigneeModal = () => {
    reference.value = null;
    copyFromHBLToConsigneeModalShow.value = false;
}

const handleCopyFromHBLToConsignee = async () => {
    try {
        const response = await fetch(`/get-hbl-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            closeCopyFromHBLToConsigneeModal()
            push.error('HBL Missing or Invalid Reference Number');
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            closeCopyFromHBLToConsigneeModal()

            form.consignee_name = data.consignee_name;
            form.consignee_nic = data.consignee_nic;
            form.consignee_address = data.consignee_address;
            form.consignee_note = data.consignee_note;

            splitNumberConsignee(data.consignee_contact);

            push.success('Copied!');
        }

    } catch (error) {
        console.log(error);
    }
}

const copiedPackages = ref({});

const copyFromHBLToPackageModalShow = ref(false);

const confirmShowingCopyFromHBLToPackageModal = () => {
    copyFromHBLToPackageModalShow.value = true;
}

const closeCopyFromHBLToPackageModal = () => {
    reference.value = null;
    copyFromHBLToPackageModalShow.value = false;
}

const handleCopyFromHBLToPackage = async () => {
    try {
        const response = await fetch(`/get-hbl-packages-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            closeCopyFromHBLToPackageModal()
            push.error('HBL Packages Missing or Invalid Reference Number');
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            closeCopyFromHBLToPackageModal()
            copiedPackages.value = data;

            const copiedTotalWeight = copiedPackages.value.reduce((acc, curr) => acc + curr.weight, 0);
            const copiedTotalVolume = copiedPackages.value.reduce((acc, curr) => acc + curr.volume, 0);

            grandTotalWeight.value += copiedTotalWeight;
            grandTotalVolume.value += copiedTotalVolume;

            calculatePayment();

            push.success('Copied!');
        }

    } catch (error) {
        console.log(error);
    }
}

const handleRemoveCopiedPackages = () => {
    copiedPackages.value = {};
    grandTotalWeight.value = 0;
    grandTotalVolume.value = 0;
    calculatePayment();
}

const handleCopyShipper = () => {
    form.consignee_name = form.hbl_name;
    consignee_countryCode.value = countryCode.value;
    consignee_contact.value = contactNumber.value;
    form.consignee_nic = form.nic;
    form.consignee_address = form.address;
}

const isShowedPaymentSummery = ref(false);

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

const isPackageRuleSelected = ref(false);
const packageRulesData = ref([]);
const priceRulesData = ref([]);
const selectedPackage = ref("");
const isExistsRules = ref(false);

const hblRules = async () => {
    try {
        const response = await fetch(`/get-hbl-rules`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
                // "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({
                cargo_type: form.cargo_type,
                hbl_type: form.hbl_type,
                warehouse: form.warehouse,
            })
        });
        const data = await response.json();
        if ((!data.package_rules || data.package_rules.length === 0) &&
            (!data.price_rules || data.price_rules.length === 0)) {
            push.error('Please add price rules');
            isExistsRules.value = false;
        }else{
            isExistsRules.value = true;
        }
        if (data.package_rules) {
            packageRulesData.value = data.package_rules;
        }
        if (data.price_rules) {
            priceRulesData.value = data.price_rules;
        }

    } catch (error) {
        console.log(error);
    }
};

const getSelectedPackage = () => {
    // Find the selected package from the packages array based on the selected ID
    const selectedRule = packageRulesData.value.find(pkg => pkg.id === packageItem.packageRule);
    if (selectedRule) {
        isPackageRuleSelected.value = true;
        packageItem.length = selectedRule.length;
        packageItem.width = selectedRule.width;
        packageItem.height = selectedRule.height;
    }else {
        isPackageRuleSelected.value = false;
        packageItem.length = 0;
        packageItem.width = 0;
        packageItem.height = 0;
    }
};
</script>

<template>
    <AppLayout title="HBL Create">
        <template #header>HBL - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handleHBLCreate">
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

                        <!-- Warehouse -->
                        <div>
                            <h2
                                class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 mt-0"
                            >
                                Warehouse
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
                                <label class="relative flex">
                                    <input
                                        v-model="form.hbl_name"
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
                                        <option
                                            v-for="(countryCode, index) in countryCodes"
                                            :key="index"
                                            :value="countryCode"
                                        >
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
                                <a  v-if="form.hbl_name"  @click.prevent="handleCopyShipper"
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
                                <span>Mobile Number</span>
                                <div class="flex -space-x-px">
                                    <select
                                        v-model="consignee_countryCode"
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option
                                            v-for="(countryCode, index) in countryCodes"
                                            :key="index"
                                        >
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
                                <a v-if="Object.values(copiedPackages).length === 0"
                                   @click.prevent="confirmShowingCopyFromHBLToPackageModal"
                                   x-tooltip.placement.bottom="'Copy from HBL'"
                                >
                                    <svg class="icon icon-paste text-[#64748b] ml-5" fill="none" stroke="#64748b"
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
                                <DangerOutlineButton v-if="Object.values(copiedPackages).length > 0"
                                                     @click.prevent="handleRemoveCopiedPackages">
                                    Remove Copied Packages
                                </DangerOutlineButton>
                            </div>
                            <PrimaryOutlineButton v-if="Object.values(copiedPackages).length === 0" type="button" :disabled="!isExistsRules"
                                                  @click="showPackageDialog">
                                New Package <i class="fas fa-plus fa-fw fa-fw"></i>
                            </PrimaryOutlineButton>
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
                                <SecondaryButton @click="closeCopyFromHBLToPackageModal">
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton
                                    class="ms-3"
                                    @click.prevent="handleCopyFromHBLToPackage"
                                >
                                    Copy From HBL
                                </PrimaryButton>
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
                                    Price and Payment
                                </h2>
                                <button
                                    class="btn border border-primary font-medium text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white active:bg-primary/90"
                                    type="button"
                                    @click="calculatePayment"
                                >
                                    Calculate
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-5 mt-5">
                                <div>
                                    <span>Freight Charge</span>
                                    <TextInput
                                        v-model="form.freight_charge"
                                        :disabled="!isEditable"
                                        class="w-full"
                                        min="0"
                                        step="any"
                                        type="number"
                                    />
                                    <InputError :message="form.errors.freight_charge"/>
                                </div>

                                <div>
                                    <span>Bill Charge</span>
                                    <TextInput
                                        v-model="form.bill_charge"
                                        :disabled="!isEditable"
                                        class="w-full"
                                        min="0"
                                        step="any"
                                        type="number"
                                    />
                                    <InputError :message="form.errors.bill_charge"/>
                                </div>

                                <div>
                                    <span>Destination Charges</span>
                                    <TextInput
                                        v-model="form.destination_charges"
                                        :disabled="!isEditable"
                                        class="w-full"
                                        min="0"
                                        step="any"
                                        type="number"
                                    />
                                </div>

                                <div>
                                    <span>Package Charges</span>
                                    <TextInput
                                        v-model="form.package_charges"
                                        :disabled="!isEditable"
                                        class="w-full"
                                        min="0"
                                        step="any"
                                        type="number"
                                    />
                                </div>

                                <div>
                                    <span>Discount</span>
                                    <TextInput
                                        v-model="form.discount"
                                        :disabled="!isEditable"
                                        class="w-full"
                                        placeholder="0"
                                        step="any"
                                        type="number"
                                    />
                                    <InputError :message="form.errors.discount"/>
                                </div>

                                <div>
                                    <span>Paid Amount</span>
                                    <TextInput
                                        v-model="form.paid_amount"
                                        class="w-full"
                                        min="0"
                                        step="any"
                                        type="number"
                                    />
                                    <InputError :message="form.errors.paid_amount"/>
                                </div>

                                <div>
                                    <span>Additional Charges</span>
                                    <TextInput
                                        v-model="form.additional_charge"
                                        :disabled="!isEditable"
                                        class="w-full"
                                        placeholder="0"
                                        step="any"
                                        type="number"
                                    />
                                    <InputError :message="form.errors.additional_charges"/>
                                </div>

                                <div class="col-start-2 mt-20 space-y-2.5 font-bold">
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

                                <div class="col-span-2">
                                    <div
                                        class="flex justify-between text-2xl text-success font-bold"
                                    >
                                        <p class="line-clamp-1">Grand Total</p>
                                        <div class="flex items-center">
                                            <svg v-if="packageList.length > 0"
                                                 class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle mr-3 text-info hover:cursor-pointer"
                                                 fill="none" height="24" stroke="currentColor" stroke-linecap="round"
                                                 stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24"
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 @click="isShowedPaymentSummery = !isShowedPaymentSummery">
                                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                                                <path d="M12 9h.01"/>
                                                <path d="M11 12h1v4h1"/>
                                            </svg>
                                            <p>{{ hblTotal ? hblTotal.toFixed(2) : 0.00 }} {{ currency }}</p>
                                        </div>
                                    </div>
                                    <template v-if="isShowedPaymentSummery">
                                        <div v-if="packageList.length > 0" class="p-2 bg-slate-100 rounded-lg mt-2">
                                            <table class="italic w-full">
                                                <tr v-if="!form.is_active_package">
                                                    <td colspan="2">Freight Charges</td>
                                                    <td colspan="2">
                                                    <span v-for="(charge, index) in freight_charge_operations"
                                                          :key="index">
                                                        {{ charge }} <br>
                                                    </span>
                                                    </td>
                                                    <td class="text-right">{{
                                                            parseFloat(form.freight_charge).toFixed(2)
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Destination Charge</td>
                                                    <td class="text-right">
                                                        {{ parseFloat(form.destination_charges).toFixed(2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Package Charge</td>
                                                    <td class="text-right">
                                                        {{ parseFloat(form.package_charges).toFixed(2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Bill Charges</td>
                                                    <td class="text-right">{{ parseFloat(form.bill_charge).toFixed(2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Discount</td>
                                                    <td class="text-right">- {{
                                                            parseFloat(form.discount).toFixed(2)
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Additional Charge</td>
                                                    <td class="text-right">+
                                                        {{ parseFloat(form.additional_charge).toFixed(2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">Vat</td>
                                                    <td class="text-right">+
                                                        {{ parseFloat(vat).toFixed(2) }}
                                                    </td>
                                                </tr>
                                                <tr class="font-bold">
                                                    <td colspan="4">Total</td>
                                                    <td class="text-right">{{ hblTotal.toFixed(2) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </template>
                                </div>
                                <!-- -->
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
                        :disabled="form.processing || !isExistsRules"
                        class="space-x-2"
                        type="submit"
                    >
                        <span>Create a HBL</span>
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
                    <p class="text-base">
                        {{ !editMode ? "Add new package to HBL" : "" }}
                    </p>

                    <div class="mt-4 space-y-4">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-4">
                                <label class="block">
                                    <span>
                                        Package
                                        <span v-if="form.is_active_package" class="text-red-500 text-sm">*</span>
                                    </span>
                                    <select
                                        v-model="packageItem.packageRule"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                        @change="getSelectedPackage"
                                        :required="form.is_active_package"
                                        :disabled="!form.is_active_package && packageList.length > 0"
                                    >
                                        <option value="0">Choose Package</option>
                                        <option
                                            v-for="pkg in packageRulesData"
                                            :key="pkg.id"
                                            :value="pkg.id"
                                        >
                                            {{
                                                pkg.rule_title + ' (' + pkg.length + '*' + pkg.width + '*' + pkg.height + ')'
                                            }}
                                        </option>
                                    </select>
                                </label>
                            </div>
                            <div class="col-span-2">
                                <label class="block">
                                    <span>Type </span>
                                    <select
                                        v-model="selectedType"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                        @change="updateTypeDescription"
                                    >
                                        <option value="">Choose one</option>
                                        <option v-for="type in packageTypes" :key="type">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </label>
                            </div>
                            <div class="col-span-2">
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
                                </label>
                            </div>
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

                            <div class="col-span-2">
                                <label class="block">
                  <span
                  >Volume (M.CU)
                    <span class="text-red-500 text-sm">*</span></span
                  >
                                    <input
                                        v-model="packageItem.volume"
                                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="1.00"
                                        step="0.001"
                                        type="number"
                                    />
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
    </AppLayout>
</template>
