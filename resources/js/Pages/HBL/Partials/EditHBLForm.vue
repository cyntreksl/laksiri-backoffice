<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import {computed, onBeforeMount, reactive, ref, watch} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import RemovePackageConfirmationModal from "@/Pages/HBL/Partials/RemovePackageConfirmationModal.vue";
import {push} from "notivue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    hbl: {
        type: Object,
        default: () => {
        },
    },
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
});

const form = useForm({
    hbl_name: props.hbl.hbl_name,
    email: props.hbl.email,
    contact_number: props.hbl.contact_number,
    nic: props.hbl.nic,
    iq_number: props.hbl.iq_number,
    address: props.hbl.address,
    consignee_name: props.hbl.consignee_name,
    consignee_nic: props.hbl.consignee_nic,
    consignee_contact: props.hbl.consignee_contact,
    consignee_address: props.hbl.consignee_address,
    consignee_note: props.hbl.consignee_note,
    cargo_type: props.hbl.cargo_type,
    hbl_type: props.hbl.hbl_type,
    warehouse: props.hbl.warehouse,
    freight_charge: props.hbl.freight_charge.toFixed(2),
    bill_charge: props.hbl.bill_charge.toFixed(2),
    other_charge: props.hbl.other_charge.toFixed(2),
    discount: props.hbl.discount.toFixed(2),
    paid_amount: props.hbl.paid_amount.toFixed(2),
    grand_total: props.hbl.grand_total.toFixed(2),
    packages: props.hbl.packages,
});

const handleHBLUpdate = () => {
    form.put(route("hbls.update", props.hbl.id), {
        onSuccess: () => {
            push.success("HBL Updated Successfully!");
            router.visit(route("hbls.index"));
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};

const resetModal = () => {
    packageItem.package_type = "";
    packageItem.length = 0;
    packageItem.width = 0;
    packageItem.height = 0;
    packageItem.quantity = 0;
    packageItem.volume = 0;
    packageItem.weight = 0;
    packageItem.remarks = "";
};

const showAddNewPackageDialog = ref(false);

const showPackageDialog = () => {
    showAddNewPackageDialog.value = true;
};

const packageList = ref(form.packages ?? []);

const packageItem = reactive({
    package_type: "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 0,
    volume: 0,
    weight: 0,
    remarks: "",
});

const grandTotalVolume = computed(() => {
    return form.packages.reduce((acc, pack) => {
        return acc + pack.volume;
    }, 0);
});

const grandTotalWeight = computed(() => {
    return form.packages.reduce((acc, pack) => {
        return acc + pack.weight;
    }, 0);
});

const addPackageData = () => {
    if (
        !packageItem.package_type ||
        packageItem.length <= 0 ||
        packageItem.width <= 0 ||
        packageItem.height <= 0 ||
        packageItem.quantity <= 0 ||
        packageItem.volume <= 0 ||
        packageItem.weight <= 0
    ) {
        push.error("Please fill all required data");
        return;
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

        grandTotalWeight.value += newItem.weight;
        grandTotalVolume.value += newItem.volume;

        calculatePayment();

        resetModal();
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
        packageItem.volume = volumeCubicMeters;
        packageItem.weight = totalWeightKg;
    }
);

const vat = ref(0);

watch(
    [
        () => form.other_charge,
        () => form.discount,
        () => form.freight_charge,
        () => vat,
    ],
    ([newOtherCharge, newDiscount, newFreightCharge]) => {
        // Convert dimensions from cm to meters
        hblTotal.value =
            parseFloat(form.freight_charge) +
            parseFloat(form.bill_charge) +
            parseFloat(form.freight_charge) +
            parseFloat(form.other_charge) +
            parseFloat(vat.value) -
            form.discount;
        form.grand_total = hblTotal.value;
    }
);

watch([() => form.cargo_type], ([newCargoType]) => {
    calculatePayment();
});

const packageTypes = [
    "WOODEN BOX",
    "CARTON",
    "FRIDGE",
    "TV CARTON",
    "COOKER",
    "W/MACHINE",
    "MATT/BED BDL",
    "TRUNK STEEL BOX",
    "TRAVELING BOX",
    "IRON TABLE/LADDER",
    "SOFA SET/BNDL",
    "BNDL",
    "BICYCLE",
];

const selectedType = ref("");

const updateTypeDescription = () => {
    packageItem.package_type =
        (packageItem.package_type ? " " : "") + selectedType.value;
};

const hblTotal = ref(0);
const currency = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const isEditable = ref(false);

onBeforeMount(() => {
    const cargoType = props.hbl.cargo_type;

    if (cargoType === "Sea Cargo") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Sea Cargo"
            );
        });

        isEditable.value = Boolean(priceRule.value.is_editable);
    } else if (cargoType === "Air Cargo") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Air Cargo"
            );
        });

        isEditable.value = Boolean(priceRule.value.is_editable);
    }
});

