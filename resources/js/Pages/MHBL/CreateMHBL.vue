<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref, watch} from "vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import hblImage from "../../../../resources/images/illustrations/hblimage.png";
import Fieldset from "primevue/fieldset";
import Card from "primevue/card";
import SelectButton from "primevue/selectbutton";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import Textarea from "primevue/textarea";
import InputLabel from "@/Components/InputLabel.vue";
import Button from "primevue/button";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

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
        type: Number,
        required: true,
    },
    grandWeight: {
        type: Number,
        required: true,
    },
    grandTotal: {
        type: Number,
        required: true,
    },
});

const packageList = ref(props.packages);
const currentBranch = usePage().props?.auth.user.active_branch_name;
const consignee_countryCode = ref('+94');
const contactNumber = ref("");
const consignee_contact = ref("");

const findCountryCodeByBranch = (country) => {
    return usePage().props.currentBranch.country_code;
};

const countryCode = ref(findCountryCodeByBranch(currentBranch));

const form = useForm({
    hbls: props.hblIds,
    hbl_name: null,
    email: "",
    contact_number: computed(() => countryCode.value + contactNumber.value),
    nic: "",
    iq_number: "",
    address: "",
    consignee_name: null,
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
            shipper => newShipper && shipper.name.toLowerCase() === newShipper.toLowerCase()
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
            router.visit(route("mhbls.index"));
            push.success("MHBL Created Successfully!");
        },
        onError: () => console.log("error"),
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <AppLayout title="MHBL Create">
        <template #header>MHBL - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <form @submit.prevent="handleMHBLCreate">
            <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                <div class="sm:col-span-2 grid grid-rows gap-4">
                    <Card>
                        <template #title>Primary Details</template>
                        <template #subtitle>Automatically selected the options below.</template>
                        <template #content>
                            <Fieldset legend="Cargo Type">
                                <SelectButton v-model="form.cargo_type" :options="cargoTypes" disabled name="Cargo Type">
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

                            <Fieldset hidden legend="Type">
                                <SelectButton v-model="form.hbl_type" :options="hblTypes" disabled name="HBL Type"/>
                                <InputError :message="form.errors.hbl_type"/>
                            </Fieldset>

                            <Fieldset legend="Warehouse">
                                <SelectButton v-model="form.warehouse" :options="warehouses" disabled name="HBL Type" option-label="name" option-value="name"/>
                                <InputError :message="form.errors.warehouse" />
                            </Fieldset>

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
                                    <Select v-model="form.hbl_name" :options="shippers" class="w-full" filter option-label="name" option-value="name" placeholder="Select shipper" />
                                    <InputError :message="form.errors.hbl_name"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Email"/>
                                    <IconField>
                                        <InputIcon class="pi pi-envelope" />
                                        <InputText v-model="form.email" class="w-full"
                                                   disabled placeholder="Email" type="email"/>
                                    </IconField>
                                    <InputError :message="form.errors.email"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Mobile Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" disabled filter placeholder="Select a Country Code"/>
                                        <InputText v-model="contactNumber" class="!rounded-l-none w-full" disabled placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.contact_number" class="col-span-1"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="PP or NIC No"/>
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.nic" class="w-full"
                                                   disabled placeholder="PP or NIC No"/>
                                    </IconField>
                                    <InputError :message="form.errors.nic"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Residency No"/>
                                    <IconField>
                                        <InputIcon class="pi pi-home" />
                                        <InputText v-model="form.iq_number" class="w-full"
                                                   disabled placeholder="Residency No"/>
                                    </IconField>
                                    <InputError :message="form.errors.iq_number"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Address"/>
                                    <Textarea v-model="form.address" class="w-full" cols="30" disabled placeholder="Type address here..." rows="5"/>
                                    <InputError :message="form.errors.address"/>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="sm:col-span-2 grid grid-rows">
                    <Card>
                        <template #title>
                            Consignee Details
                        </template>
                        <template #content>
                            <div class="grid grid-cols-3 gap-5 mt-3">
                                <div class="col-span-3">
                                    <InputLabel value="Name"/>
                                    <Select v-model="form.consignee_name" :options="consignees" class="w-full" filter option-label="name" option-value="name" placeholder="Select Consignee" />
                                    <InputError :message="form.errors.consignee_name"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="PP or NIC No"/>
                                    <IconField>
                                        <InputIcon class="pi pi-tag" />
                                        <InputText v-model="form.consignee_nic" class="w-full"
                                                   disabled placeholder="PP or NIC No"/>
                                    </IconField>
                                    <InputError :message="form.errors.consignee_nic"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Mobile Number"/>
                                    <div class="flex flex-row">
                                        <Select v-model="consignee_countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" disabled filter placeholder="Select a Country Code"/>
                                        <InputText v-model="consignee_contact" class="!rounded-l-none w-full" disabled placeholder="123 4567 890"/>
                                    </div>
                                    <InputError :message="form.errors.consignee_contact" class="col-span-1"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Address"/>
                                    <Textarea v-model="form.consignee_address" class="w-full" cols="30" disabled placeholder="Type address here..." rows="5"/>
                                    <InputError :message="form.errors.consignee_address"/>
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Note"/>
                                    <Textarea v-model="form.consignee_note" class="w-full" cols="30" disabled placeholder="Type note here..." rows="3"/>
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
                            Package Details
                        </template>
                        <template #content>
                            <DataTable v-if="packageList.length > 0" :value="packageList" tableStyle="min-width: 50rem">
                                <Column field="type" header="Type"></Column>
                                <Column field="length" header="Length (CM)">
                                    <template #body="slotProps">
                                        {{ slotProps.data.length.toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="width" header="Width">
                                    <template #body="slotProps">
                                        {{ slotProps.data.width.toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="height" header="Height">
                                    <template #body="slotProps">
                                        {{ slotProps.data.height.toFixed(3) }}
                                    </template>
                                </Column>
                                <Column field="quantity" header="Quantity"></Column>
                                <Column field="totalWeight" header="Weight">
                                    <template #body="slotProps">
                                        {{ slotProps.data.totalWeight.toFixed(3) }}
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
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="sm:col-span-2 grid-cols-2 grid gap-4 space-y-5">
                    <!-- Price & Payment -->
                    <div class="sm:col-span-2 space-y-5">
                        <Card>
                            <template #title>
                                MHBL Summary
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
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-6 my-6 gap-4">
                <!-- Empty grid columns for spacing -->
                <div class="col-span-4"></div>

                <div class="flex justify-end space-x-5 col-span-2">
                    <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('mhbls.index'))" />

                    <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Create a MHBL" type="submit" />
                </div>
            </div>
        </form>
    </AppLayout>
</template>
