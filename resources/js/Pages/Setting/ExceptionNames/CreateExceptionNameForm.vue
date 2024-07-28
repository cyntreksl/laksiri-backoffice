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

const confirmingExceptionNameCreation = ref(false);

const closeModal = () => {
    confirmingExceptionNameCreation.value = false;
};

const form = useForm({
    name: "",
});

const createExceptionName = () => {
    form.post(route("setting.exception-names.store"), {
        onSuccess: () => {
            closeModal()
            form.reset();
            router.visit(route("setting.exception-names.index"));
            push.success("Exception Name Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="flex justify-end mx-5 mt-4">
        <PrimaryButton
            @click="confirmingExceptionNameCreation = !confirmingExceptionNameCreation"
        >
            Create New Exception Name
        </PrimaryButton>
    </div>

    <DialogModal
        :maxWidth="'xl'"
        :show="confirmingExceptionNameCreation"
        @close="closeModal"
    >
        <template #title> Create New Exception Name</template>

        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Name"/>
                    <TextInput v-model="form.name" class="w-full" placeholder="Exception Name"/>
                    <InputError :message="form.errors.name"/>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal"> Cancel</SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="createExceptionName"
            >
                Create Exception Name
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
