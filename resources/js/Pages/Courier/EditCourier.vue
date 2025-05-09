<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, reactive, ref, watch} from "vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import Button from "primevue/button";
import hblImage from "../../../images/illustrations/hblimage.png";
import IconField from "primevue/iconfield";
import Select from "primevue/select";
import SelectButton from "primevue/selectbutton";
import InputIcon from "primevue/inputicon";
import Card from "primevue/card";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import Textarea from "primevue/textarea";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import InputNumber from "primevue/inputnumber";
import {useConfirm} from "primevue/useconfirm";

const props = defineProps({
    courier: {
        type: Object,
        default: () => ({}),
    },
    hblTypes: {
        type: Object,
        default: () => ({}),
    },
    cargoTypes: {
        type: Object,
        default: () => ({}),
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
        default: () => ({}),
    },
});

const showAddNewPackageDialog = ref(false);
const editMode = ref(false);
const editIndex = ref(null);
const packageList = ref(props.courier.packages ?? []);
const packageItemVolume = ref(0);
const selectedType = ref("");
const packageItemLength = ref(0);
const packageItemWidth = ref(0);
const packageItemHeight = ref(0);
const confirm = useConfirm();

const splitCountryCode = (fullNumber) => {
    if (fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return code;
            }
        }
    }
    return '+94';
};

const splitContactNumber = (fullNumber) => {
    if(fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return fullNumber.slice(code.length);
            }
        }
    } else return "";
}

const countryCode = ref(splitCountryCode(props.courier.contact_number));
const consignee_countryCode = ref(splitCountryCode(props.courier.consignee_contact));
const contactNumber = ref(splitContactNumber(props.courier.contact_number));
const consignee_contact = ref(splitContactNumber(props.courier.consignee_contact));

const form = useForm({
    cargo_type: props.courier.cargo_type,
    hbl_type: props.courier.hbl_type,
    courier_agent: props.courier.courier_agent,
    name: props.courier.name,
    email: props.courier.email,
    contact_number: computed(() => countryCode.value + contactNumber.value),
    nic: props.courier.nic,
    iq_number: props.courier.iq_number,
    address: props.courier.address,
    consignee_name: props.courier.consignee_name,
    consignee_nic: props.courier.consignee_nic,
    consignee_contact: computed(
        () => consignee_countryCode.value + consignee_contact.value
    ),
    consignee_address: props.courier.consignee_address,
    consignee_note: props.courier.consignee_note,
    packages: props.courier.packages
});

const handleCourierUpdate = () => {
    if(Object.keys(form.packages).length <= 0){
        push.error("Please add at least one package.");
        return;
    }else{
        form.put(route("couriers.update",props.courier.id), {
            onSuccess: (page) => {
                push.success("Courier Updated Successfully!");
                router.visit(route("couriers.index"));
            },
            onError: () => console.log("error"),
            preserveScroll: true,
            preserveState: true,
        });
    }
};

const showPackageDialog = () => {
    showAddNewPackageDialog.value = true;
    if (!editMode.value) {
        selectedType.value = "";
    }
};

const packageItem = reactive({
    package_type: props.packageTypes.find(
        type => type.name.toLowerCase() === 'carton'.toLowerCase()
    )?.name || "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 1,
    volume: 0,
    weight: 0,
    remarks: "",
    measure_type: "cm",
});

const grandTotalWeight = computed(() => {
    return form.packages.reduce((acc, pack) => {
        return Number(acc) + Number(pack.weight);
    }, 0).toFixed(2);
});

const grandTotalVolume = computed(() => {
    return form.packages.reduce((acc, pack) => {
        return Number(acc) + Number(pack.volume);
    }, 0).toFixed(3);
});