const calculatePayment = () => {
    const cargoType = form.cargo_type;
    const hblType = form.hbl_type;
    const freightCharge = ref(props.hbl.freight_charge);
    const billCharge = ref(props.hbl.bill_charge);
    const otherCharge = ref(props.hbl.other_charge);

    if (cargoType === "Sea Cargo" && hblType === "Gift") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Sea Cargo" && priceRule.hbl_type === "Gift"
            );
        });

        if (priceRule.value.price_mode === "volume") {
            const trueAction = priceRule.value.true_action.trim();
            const operator = trueAction[0];
            const value = parseFloat(trueAction.slice(1).trim());

            switch (operator) {
                case "*":
                    freightCharge.value = grandTotalVolume.value * value;
                    break;
                case "+":
                    freightCharge.value = grandTotalVolume.value + value;
                    break;
                case "-":
                    freightCharge.value = grandTotalVolume.value - value;
                    break;
                case "/":
                    if (value !== 0) {
                        freightCharge.value = grandTotalVolume.value / value;
                    } else {
                        console.error("Division by zero error");
                    }
                    break;
                default:
                    console.error("Unsupported operation");
                    break;
            }
        }

        billCharge.value = priceRule.value.bill_price.toFixed(3) || 0;
        otherCharge.value =
                (parseFloat(priceRule.value.per_package_charges) + parseFloat(priceRule.value.volume_charges) * grandTotalVolume.value).toFixed(3) || 0;
        isEditable.value = Boolean(priceRule.value.is_editable);
        vat.value =
            priceRule.value.bill_vat !== 0
                ? parseFloat(priceRule.value.bill_vat) / 100
                : 0;
    } else if (cargoType === "Sea Cargo" && hblType === "UBP") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Sea Cargo" && priceRule.hbl_type === "UBP"
            );
        });

        if (priceRule.value.price_mode === "volume") {
            const trueAction = priceRule.value.true_action.trim();
            const operator = trueAction[0];
            const value = parseFloat(trueAction.slice(1).trim());

            switch (operator) {
                case "*":
                    freightCharge.value = grandTotalVolume.value * value;
                    break;
                case "+":
                    freightCharge.value = grandTotalVolume.value + value;
                    break;
                case "-":
                    freightCharge.value = grandTotalVolume.value - value;
                    break;
                case "/":
                    if (value !== 0) {
                        freightCharge.value = grandTotalVolume.value / value;
                    } else {
                        console.error("Division by zero error");
                    }
                    break;
                default:
                    console.error("Unsupported operation");
                    break;
            }
        }

        billCharge.value = priceRule.value.bill_price.toFixed(3) || 0;
        otherCharge.value =
                (parseFloat(priceRule.value.per_package_charges) + parseFloat(priceRule.value.volume_charges) * grandTotalVolume.value).toFixed(3) || 0;
        isEditable.value = Boolean(priceRule.value.is_editable);
        vat.value =
            priceRule.value.bill_vat !== 0
                ? parseFloat(priceRule.value.bill_vat) / 100
                : 0;
    } else if (cargoType === "Sea Cargo" && hblType === "Door to Door") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Sea Cargo" && priceRule.hbl_type === "Door to Door"
            );
        });

        if (priceRule.value.price_mode === "volume") {
            const trueAction = priceRule.value.true_action.trim();
            const operator = trueAction[0];
            const value = parseFloat(trueAction.slice(1).trim());

            switch (operator) {
                case "*":
                    freightCharge.value = grandTotalVolume.value * value;
                    break;
                case "+":
                    freightCharge.value = grandTotalVolume.value + value;
                    break;
                case "-":
                    freightCharge.value = grandTotalVolume.value - value;
                    break;
                case "/":
                    if (value !== 0) {
                        freightCharge.value = grandTotalVolume.value / value;
                    } else {
                        console.error("Division by zero error");
                    }
                    break;
                default:
                    console.error("Unsupported operation");
                    break;
            }
        }

        billCharge.value = priceRule.value.bill_price.toFixed(3) || 0;
        otherCharge.value =
                (parseFloat(priceRule.value.per_package_charges) + parseFloat(priceRule.value.volume_charges) * grandTotalVolume.value).toFixed(3) || 0;
        isEditable.value = Boolean(priceRule.value.is_editable);
        vat.value =
            priceRule.value.bill_vat !== 0
                ? parseFloat(priceRule.value.bill_vat) / 100
                : 0;
    } else if (cargoType === "Air Cargo" && hblType === "Gift") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Air Cargo" && priceRule.hbl_type === "Gift"
            );
        });

        if (priceRule.value.price_mode === "weight") {
            const trueAction = priceRule.value.true_action.trim();
            const operator = trueAction[0];
            const value = parseFloat(trueAction.slice(1).trim());

            switch (operator) {
                case "*":
                    freightCharge.value = grandTotalWeight.value * value;
                    break;
                case "+":
                    freightCharge.value = grandTotalWeight.value + value;
                    break;
                case "-":
                    freightCharge.value = grandTotalWeight.value - value;
                    break;
                case "/":
                    if (value !== 0) {
                        freightCharge.value = grandTotalWeight.value / value;
                    } else {
                        console.error("Division by zero error");
                    }
                    break;
                default:
                    console.error("Unsupported operation");
                    break;
            }
        }

        billCharge.value = priceRule.value.bill_price.toFixed(3) || 0;
        otherCharge.value =
                (parseFloat(priceRule.value.per_package_charges) + parseFloat(priceRule.value.volume_charges) * grandTotalVolume.value).toFixed(3) || 0;
        isEditable.value = Boolean(priceRule.value.is_editable);
        vat.value =
            priceRule.value.bill_vat !== 0
                ? parseFloat(priceRule.value.bill_vat) / 100
                : 0;
    } else if (cargoType === "Air Cargo" && hblType === "UBP") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Air Cargo" && priceRule.hbl_type === "UBP"
            );
        });

        if (priceRule.value.price_mode === "weight") {
            const trueAction = priceRule.value.true_action.trim();
            const operator = trueAction[0];
            const value = parseFloat(trueAction.slice(1).trim());

            switch (operator) {
                case "*":
                    freightCharge.value = grandTotalWeight.value * value;
                    break;
                case "+":
                    freightCharge.value = grandTotalWeight.value + value;
                    break;
                case "-":
                    freightCharge.value = grandTotalWeight.value - value;
                    break;
                case "/":
                    if (value !== 0) {
                        freightCharge.value = grandTotalWeight.value / value;
                    } else {
                        console.error("Division by zero error");
                    }
                    break;
                default:
                    console.error("Unsupported operation");
                    break;
            }
        }

        billCharge.value = priceRule.value.bill_price.toFixed(3) || 0;
        otherCharge.value =
                (parseFloat(priceRule.value.per_package_charges) + parseFloat(priceRule.value.volume_charges) * grandTotalVolume.value).toFixed(3) || 0;
        isEditable.value = Boolean(priceRule.value.is_editable);
        vat.value =
            priceRule.value.bill_vat !== 0
                ? parseFloat(priceRule.value.bill_vat) / 100
                : 0;
    } else if (cargoType === "Air Cargo" && hblType === "Door to Door") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Air Cargo" && priceRule.hbl_type === "Door to Door"
            );
        });

        if (priceRule.value.price_mode === "weight") {
            const trueAction = priceRule.value.true_action.trim();
            const operator = trueAction[0];
            const value = parseFloat(trueAction.slice(1).trim());

            switch (operator) {
                case "*":
                    freightCharge.value = grandTotalWeight.value * value;
                    break;
                case "+":
                    freightCharge.value = grandTotalWeight.value + value;
                    break;
                case "-":
                    freightCharge.value = grandTotalWeight.value - value;
                    break;
                case "/":
                    if (value !== 0) {
                        freightCharge.value = grandTotalWeight.value / value;
                    } else {
                        console.error("Division by zero error");
                    }
                    break;
                default:
                    console.error("Unsupported operation");
                    break;
            }
        }

        billCharge.value = priceRule.value.bill_price.toFixed(2) || 0;
        otherCharge.value =
                (parseFloat(priceRule.value.per_package_charges) + parseFloat(priceRule.value.volume_charges) * grandTotalVolume.value).toFixed(3) || 0;
        isEditable.value = Boolean(priceRule.value.is_editable);
        vat.value =
            priceRule.value.bill_vat !== 0
                ? parseFloat(priceRule.value.bill_vat) / 100
                : 0;
    }

    form.freight_charge = freightCharge.value.toFixed(2);
    form.bill_charge = billCharge.value;
    form.other_charge = otherCharge.value;
};
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
    resetModal();
    showAddNewPackageDialog.value = false;
    editIndex.value = null;
    editMode.value = false;
};

