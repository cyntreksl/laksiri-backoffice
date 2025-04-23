<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {computed, ref, watch} from "vue";
import Button from "primevue/button";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Dialog from "primevue/dialog";

const props = defineProps({
    officer: {
        type: Object,
        default: () => {
        },
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },
    visible: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(["update:visible"]);

const splitCountryCode = (fullNumber) => {
    if (fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return code;
            }
        }
    }
};

const splitContactNumber = (fullNumber) => {
    if (fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return fullNumber.slice(code.length);
            }
        }
    }
};

const countryCode = ref(splitCountryCode(props.officer.mobile_number));
const contactNumber = ref(splitContactNumber(props.officer.mobile_number));

const form = useForm({
    id: props.officer.id,
    name: props.officer.name,
    type: props.officer.type,
    email: props.officer.email,
    mobile_number:computed(() => countryCode.value + contactNumber.value),
    pp_or_nic_no: props.officer.pp_or_nic_no,
    residency_no: props.officer.residency_no,
    address:props.officer.address,
    description:props.officer.description,
});

watch(() => props.officer, (newOfficer) => {
    if (newOfficer) {
        form.id = newOfficer.id;
        form.name = newOfficer.name;
        form.type = newOfficer.type;
        form.email = newOfficer.email;
        form.pp_or_nic_no = newOfficer.pp_or_nic_no;
        form.residency_no = newOfficer.residency_no;
        form.address = newOfficer.address;
        form.description = newOfficer.description;

        // Update mobile number components
        countryCode.value = splitCountryCode(newOfficer.mobile_number) || props.countryCodes[0] || '';
        contactNumber.value = splitContactNumber(newOfficer.mobile_number) || '';
    }
}, { immediate: true, deep: true });

const updateOfficer = () => {
    form.put(route("setting.shipper-consignees.update", props.officer.id), {
        onSuccess: () => {
            router.visit(route("setting.shipper-consignees.index"));
            form.reset();
            emit('close');
            push.success("Officer Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '35rem' }" :visible="visible" header="Edit Officer" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-1 gap-5">
            <div>
                <InputLabel for="type" value="Type"/>
                <Select v-model="form.type" :options="['shipper', 'consignee']" class="w-full" placeholder="Select a Type" >
                    <template #option="slotProps">
                        {{ slotProps.option.toUpperCase() }}
                    </template>
                    <template #value="slotProps">
                        {{ slotProps.value.toUpperCase() }}
                    </template>
                </Select>
                <InputError :message="form.errors.type"/>
            </div>

            <div>
                <InputLabel for="name" value="Name"/>
                <InputText v-model="form.name" class="w-full" placeholder="Enter Officer Name" type="text"/>
                <InputError :message="form.errors.name"/>
            </div>

            <div v-if="form.type === 'shipper'">
                <InputLabel for="email" value="Email"/>
                <InputText v-model="form.email" class="w-full" placeholder="Enter Officer Email" type="email"/>
                <InputError :message="form.errors.email"/>
            </div>

            <div>
                <InputLabel for="mobile_number" value="Mobile Number"/>
                <div class="flex w-full">
                    <Select
                        v-model="countryCode"
                        :options="countryCodes"
                        class="!rounded-r-none !border-r-0 w-1/4"
                        filter
                        placeholder="Select a Country Code"
                    />
                    <InputText
                        v-model="contactNumber"
                        class="!rounded-l-none w-3/4"
                        placeholder="123 4567 890"
                    />
                </div>
                <InputError :message="form.errors.mobile_number"/>
            </div>

            <div>
                <InputLabel for="pp_or_nic_no" value="PP or NIC No"/>
                <InputText v-model="form.pp_or_nic_no" class="w-full" placeholder="Enter Officer PP or NIC No"
                           type="text"/>
                <InputError :message="form.errors.pp_or_nic_no"/>
            </div>

            <div v-if="form.type === 'shipper'">
                <InputLabel for="residency_no" value="Residency No"/>
                <InputText v-model="form.residency_no" class="w-full" placeholder="Enter Residency No" type="text"/>
                <InputError :message="form.errors.residency_no"/>
            </div>

            <div>
                <InputLabel for="address" value="Address"/>
                <Textarea v-model="form.address" class="w-full" cols="30" placeholder="Type address here..." rows="4"/>
                <InputError :message="form.errors.address"/>
            </div>

            <div v-if="form.type === 'consignee'">
                <InputLabel for="note" value="Note"/>
                <Textarea v-model="form.description" class="w-full" cols="30" placeholder="Type note here..." rows="4"/>
                <InputError :message="form.errors.description"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Save Officer"
                    type="button"
                    @click="updateOfficer"></Button>
        </div>
    </Dialog>
</template>

