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
    mobile_number: "",
    nic: "",
});

const createShipper = () => {
    form.post(route("setting.shipper.store"), {
        onSuccess: () => {
            closeModal();
            form.reset();
            router.visit(route(""));
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

                <!-- Mobile Number Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="mobile_number" value="Mobile Number" />
                    <TextInput
                        v-model="form.mobile_number"
                        id="mobile_number"
                        type="tel"
                        class="w-full"
                        placeholder="Enter Mobile Number"
                    />
                    <InputError :message="form.errors.mobile_number" />
                </div>

                <!-- NIC Field -->
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel for="nic" value="NIC" />
                    <TextInput
                        v-model="form.nic"
                        id="nic"
                        type="text"
                        class="w-full"
                        placeholder="Enter NIC"
                    />
                    <InputError :message="form.errors.nic" />
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