const editMode = ref(false);
const editIndex = ref(null);

const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPackageDialog.value = true;
    // populate packageItem with existing data for editing
    Object.assign(packageItem, packageList.value[index]);
};
</script>

<template>
    <form @submit.prevent="handleHBLUpdate">
        <div class="grid grid-cols-1 sm:grid-cols-5 my-4 gap-4">
            <div class="sm:col-span-3 space-y-5">
                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                        >
                            Basic Details
                        </h2>
                    </div>
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
                    <div class="grid grid-cols-2 gap-5 mt-3">
                        <div>
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

                        <div>
                            <span>Mobile Number</span>
                            <div class="flex -space-x-px">
                                <select
                                    class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option>+94</option>
                                    <option>+95</option>
                                    <option>+96</option>
                                </select>

                                <input
                                    v-model="form.contact_number"
                                    class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                    placeholder="123 4567 890"
                                    type="text"
                                />
                            </div>
                            <InputError :message="form.errors.contact_number"/>
                        </div>

                        <div>
                            <span>PP or NIC No</span>
                            <label class="relative flex">
                                <input
                                    v-model="form.nic"
                                    class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="ID/PP Number"
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
                                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>
                            </label>
                            <InputError :message="form.errors.nic"/>
                        </div>

                        <div>
                            <span>IQ Number</span>
                            <label class="relative flex">
                                <input
                                    v-model="form.iq_number"
                                    class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="IQ Number"
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
                                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>
                            </label>
                            <InputError :message="form.errors.iq_number"/>
                        </div>

                        <div class="col-span-2">
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

                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                        >
                            Consignee Details
                        </h2>
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

                        <div>
                            <span>PP or NIC No</span>
                            <label class="relative flex">
                                <input
                                    v-model="form.consignee_nic"
                                    class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="NIC/PP Number"
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
                                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>
                            </label>
                            <InputError :message="form.errors.consignee_nic"/>
                        </div>

                        <div>
                            <span>Mobile Number</span>
                            <div class="flex -space-x-px">
                                <select
                                    class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option>+94</option>
                                    <option>+95</option>
                                    <option>+96</option>
                                </select>

                                <input
                                    v-model="form.consignee_contact"
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
                    rows="4"
                ></textarea>
                            </label>
                            <InputError :message="form.errors.consignee_note"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2 space-y-5">
                <!-- Action Buttons -->
                <div class="flex justify-end space-x-5">
                    <DangerOutlineButton @click="router.visit(route('hbls.index'))"
                    >Cancel
                    </DangerOutlineButton
                    >
                    <PrimaryButton class="space-x-2" type="submit">
                        <span>Update HBL</span>
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

                <!-- Cargo Type -->
                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
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
                            </label>
                        </div>
                        <InputError :message="form.errors.cargo_type"/>
                    </div>
                </div>

                <!-- Type -->
                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
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
                </div>

                <!-- Warehouse -->
                <div class="card px-4 py-4 sm:px-5">
                    <div>
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                        >
                            Warehouse
                        </h2>
                    </div>
                    <div class="my-5">
                        <div class="space-x-5">
                            <label
                                v-for="warehouse in warehouses"
                                class="inline-flex items-center space-x-2"
                            >
                                <input
                                    v-model="form.warehouse"
                                    :value="warehouse"
                                    class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                    name="warehouse"
                                    type="radio"
                                />
                                <p>{{ warehouse }}</p>
                            </label>
                        </div>
                        <InputError :message="form.errors.warehouse"/>
                    </div>
                </div>

                <!-- Price & Payment -->
                <div class="card px-4 py-4 sm:px-5">
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
                            Re Calculate Payment
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
                            <span>Destination Charge</span>
                            <TextInput
                                v-model="form.other_charge"
                                :disabled="!isEditable"
                                class="w-full"
                                min="0"
                                step="any"
                                type="number"
                            />
                            <InputError :message="form.errors.other_charge"/>
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

                        <div class="col-span-2">
                            <span>Paid Amount</span>
                            <TextInput
                                v-model="form.paid_amount"
                                :disabled="!isEditable"
                                class="w-full"
                                min="0"
                                step="any"
                                type="number"
                            />
                            <InputError :message="form.errors.paid_amount"/>
                        </div>

                        <div class="col-start-2 mt-2 space-y-2.5 font-bold">
                            <div class="flex justify-between">
                                <p class="line-clamp-1">Packages</p>
                                <p class="text-slate-700 dark:text-navy-100">
                                    {{ packageList.length }}
                                </p>
                            </div>
                            <div class="flex justify-between">
                                <p class="line-clamp-1">Weight</p>
                                <p class="text-slate-700 dark:text-navy-100">
                                    {{ grandTotalWeight }}
                                </p>
                            </div>
                            <div class="flex justify-between">
                                <p class="line-clamp-1">Volume</p>
                                <p class="text-slate-700 dark:text-navy-100">
                                    {{ grandTotalVolume }}
                                </p>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <div class="flex justify-between text-2xl text-success font-bold">
                                <p class="line-clamp-1">Grand Total</p>
                                <p>{{ hblTotal.toFixed(2) }} {{ currency }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card col-span-5 px-4 py-4 sm:px-5 mb-10">
            <div class="flex justify-between items-center">
                <h2
                    class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                >
                    Package Details
                </h2>
                <PrimaryOutlineButton type="button" @click="showPackageDialog">
                    New Package <i class="fas fa-plus fa-fw fa-fw"></i>
                </PrimaryOutlineButton>
            </div>

            <div class="mt-5">
                <div
                    v-if="form.packages.length > 0"
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
                        <tr v-for="(item, index) in form.packages">
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
                                {{ item.weight }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ item.volume }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ item.remarks || "-" }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center">
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
                        <div class="col-span-2">
                            <label class="block">
                                <span>Type </span>
                                <select
                                    v-model="selectedType"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    @change="updateTypeDescription"
                                >
                                    <option value="">Choose one</option>
                                    <option
                                        v-for="package_type in packageTypes"
                                        :key="package_type"
                                    >
                                        {{ package_type }}
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
                                    v-model="packageItem.package_type"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="Sofa set"
                                    type="text"
                                />
                            </label>
                        </div>

                        <div>
                            <label class="block">
                <span
                >Length (cm) <br/>
                    <span class="text-red-500 text-sm">*</span></span
                >
                                <input
                                    v-model="packageItem.length"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="1.00"
                                    step="0.01"
                                    type="number"
                                />
                            </label>
                        </div>
                        <div>
                            <label class="block">
                                 <span
                                 >Width <br/><span class="text-red-500 text-sm">*</span>
                  </span>
                                <input
                                    v-model="packageItem.width"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="1.00"
                                    step="0.01"
                                    type="number"
                                />
                            </label>
                        </div>

                        <div>
                            <label class="block">
                                <span
                                >Height <br/><span class="text-red-500 text-sm"
                                >*<br/></span
                                ></span>
                                <input
                                    v-model="packageItem.height"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    placeholder="1.00"
                                    step="0.01"
                                    type="number"
                                />
                            </label>
                        </div>
                        <div>
                            <label class="block">
                <span
                >Quantity <br/><span class="text-red-500 text-sm"
                >*<br/></span
                ></span>
                                <input
                                    v-model="packageItem.quantity"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
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
                                    step="0.01"
                                    type="number"
                                />
                            </label>
                        </div>
                        <div class="col-span-2">
                            <label class="block">
                <span
                >Total Weight
                  <span class="text-red-500 text-sm">*</span></span
                >
                                <input
                                    v-model="packageItem.weight"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
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
                        <SecondaryButton class="min-w-[7rem]" @click="closeAddPackageModal">
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
</template>
