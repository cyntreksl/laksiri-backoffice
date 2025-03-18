<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import TextInput from "@/Components/TextInput.vue";

const confirmingPickupTypeCreation = ref(false);

const closeModal = () => {
    confirmingPickupTypeCreation.value = false;
};

const form = useForm({
    pickup_type_name: "",
});

const createPickupType = () => {
    form.post(route("setting.pickup-types.store"), {
        onSuccess: () => {
            form.reset();
            closeModal();
            router.visit(route("setting.pickup-types.index"));
            push.success("Pickup Type Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="flex justify-end mx-5 mt-4">
        <PrimaryButton
            @click="confirmingPickupTypeCreation = !confirmingPickupTypeCreation"
        >
            Create New Pickup Type
        </PrimaryButton>
    </div>

    <DialogModal
        :maxWidth="'xl'"
        :show="confirmingPickupTypeCreation"
        @close="closeModal"
    >
        <template #title> Create New Pickup Type</template>

        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Pickup Type"/>
                    <TextInput v-model="form.pickup_type_name" class="w-full" placeholder="Pickup Type"/>
                    <InputError :message="form.errors.pickup_type_name"/>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal"> Cancel</SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="createPickupType"
            >
                Create Pickup Type
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
