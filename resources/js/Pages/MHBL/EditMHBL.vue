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
import {forEach} from "vuedraggable/dist/vuedraggable.common.js";

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
    mhbl: {
        type: Object,
        default: () => {
        }
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
const grandVolume = ref(
    packageList.value.reduce((total, packageItem) => total + packageItem.volume, 0)
);
const grandWeight = ref(
    packageList.value.reduce((total, packageItem) => total + packageItem.weight, 0)
);

//branch set
const currentBranch = usePage().props?.auth.user.active_branch_name;

const findCountryCodeByBranch = (country) => {
    return usePage().props.currentBranch.country_code;
};



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

const countryCode = ref(splitCountryCode(props.mhbl.shipper.mobile_number) ?? findCountryCodeByBranch(currentBranch));
const consignee_countryCode = ref(splitCountryCode(props.mhbl.consignee.mobile_number) ?? '+94');
const contactNumber = ref(splitContactNumber(props.mhbl.shipper.mobile_number) ?? "");
const consignee_contact = ref(splitContactNumber(props.mhbl.consignee.mobile_number) ?? "");

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
    hbl_name: props.mhbl.shipper.name,
    email: props.mhbl.shipper.email,
    contact_number: computed(() => countryCode.value + contactNumber.value),
    nic: props.mhbl.shipper.pp_or_nic_no,
    iq_number: props.mhbl.shipper.residency_no,
    address: props.mhbl.shipper.address,
    consignee_name: props.mhbl.consignee.name,
    consignee_nic: props.mhbl.consignee.pp_or_nic_no,
    consignee_contact: computed(
        () => consignee_countryCode.value + consignee_contact.value
    ),
    consignee_address: props.mhbl.consignee.address,
    consignee_note: "",
    cargo_type: props.mhbl.cargo_type,
    hbl_type: 'Gift',
    warehouse: props.mhbl.warehouse.name,
    grand_volume: grandVolume.value ?? 0,
    grand_weight: grandWeight.value ?? 0,
    grand_total: props.grandTotal ?? 0,
    packages: {},
    is_active_package: false,
    shipper_id: props.mhbl.shipper.id,
    consignee_id: props.mhbl.consignee.id,
});

const packageItem = reactive({
    hbl: "",
    hbl_id: 0,
    id: 0,
    type: "",
    length: 0,
    width: 0,
    height: 0,
    weight: 0,
    quantity: 1,
    volume: 0,
    totalWeight: 0,
    remarks: "",
    packageRule: 0,
    measure_type: "cm",
});

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

const handleMHBLUpdate = () => {
    form.packages = packageList.value;
    if(form.packages.length > 0){
        form.put(route("mhbls.update", props.mhbl.id), {
            onSuccess: (page) => {
                form.reset();
                push.success("MHBL Updated Successfully!");
            },
            onError: () => console.log("error"),
            preserveScroll: true,
            preserveState: true,
        });
    }else push.error("Please select HBLs!");

};

const hblNumber = ref("");
const showAddNewHBLDialog = ref(false);
const showRemoveHBLDialog = ref(false);
const editMode = ref(false);
const showAddHBLModal = () => {
    showAddNewHBLDialog.value = true;
};

const showRemoveHBLModal = () => {
    showRemoveHBLDialog.value = true;
};

const closeAddNewHBLModal = () => {
    hblNumber.value = null;
    showAddNewHBLDialog.value = false;
}

const closeRemoveHBLModal = () => {
    hblNumber.value = null;
    showRemoveHBLDialog.value = false;
}

const handleAddNewHBL = async () => {
    const response = await fetch(`/mhbls/add-hbl`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": usePage().props.csrf,
        },
        body: JSON.stringify({
            hbl_number: hblNumber.value
        })
    });
    if (!response.ok) {
        throw new Error('Network response was not ok.');
    } else {
        const data = await response.json();

        if(data.mhbl){
            closeAddNewHBLModal();
            push.error("HBL already added to a MHBL.");
        }

        if(data.cargo_type !== form.cargo_type || data.hbl_type !== 'Door to Door' || data.warehouse === form.warehouse){
            closeAddNewHBLModal();
            push.error("Selected HBL is not maching to  Primary Details");
        }else{
            for (const hblPackage of data.packages) {
                const packageItem = {
                    hbl: data.hbl_number,
                    hbl_id: data.id,
                    type: hblPackage.package_type,
                    length: hblPackage.length,
                    width: hblPackage.width,
                    height: hblPackage.height,
                    quantity: hblPackage.quantity,
                    volume: hblPackage.volume,
                    weight: hblPackage.weight,
                    remarks: hblPackage.remarks,
                    packageRule: hblPackage.package_rule,
                    measure_type: hblPackage.measure_type,
                };

                const newItem = {...packageItem};
                packageList.value.push(newItem);
                form.grand_weight = form.grand_weight + packageItem.weight;
                form.grand_volume = form.grand_volume + packageItem.volume;
            }
            form.hbls.push(data.id);
            closeAddNewHBLModal();
            push.success('Add new HBL Successfully!')
        }

    }
}

