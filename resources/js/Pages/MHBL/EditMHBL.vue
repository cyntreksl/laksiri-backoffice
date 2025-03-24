<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { computed, reactive, ref, watch } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryOutlineButton from "@/Components/PrimaryOutlineButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import RemovePackageConfirmationModal from "@/Pages/HBL/Partials/RemovePackageConfirmationModal.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { push } from "notivue";
import DialogModal from "@/Components/DialogModal.vue";
import hblImage from "../../../../resources/images/illustrations/hblimage.png";
import HBLDetailModal from "@/Pages/Common/HBLDetailModal.vue";
import { float } from "quill/ui/icons.js";
import { forEach } from "vuedraggable/dist/vuedraggable.common.js";
import Card from 'primevue/card';
import Fieldset from 'primevue/fieldset';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
// import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import SelectButton from 'primevue/selectbutton';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import IftaLabel from 'primevue/iftalabel';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Dropdown from 'primevue/dropdown';
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
    if (form.packages.length > 0) {
        form.put(route("mhbls.update", props.mhbl.id), {
            onSuccess: (page) => {
                form.reset();
                push.success("MHBL Updated Successfully!");
            },
            onError: () => console.log("error"),
            preserveScroll: true,
            preserveState: true,
        });
    } else push.error("Please select HBLs!");

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
    if (!hblNumber.value) {
        closeAddNewHBLModal();
        push.error('Please enter HBL Number!')
    } else {
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

            if (data.mhbl) {
                closeAddNewHBLModal();
                push.error("HBL already added to a MHBL.");
            }

            if (data.cargo_type !== form.cargo_type || data.hbl_type !== 'Door to Door' || data.warehouse === form.warehouse) {
                closeAddNewHBLModal();
                push.error("Selected HBL is not maching to  Primary Details");
            } else {
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

                    const newItem = { ...packageItem };
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
}

const handleRemoveHBL = async () => {
    if (!hblNumber.value) {
        closeRemoveHBLModal();
        push.error('Please enter HBL Number!')
    } else {
        const selectedPackages = packageList.value.filter(pkg => pkg.hbl === hblNumber.value);
        form.grand_weight = form.grand_weight - selectedPackages.reduce((sum, pkg) => sum + pkg.weight, 0);
        form.grand_volume = form.grand_volume - selectedPackages.reduce((sum, pkg) => sum + pkg.volume, 0);
        packageList.value = packageList.value.filter(pkg => pkg.hbl !== hblNumber.value);
        form.hbls = form.hbls.filter(hbl => !selectedPackages.some(pkg => pkg.hbl_id === hbl));
        closeRemoveHBLModal();
        push.success('Remove HBL Successfully!')
    }
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

</script>

<template>
    <AppLayout title="MHBL - Edit">
        <template #header>MHBL - Edit</template>

        <!-- Breadcrumb -->
        <Breadcrumb />

        <!-- Create Pickup Form -->
        <form @submit.prevent="handleMHBLUpdate">
            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-2 grid grid-rows gap-4">
                    <card>
                        <template #title>Primary Details</template>
                        <template #content>
                            <Fieldset legend="Cargo Type (Automatically Selected)">
                                <SelectButton disabled v-model="form.cargo_type" :options="cargoTypes"
                                    name="Cargo Type">
                                    <template #option="slotProps">
                                        <div class="flex items-center">
                                            <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                            <i v-else class="ti ti-plane mr-2"></i>
                                            <span>{{ slotProps.option }}</span>
                                        </div>
                                    </template>
                                </SelectButton>
                                <InputError :message="form.errors.cargo_type" />
                            </Fieldset>
                            <Fieldset legend="Warehouse (Automatically Selected)">
                                <SelectButton disabled v-model="form.warehouse" :options="warehouses" name="HBL Type"
                                    option-label="name" option-value="name" @change="updateWarehouseId" />
                                <InputError :message="form.errors.warehouse" />
                            </Fieldset>
                            <div class="flex justify-center mt-36">
                                <img :src="hblImage" alt="hbl-image" class="w-3/4">
                            </div>
                        </template>
                    </card>

                </div>
                <div class="sm:col-span-2">
                    <card>
                        <template #title>
                            <div class="flex justify-between items-center">
                                <span>Shipper Details</span>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-3 gap-5 mt-3">
                                <div class="col-span-3">
                                    <InputLabel value="Name" />
                                    <Dropdown v-model="form.hbl_name" :options="shippers" optionLabel="name"
                                        optionValue="name" placeholder="Select shipper" class="w-full mt-1.5"
                                        :disabled="true"
                                        inputClass="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent" />
                                    <InputError :message="form.errors.hbl_name" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Email" />
                                    <IconField>
                                        <InputField>
                                            <InputIcon class="pi pi-envelope" />
                                            <InputText v-model="form.email" class="w-full" placeholder="Email"
                                                type="email" :disabled="true" />
                                        </InputField>
                                        <InputError :message="form.errors.email" />
                                    </IconField>
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Mobile Number" />
                                    <div class="flex flex-row">
                                        <Select v-model="countryCode" :options="countryCodes"
                                            class="w-25 !rounded-r-none !border-r-0" filter
                                            placeholder="Select a Country Code" :disabled="true" />
                                        <InputText v-model="contactNumber" class="!rounded-l-none w-full"
                                            placeholder="123 4567 890" :disabled="true" />
                                    </div>
                                    <InputError :message="form.errors.contact_number" class="col-span-1" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="PP or NIC No" />
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.nic" class="w-full" placeholder="PP or NIC No"
                                            :disabled="true" />
                                    </IconField>
                                    <InputError :message="form.errors.nic" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Residency No" />
                                    <IconField>
                                        <InputIcon class="pi pi-home" />
                                        <InputText v-model="form.iq_number" class="w-full" placeholder="Residency No"
                                            :disabled="true" />
                                    </IconField>
                                    <InputError :message="form.errors.iq_number" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Address" />
                                    <Textarea v-model="form.address" class="w-full" cols="30"
                                        placeholder="Type address here..." rows="5" :disabled="true" />
                                    <InputError :message="form.errors.address" />
                                </div>
                                <br>
                            </div>
                        </template>
                    </card>
                </div>

                <div class="sm:col-span-2">
                    <Card>
                        <template #title>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Consignee Details
                            </h2>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-3 gap-5 mt-3">
                                <div class="col-span-3">
                                    <InputLabel value="Name" />
                                    <Dropdown v-model="form.consignee_name" :options="consignees" optionLabel="name"
                                        optionValue="name" placeholder="Select consignee" class="w-full mt-1.5"
                                        :disabled="true"
                                        inputClass="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent" />
                                    <InputError :message="form.errors.consignee_name" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="PP or NIC No" />
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.consignee_nic" class="w-full" placeholder="PP or NIC No"
                                                   :disabled="true" />
                                    </IconField>
                                    <InputError :message="form.errors.consignee_nic" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Mobile Number" />
                                    <div class="flex flex-row">
                                        <Select v-model="consignee_countryCode" :options="countryCodes"
                                                class="w-25 !rounded-r-none !border-r-0" filter
                                                placeholder="Select a Country Code" :disabled="true" />
                                        <InputText v-model="consignee_contact" class="!rounded-l-none w-full"
                                                   placeholder="123 4567 890" :disabled="true" />
                                    </div>
                                    <InputError :message="form.errors.consignee_contact" class="col-span-1" />
                                </div>
                                <div class="col-span-3">
                                    <InputLabel value="Address" />
                                    <Textarea v-model="form.consignee_address" class="w-full" cols="30"
                                              placeholder="Type address here..." rows="5" :disabled="true" />
                                    <InputError :message="form.errors.consignee_address" />
                                </div>
                                <br> <br> <br> <br>  <br>  <br>  <br>  <br>  <br>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-4  ">
                    <Card>
                        <template #content>
                            <!-- Header Section -->
                            <div class="mt-4 flex justify-between items-center ">
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">
                                    Package Details
                                </h2>
                                <div class="flex space-x-3 gap-5 ">
                                    <Button label="New HBL" icon="pi pi-plus" class="p-button-outlined "
                                        @click="showAddHBLModal" />
                                    <Button label="Remove HBL" icon="pi pi-trash" class="p-button-danger"
                                        @click="showRemoveHBLModal" />
                                </div>
                            </div>
                            <br>
                            <!-- Packages Table -->
                            <DataTable v-if="packageList.length > 0" :value="packageList" stripedRows removableSort
                                       class="min-w-50rem border-radius-10 overflow-hidden">
                                <Column field="hbl" header="HBL"/>
                                <Column field="type" header="Type"/>
                                <Column field="length" header="Length (CM)">
                                    <template #body="{ data }">
                                        {{ Number(data.length).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="width" header="Width (CM)">
                                    <template #body="{ data }">
                                        {{ Number(data.width).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="height" header="Height (CM)">
                                    <template #body="{ data }">
                                        {{ Number(data.height).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="quantity" header="Quantity"/>
                                <Column field="weight" header="Weight (KG)">
                                    <template #body="{ data }">
                                        {{ Number(data.weight).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="volume" header="Volume (M³)"/>
                                <Column field="remarks" header="Remarks"/>
                            </DataTable>

                            <!-- Copied Packages Table -->
                            <DataTable v-else-if="Object.keys(copiedPackages).length > 0"
                                       :value="Object.values(copiedPackages)" stripedRows removableSort
                                       class="min-w-50rem border-radius-10 overflow-hidden">
                                <Column field="package_type" header="Type"/>
                                <Column field="length" header="Length (CM)">
                                    <template #body="{ data }">
                                        {{ Number(data.length).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="width" header="Width (CM)">
                                    <template #body="{ data }">
                                        {{ Number(data.width).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="height" header="Height (CM)">
                                    <template #body="{ data }">
                                        {{ Number(data.height).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="quantity" header="Quantity"/>
                                <Column field="weight" header="Weight (KG)">
                                    <template #body="{ data }">
                                        {{ Number(data.weight).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="volume" header="Volume (M³)">
                                    <template #body="{ data }">
                                        {{ Number(data.volume).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="remarks" header="Remarks"/>
                            </DataTable>

                            <!-- Empty State -->
                            <div v-else class="flex flex-col items-center mt-6 mb-6">
                                <i class="pi pi-box text-7xl text-gray-400 mb-4"></i>
                                <p class="text-gray-600">No packages. Please add packages to view data.</p>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="sm:col-span-2 grid grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <Card class="p-4 sm:p-5">
                            <template #title>
                                <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">
                                    MHBL Summary
                                </h2>
                            </template>
                            <template #content>
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
                                            {{ form.grand_weight ? form.grand_weight.toFixed(2) : '0.00' }}
                                        </p>
                                    </div>
                                    <div class="flex justify-between mb-4">
                                        <p class="line-clamp-1">Volume</p>
                                        <p class="text-slate-700 dark:text-navy-100">
                                            {{ form.grand_volume ? form.grand_volume.toFixed(2) : '0.00' }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>

            </div>
            <div class="grid grid-cols-1 sm:grid-cols-6 my-6 gap-4">
                <!-- Empty grid columns for spacing -->
                <div class="col-span-4"></div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-5 col-span-2">
                    <DangerOutlineButton @click="router.visit(route('mhbls.index'))">
                        Cancel
                    </DangerOutlineButton>
                    <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                        class="space-x-2" type="submit">
                        <span>Update MHBL</span>
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </PrimaryButton>
                </div>
            </div>
            <DialogModal :maxWidth="'xl'" :show="showAddNewHBLDialog" @close="closeAddNewHBLModal">
                <template #title>
                    Add New HBL
                </template>

                <template #content>
                    <div class="mt-4">
                        <TextInput v-model="hblNumber" class="w-full" placeholder="Enter HBL Number" required
                            type="text" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeAddNewHBLModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton class="ms-3" @click.prevent="handleAddNewHBL">
                        Add HBL
                    </PrimaryButton>
                </template>
            </DialogModal>

            <DialogModal :maxWidth="'xl'" :show="showRemoveHBLDialog" @close="closeRemoveHBLModal">
                <template #title>
                    Remove HBL
                </template>

                <template #content>
                    <div class="mt-4">
                        <TextInput v-model="hblNumber" class="w-full" placeholder="Enter HBL Number" required
                            type="text" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeRemoveHBLModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton class="ms-3 border-red-500 text-red-500 hover:bg-red-500 hover:text-white"
                        @click.prevent="handleRemoveHBL">
                        Remove HBL
                    </PrimaryButton>
                </template>
            </DialogModal>
        </form>
    </AppLayout>
</template>