const addPackageData = () => {
    if (
        !packageItem.package_type ||
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
        packageItem.volume = parseFloat(packageItem.volume) || 0;
        packageList.value.splice(editIndex.value, 1, {...packageItem});
        grandTotalWeight.value = packageList.value.reduce(
            (accumulator, currentValue) => accumulator + parseFloat(currentValue.weight),
            0
        );
        grandTotalVolume.value = packageList.value.reduce(
            (accumulator, currentValue) => accumulator + parseFloat(currentValue.volume),
            0
        );

    } else {
        const newItem = {...packageItem};
        newItem.volume = parseFloat(newItem.volume) || 0;
        packageList.value.push(newItem); // Add the new item to packageList
        form.packages = packageList.value;

        grandTotalWeight.value += parseFloat(newItem.weight);
        grandTotalVolume.value += parseFloat(newItem.volume.toFixed(3));
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
        packageItem.volume = Number((newLength * newWidth * newHeight * newQuantity).toFixed(3));
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

const updateTypeDescription = () => {
    packageItem.package_type = (packageItem.package_type ? " " : "") + selectedType.value;
};

// remove package
const confirmRemovePackage = (index) => {
    confirm.require({
        message: 'Would you like to remove this package record?',
        header: 'Remove Package?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Remove Package',
            severity: 'danger'
        },
        accept: () => {
            handleRemovePackage(index)
        },
        reject: () => {
        }
    });
};

const handleRemovePackage = (index) => {
    if (index !== null)  {
        grandTotalVolume.value -= packageList.value[index].volume;
        grandTotalWeight.value -= packageList.value[index].totalWeight;
        packageList.value.splice(index, 1);
        form.packages = packageList.value;
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
    packageItem.weight = 0;
    packageItem.remarks = "";
    packageItem.packageRule = 0;
};

const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPackageDialog.value = true;
    selectedType.value = packageList.value[index].package_type;
    // populate packageItem with existing data for editing
    Object.assign(packageItem, packageList.value[index]);
    const factor = conversionFactors[packageItem.measure_type] || 1;
    packageItem.length = packageItem.length/factor;
    packageItem.width = packageItem.width/factor;
    packageItem.height = packageItem.height/factor;
};

const handleCopyShipper = () => {
    form.consignee_name = form.name;
    form.consignee_nic = form.nic;
}

const conversionFactors = {
    cm: 1,
    m: 100,
    in: 2.54,
    ft: 30.48,
};

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

const onDialogShow = () => {
    document.body.classList.add('p-overflow-hidden');
};

const onDialogHide = () => {
    document.body.classList.remove('p-overflow-hidden');
};
</script>

<template>
    <AppLayout title="Edit Courier">
        <template #header>Edit Courier</template>

        <Breadcrumb/>

        <form @submit.prevent="handleCourierUpdate">
            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-2 grid grid-rows gap-4">
                    <Card>
                        <template #title>Primary Details</template>
                        <template #content>
                            <div class="grid grid-cols-3 gap-5 mt-3">

                                <div class="col-span-3">
                                    <InputLabel class="mb-2" value="Cargo Type"/>
                                    <SelectButton v-model="form.cargo_type" :options="cargoTypes" name="Cargo Type">
                                        <template #option="slotProps">
                                            <div class="flex items-center">
                                                <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                                <i v-else class="ti ti-plane mr-2"></i>
                                                <span>{{ slotProps.option }}</span>
                                            </div>
                                        </template>
                                    </SelectButton>
                                    <InputError :message="form.errors.cargo_type"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel class="mb-2" value="Type"/>
                                    <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type"/>
                                    <InputError :message="form.errors.hbl_type"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel class="mb-2" value="Courier Agent"/>
                                    <Select v-model="form.courier_agent" :options="courierAgents" class="w-full"
                                            option-label="company_name" option-value="id"
                                            placeholder="Select a Courier Agent"/>
                                    <InputError :message="form.errors.courier_agent" class="col-span-1"/>
                                </div>
                            </div>

                            <div class="flex justify-center mt-16">
                                <img :src="hblImage" alt="hbl-image" class="w-3/4">
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="sm:col-span-2">
                    <Card>
                        <template #title>
                            Shipper Details
                        </template>
                        <template #content>
                            <div class="grid grid-cols-3 gap-5 mt-3">
                                <div class="col-span-3">
                                    <InputLabel value="Name"/>
                                    <IconField>
                                        <InputIcon class="pi pi-user" />
                                        <InputText v-model="form.name" class="w-full" placeholder="Name" />
                                    </IconField>
                                    <InputError :message="form.errors.name"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Email"/>
                                    <IconField>
                                        <InputIcon class="pi pi-envelope" />
                                        <InputText v-model="form.email" class="w-full"
                                                   placeholder="Email" type="email" />
                                    </IconField>
                                    <InputError :message="form.errors.email"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Mobile Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                        <InputText v-model="contactNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.contact_number" class="col-span-1"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="PP or NIC No"/>
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.nic" class="w-full"
                                                   placeholder="PP or NIC No" />
                                    </IconField>
                                    <InputError :message="form.errors.nic"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Residency No"/>
                                    <IconField>
                                        <InputIcon class="pi pi-home" />
                                        <InputText v-model="form.iq_number" class="w-full"
                                                   placeholder="Residency No" />
                                    </IconField>
                                    <InputError :message="form.errors.iq_number"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Address"/>
                                    <Textarea v-model="form.address" class="w-full" cols="30" placeholder="Type address here..." rows="5" />
                                    <InputError :message="form.errors.address"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="sm:col-span-2 grid grid-rows">
                    <Card>
                        <template #title>
                            <div class="flex justify-between items-center">
                                <span>Consignee Details</span>
                                <div class="flex space-x-1">
                                    <Button v-if="form.name" aria-label="Copy Shipper" icon="pi pi-clone" rounded size="large" variant="text" x-tooltip.placement.bottom="'Copy Shipper'"
                                            @click.prevent="handleCopyShipper" />
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-3 gap-5 mt-3">
                                <div class="col-span-3">
                                    <InputLabel value="Name"/>
                                    <IconField>
                                        <InputIcon class="pi pi-user" />
                                        <InputText v-model="form.consignee_name" class="w-full" placeholder="Name" />
                                    </IconField>
                                    <InputError :message="form.errors.consignee_name"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="PP or NIC No"/>
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.consignee_nic" class="w-full"
                                                   placeholder="PP or NIC No" />
                                    </IconField>
                                    <InputError :message="form.errors.consignee_nic"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Mobile Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="consignee_countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                        <InputText v-model="consignee_contact" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.consignee_contact" class="col-span-1"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Address"/>
                                    <Textarea v-model="form.consignee_address" class="w-full" cols="30" placeholder="Type address here..." rows="5" />
                                    <InputError :message="form.errors.consignee_address"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Note"/>
                                    <Textarea v-model="form.consignee_note" class="w-full" cols="30" placeholder="Type note here..." rows="3" />
                                    <InputError :message="form.errors.consignee_note"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-4">
                    <Card>
                        <template #title>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <span>Package Details</span>
                                </div>
                                <Button icon="pi pi-plus" label="New Package" severity="help" type="button" variant="outlined"
                                        @click="showPackageDialog" />
                            </div>
                        </template>
                        <template #content>
                            <DataTable v-if="packageList.length > 0" :value="packageList" tableStyle="min-width: 50rem">
                                <Column header="Actions">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-times" rounded severity="danger" size="small" variant="text" @click.prevent="confirmRemovePackage(slotProps.index)" />

                                        <Button icon="pi pi-pencil" rounded size="small" variant="text" @click.prevent="openEditModal(slotProps.index)"  />
                                    </template>
                                </Column>
                                <Column field="type" header="Type"></Column>
                                <Column field="length" header="Length (CM)">
                                    <template #body="slotProps">
                                        {{ slotProps.data.length }}
                                    </template>
                                </Column>
                                <Column field="width" header="Width">
                                    <template #body="slotProps">
                                        {{ slotProps.data.width }}
                                    </template>
                                </Column>
                                <Column field="height" header="Height">
                                    <template #body="slotProps">
                                        {{ slotProps.data.height }}
                                    </template>
                                </Column>
                                <Column field="quantity" header="Quantity"></Column>
                                <Column field="totalWeight" header="Weight">
                                    <template #body="slotProps">
                                        {{ slotProps.data.weight }}
                                    </template>
                                </Column>
                                <Column field="volume" header="Volume (M.CU)"></Column>
                                <Column field="remarks" header="Remark"></Column>
                            </DataTable>

                            <div v-if="packageList.length === 0"
                                 class="text-center">
                                <div class="text-center mb-4">
                                    <i class="pi pi-box text-purple-300 animate-slow-bounce" style="font-size: 8rem"></i>
                                    <p class="text-gray-600">
                                        No packages. Please add packages to view data.
                                    </p>
                                </div>
                                <Button icon="pi pi-plus" label="New Package" severity="help" type="button" variant="outlined"
                                        @click="showPackageDialog" />
                            </div>
                        </template>
                    </Card>
                </div>
                <div class="sm:col-span-2">
                    <Card>
                        <template #title>
                            Summary
                        </template>
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
                                                        <p class="ml-4">{{ grandTotalWeight }}</p>
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
                                                        <p class="ml-4">{{ grandTotalVolume }}</p>
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

            <div class="grid grid-cols-1 sm:grid-cols-6 my-6 gap-4">
                <!-- Empty grid columns for spacing -->
                <div class="col-span-4"></div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-5 col-span-2">
                    <Button label="Cancel" severity="secondary" variant="outlined"  @click="router.visit(route('couriers.index'))" />

                    <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Update a Courier" type="submit" />
                </div>
            </div>
        </form>

        <Dialog v-model:visible="showAddNewPackageDialog" :header="editMode ? `Edit Package` : `Add New Package`" :style="{ width: '60rem' }" block-scroll maximizable modal position="bottom" @hide="onDialogHide" @show="onDialogShow">

            <span class="text-surface-500 dark:text-surface-400 block mb-4">{{ !editMode ? "Add new package to Courier" : "" }}</span>

            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-4 md:col-span-1">
                    <InputLabel value="Type"/>
                    <Select v-model="selectedType" :options="packageTypes" class="w-full" filter option-label="name" option-value="name" placeholder="Choose One" @change="updateTypeDescription" />
                </div>

                <div class="col-span-4 md:col-span-2">
                    <InputLabel>
                        Type Description
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputText v-model="packageItem.type" class="w-full" placeholder="Sofa set"/>
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Measure Type
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <Select v-model="packageItem.measure_type" :options="measureTypes" class="w-full" placeholder="Choose One"/>
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Length
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber v-model="packageItem.length" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                    <Message severity="secondary" size="small" variant="simple">{{ packageItemLength.toFixed(2) }} cm</Message>
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Width
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber v-model="packageItem.width" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                    <Message severity="secondary" size="small" variant="simple">{{ packageItemWidth.toFixed(2) }} cm</Message>
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Height
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber v-model="packageItem.height" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                    <Message severity="secondary" size="small" variant="simple">{{ packageItemHeight.toFixed(2) }} cm</Message>
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Quantity
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber v-model="packageItem.quantity" class="w-full" min="0" placeholder="1" step="1"/>
                </div>

                <div class="col-span-2">
                    <InputLabel>
                        Volume ({{volumeUnit }})
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber v-model="packageItem.volume" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" placeholder="1.00" step="0.001"/>
                    <Message severity="secondary" size="small" variant="simple">{{packageItemVolume}} M.CU</Message>
                </div>

                <div class="col-span-2">
                    <InputLabel value="Total Weight" />
                    <InputNumber v-model="packageItem.weight" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0" placeholder="1" step="1"/>
                </div>

                <div class="col-span-4">
                    <InputLabel value="Remarks" />
                    <Textarea v-model="packageItem.remarks" class="w-full" cols="30" placeholder="Type Remarks..." rows="4" />
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" severity="secondary" text @click="closeAddPackageModal" />
                <Button :label="editMode ? `Edit Package` : `Add Package`" severity="help" @click="addPackageData" />
            </template>
        </Dialog>
    </AppLayout>
</template>
