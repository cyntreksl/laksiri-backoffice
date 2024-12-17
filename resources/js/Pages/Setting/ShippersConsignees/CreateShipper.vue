<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { push } from "notivue";
import TextInput from "@/Components/TextInput.vue";

const confirmShipperCreation = ref(false);

const closeModal = () => (confirmShipperCreation.value = false);

const form = useForm({
    type:"shipper",
    name: "",
    email: "",
    mobile_number:"",
    pp_or_nic_no: "",
    residency_no:""

});

const createShipper = () => {
    form.post(route("setting.shipper-consignees.storeshipper"), {
        onSuccess: () => {
            closeModal();
            form.reset();
            router.visit(route("setting.shipper-consignees.index"));
            push.success("Shipper Created Successfully");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="flex justify-end mx-5 mt-4">
        <PrimaryButton @click="confirmShipperCreation = !confirmShipperCreation">
            Create New Shipper
        </PrimaryButton>
    </div>

    <DialogModal :maxWidth="'xl'" :show="confirmShipperCreation" @close="closeModal">
        <template #title> Create New Shipper </template>
        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Name Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        v-model="form.name"
                        id="name"
                        type="text"
                        class="w-full"
                        placeholder="Enter Name"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Email Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        v-model="form.email"
                        id="email"
                        type="email"
                        class="w-full"
                        placeholder="Enter Email"
                    />
                    <InputError :message="form.errors.email" />
                </div>
                <!-- Mobile Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="mobile_number" value="Email" />
                    <TextInput
                        v-model="form.mobile_number"
                        id="mobile_number"
                        type="text"
                        class="w-full"
                        placeholder="Enter Mobile No"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Passport or NIC Number Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="pp_or_nic_no" value="Passport/NIC Number" />
                    <TextInput
                        v-model="form.pp_or_nic_no"
                        id="pp_or_nic_no"
                        type="text"
                        class="w-full"
                        placeholder="Enter Passport or NIC Number"
                    />
                    <InputError :message="form.errors.pp_or_nic_no" />
                </div>

                <!-- Residency Number Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="residency_no" value="Residency Number" />
                    <TextInput
                        v-model="form.residency_no"
                        id="residency_no"
                        type="text"
                        class="w-full"
                        placeholder="Enter Residency Number"
                    />
                    <InputError :message="form.errors.residency_no" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="createShipper"
            >
                Create Shipper
            </PrimaryButton>
        </template>
    </DialogModal>
</template>


<style scoped></style>
