<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {computed, ref} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Select from "primevue/select";

const props = defineProps({
    countryCodes: {
        type: Array,
        default: () => [],
    },
    visible: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits(["update:visible"]);

const countryCode = ref('+94');
const contactNumber = ref("");

const form = useForm({
    type: "shipper",
    name: "",
    email: "",
    mobile_number: computed(() => countryCode.value + contactNumber.value),
    pp_or_nic_no: "",
    residency_no: "",
    address: "",
    description: ""
});

const createShipper = () => {
    form.post(route("setting.shipper-consignees.store"), {
        onSuccess: () => {
            form.reset();
            emit('close');
            router.visit(route("setting.shipper-consignees.index"));
            push.success("Shipper Created Successfully");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '35rem' }" :visible="visible" header="Create New Officer" modal
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
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Officer"
                    type="button"
                    @click="createShipper"></Button>
        </div>
    </Dialog>
</template>