const handleRemoveHBL = async () => {
    const selectedPackages = packageList.value.filter(pkg => pkg.hbl === hblNumber.value);
    form.grand_weight = form.grand_weight - selectedPackages.reduce((sum, pkg) => sum + pkg.weight, 0);
    form.grand_volume = form.grand_volume - selectedPackages.reduce((sum, pkg) => sum + pkg.volume, 0);
    packageList.value = packageList.value.filter(pkg => pkg.hbl !== hblNumber.value);
    form.hbls = form.hbls.filter(hbl => !selectedPackages.some(pkg => pkg.hbl_id === hbl));
    closeRemoveHBLModal();
    push.success('Remove HBL Successfully!')
}

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
        <form @submit.prevent="handleMHBLUpdate">
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

                        <!-- Cargo Type Section -->
                        <div class="mt-5">
                            <h2 class="text-sm font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Cargo Type <span class="text-xs text-gray-400">(Automatically Selected)</span>
                            </h2>
                        </div>
                        <div  class="my-5">
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
                                Type <span class="text-xs text-gray-400">(Automatically Selected)</span>
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
                        </div>

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
                                        disabled
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
                                        disabled
                                        v-model="countryCode"
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                            {{ countryCode }}
                                        </option>
                                    </select>

                                    <input
                                        disabled
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
                                        disabled
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
                                        disabled
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
                      disabled
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
                            </div>
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
                                        disabled
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
                                        disabled
                                        v-model="consignee_countryCode"
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                            {{ countryCode }}
                                        </option>
                                    </select>

                                    <input
                                        disabled
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
                                      disabled
                                      v-model="form.consignee_address"
                                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                      placeholder="Type address here..."
                                      rows="4"
                                  ></textarea>
                                </label>
                                <InputError :message="form.errors.consignee_address"/>
                            </div>

                            <div hidden="true" class="col-span-2">
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
                            <div>
                                <PrimaryOutlineButton class="mr-5" type="button"
                                                      @click="showAddHBLModal">
                                    New HBL <i class="fas fa-plus fa-fw fa-fw"></i>
                                </PrimaryOutlineButton>
                                <PrimaryOutlineButton class="border-red-500 text-red-500 hover:bg-red-500 hover:text-white" type="button" @click="showRemoveHBLModal">
                                    Remove HBL
                                </PrimaryOutlineButton>
                            </div>
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
                                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                                        >
                                            HBL
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
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            {{ item.hbl}}
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
                                            {{ item.weight.toFixed(3) }}
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
                        <span>Update MHBL</span>
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
            <DialogModal :maxWidth="'xl'" :show="showAddNewHBLDialog"
                         @close="closeAddNewHBLModal">
                <template #title>
                    Add New HBL
                </template>

                <template #content>
                    <div class="mt-4">
                        <TextInput
                            v-model="hblNumber"
                            class="w-full"
                            placeholder="Enter HBL Number"
                            required
                            type="text"
                        />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeAddNewHBLModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        @click.prevent="handleAddNewHBL"
                    >
                        Add HBL
                    </PrimaryButton>
                </template>
            </DialogModal>

            <DialogModal :maxWidth="'xl'" :show="showRemoveHBLDialog"
                         @close="closeRemoveHBLModal">
                <template #title>
                    Remove HBL
                </template>

                <template #content>
                    <div class="mt-4">
                        <TextInput
                            v-model="hblNumber"
                            class="w-full"
                            placeholder="Enter HBL Number"
                            required
                            type="text"
                        />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeRemoveHBLModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        class="ms-3 border-red-500 text-red-500 hover:bg-red-500 hover:text-white"
                        @click.prevent="handleRemoveHBL"
                    >
                        Remove HBL
                    </PrimaryButton>
                </template>
            </DialogModal>
        </form>
    </AppLayout>
</template>
