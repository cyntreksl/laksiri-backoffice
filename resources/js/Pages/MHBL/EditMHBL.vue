<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { computed, reactive, ref, watch } from "vue";
import InputError from "@/Components/InputError.vue";
import { push } from "notivue";
import hblImage from "../../../../resources/images/illustrations/hblimage.png";
import { float } from "quill/ui/icons.js";
import Card from 'primevue/card';
import Fieldset from 'primevue/fieldset';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import SelectButton from 'primevue/selectbutton';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
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
        push.error('Please enter HBL Number!');
        return;
    }

    if (form.hbls.includes(hblNumber.value)) {
        closeAddNewHBLModal();
        push.error("HBL Number is already added.");
        return;
    }

    try {
        const response = await fetch(`/mhbls/add-hbl`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({ hbl_number: hblNumber.value }),
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        }

        const data = await response.json();

        if (!data.hbl_number || data.hbl_number !== hblNumber.value) {
            closeAddNewHBLModal();
            push.error("HBL Not Found!");
            return;
        }

        if (form.hbls.includes(data.id)) {
            closeAddNewHBLModal();
            push.error("HBL ID is already linked to this shipment.");
            return;
        }

        if (data.cargo_type !== form.cargo_type || data.hbl_type !== 'Door to Door' || data.warehouse === form.warehouse) {
            closeAddNewHBLModal();
            push.error("Selected HBL does not match the Primary Details.");
            return;
        }

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

            packageList.value.push({ ...packageItem });
            form.grand_weight += packageItem.weight;
            form.grand_volume += packageItem.volume;
        }

        form.hbls.push(data.id);
        closeAddNewHBLModal();
        push.success('HBL added successfully!');

    } catch (error) {
        push.error("An error occurred while adding HBL.");
        console.error("Error adding HBL:", error);
    }
};

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
                <!-- Primary Details Card -->
                <div class="sm:col-span-2">
                    <Card>
                        <template #title>Primary Details</template>
                        <template #subtitle>Automatically selected the options below.</template>
                        <template #content>
                            <Fieldset legend="Cargo Type">
                                <SelectButton v-model="form.cargo_type" :options="cargoTypes" disabled name="Cargo Type">
                                    <template #option="slotProps">
                                        <div class="flex items-center">
                                            <i :class="slotProps.option === 'Sea Cargo' ? 'ti ti-ship' : 'ti ti-plane'" class="mr-2"></i>
                                            <span>{{ slotProps.option }}</span>
                                        </div>
                                    </template>
                                </SelectButton>
                                <InputError :message="form.errors.cargo_type"/>
                            </Fieldset>

                            <Fieldset v-if="false" legend="Type">
                                <SelectButton v-model="form.hbl_type" :options="hblTypes" disabled name="HBL Type"/>
                                <InputError :message="form.errors.hbl_type"/>
                            </Fieldset>

                            <Fieldset legend="Warehouse">
                                <SelectButton v-model="form.warehouse" :options="warehouses" disabled option-label="name" option-value="name"/>
                                <InputError :message="form.errors.warehouse" />
                            </Fieldset>

                            <div class="flex justify-center mt-16">
                                <img :src="hblImage" alt="hbl-image" class="w-3/4">
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Shipper Details Card -->
                <div class="sm:col-span-2">
                    <Card>
                        <template #title>Shipper Details</template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-4 mt-3">
                                <div>
                                    <InputLabel value="Name"/>
                                    <Select v-model="form.hbl_name" :options="shippers" class="w-full" filter option-label="name" option-value="name" placeholder="Select shipper" disabled />
                                    <InputError :message="form.errors.hbl_name"/>
                                </div>
                                <div>
                                    <InputLabel value="Email" />
                                    <IconField>
                                        <InputField>
                                            <InputIcon class="pi pi-envelope" />
                                            <InputText v-model="form.email" class="w-full" placeholder="Email" type="email" disabled />
                                        </InputField>
                                        <InputError :message="form.errors.email" />
                                    </IconField>
                                </div>
                                <div>
                                    <InputLabel value="Mobile Number" />
                                    <div class="flex">
                                        <Select v-model="countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" disabled />
                                        <InputText v-model="contactNumber" class="!rounded-l-none w-full" placeholder="123 4567 890" disabled />
                                    </div>
                                    <InputError :message="form.errors.contact_number" />
                                </div>
                                <div>
                                    <InputLabel value="PP or NIC No" />
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.nic" class="w-full" placeholder="PP or NIC No" disabled />
                                    </IconField>
                                    <InputError :message="form.errors.nic" />
                                </div>
                                <div>
                                    <InputLabel value="Residency No" />
                                    <IconField>
                                        <InputIcon class="pi pi-home" />
                                        <InputText v-model="form.iq_number" class="w-full" placeholder="Residency No" disabled />
                                    </IconField>
                                    <InputError :message="form.errors.iq_number" />
                                </div>
                                <div>
                                    <InputLabel value="Address" />
                                    <Textarea v-model="form.address" class="w-full" cols="30" placeholder="Type address here..." rows="5" disabled />
                                    <InputError :message="form.errors.address" />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Consignee Details Card -->
                <div class="sm:col-span-2">
                    <Card>
                        <template #title>Consignee Details</template>
                        <template #content>
                            <div class="grid grid-cols-1 gap-4 mt-3">
                                <div>
                                    <InputLabel value="Name"/>
                                    <Select v-model="form.consignee_name" :options="consignees" class="w-full" filter option-label="name" option-value="name" placeholder="Select Consignee" disabled />
                                    <InputError :message="form.errors.consignee_name"/>
                                </div>
                                <div>
                                    <InputLabel value="PP or NIC No" />
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.consignee_nic" class="w-full" placeholder="PP or NIC No" disabled />
                                    </IconField>
                                    <InputError :message="form.errors.consignee_nic" />
                                </div>
                                <div>
                                    <InputLabel value="Mobile Number" />
                                    <div class="flex">
                                        <Select v-model="consignee_countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" disabled />
                                        <InputText v-model="consignee_contact" class="!rounded-l-none w-full" placeholder="123 4567 890" disabled />
                                    </div>
                                    <InputError :message="form.errors.consignee_contact" />
                                </div>
                                <div>
                                    <InputLabel value="Address" />
                                    <Textarea v-model="form.consignee_address" class="w-full" cols="30" placeholder="Type address here..." rows="5" disabled />
                                    <InputError :message="form.errors.consignee_address" />
                                </div>
                                <div>
                                    <div class="h-34"></div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Package Details and Summary Section -->
            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-4">
                    <Card>
                        <template #title>
                            <div class="flex justify-between items-center">
                                <span>Package Details</span>
                                <div class="flex gap-5">
                                    <Button label="New HBL" icon="pi pi-plus" class="p-button-outlined" @click="showAddHBLModal" />
                                    <Button label="Remove HBL" icon="pi pi-trash" class="p-button-danger" @click="showRemoveHBLModal" />
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <!-- Packages Table -->
                            <DataTable v-if="packageList.length > 0" :value="packageList" stripedRows removableSort
                                       class="min-w-50rem border-radius-10 overflow-hidden mt-4">
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
                                <Column field="volume" header="Volume (M.CU)"/>
                                <Column field="remarks" header="Remarks"/>
                            </DataTable>

                            <!-- Copied Packages Table -->
                            <DataTable v-else-if="Object.keys(copiedPackages).length > 0"
                                       :value="Object.values(copiedPackages)" stripedRows removableSort
                                       class="min-w-50rem border-radius-10 overflow-hidden mt-4">
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
                                <Column field="volume" header="Volume (M.CU)">
                                    <template #body="{ data }">
                                        {{ Number(data.volume).toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="remarks" header="Remarks"/>
                            </DataTable>

                            <!-- Empty State -->
                            <div v-else class="text-center py-8">
                                <i class="pi pi-box text-purple-300 animate-slow-bounce" style="font-size: 8rem"></i>
                                <p class="text-gray-600 mt-4">
                                    No packages. Please add packages to view data.
                                </p>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- MHBL Summary Card -->
                <div class="sm:col-span-2">
                    <Card>
                        <template #title>MHBL Summary</template>
                        <template #content>
                            <div class="grid grid-cols-2 gap-5 mt-5">
                                <div class="flow-root col-span-2 my-3">
                                    <ul class="-my-6" role="list">
                                        <li class="flex py-3">
                                            <div class="flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                        <h3>
                                                            Packages
                                                        </h3>
                                                        <p class="ml-4">{{ packageList.length }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="flex py-3">
                                            <div class="flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                        <h3>
                                                            Weight
                                                        </h3>
                                                        <p class="ml-4">{{ form.grand_weight.toFixed(2) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="flex py-3">
                                            <div class="flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                        <h3>
                                                            Volume
                                                        </h3>
                                                        <p class="ml-4">{{ form.grand_volume.toFixed(3) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-5 my-6">
                <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('mhbls.index'))" />
                <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create a MHBL" type="submit" />
            </div>

            <!-- Add New HBL Dialog -->
            <Dialog v-model:visible="showAddNewHBLDialog" modal header="Add New HBL" :style="{ width: '30vw' }" :blockScroll="true">
                <div class="mt-4">
                    <InputText v-model="hblNumber" class="w-full p-inputtext" placeholder="Enter HBL Number" required type="text" />
                </div>

                <template #footer>
                    <Button label="Cancel" class="p-button-text" @click="closeAddNewHBLModal" />
                    <Button label="Add HBL" class="p-button-primary ms-3" icon="pi pi-plus" @click.prevent="handleAddNewHBL" />
                </template>
            </Dialog>

            <!-- Remove HBL Dialog -->
            <Dialog v-model:visible="showRemoveHBLDialog" modal header="Remove HBL" :style="{ width: '30vw' }" :blockScroll="true">
                <div class="mt-4">
                    <InputText v-model="hblNumber" class="w-full p-inputtext" placeholder="Enter HBL Number" required type="text" />
                </div>
                <template #footer>
                    <Button label="Cancel" class="p-button-text" @click="closeRemoveHBLModal" />
                    <Button label="Remove HBL" class="p-button-danger ms-3" icon="pi pi-trash" @click.prevent="handleRemoveHBL" />
                </template>
            </Dialog>
        </form>
    </AppLayout>
</template>

<style>
.h-34 {
height: 8.7rem;
}
</style>
