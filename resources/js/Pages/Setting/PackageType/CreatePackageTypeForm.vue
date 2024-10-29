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

const confirmingPackageTypeCreation = ref(false);

const closeModal = () => {
    confirmingPackageTypeCreation.value = false;
};

const form = useForm({
    name: "",
    description: "",
});

const createPackageType = () => {
    form.post(route("setting.package-types.store"), {
        onSuccess: () => {
            closeModal()
            form.reset();
            router.visit(route("setting.package-types.index"));
            push.success("Package Type Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="flex justify-end mx-5 mt-4">
        <PrimaryButton
            @click="confirmingPackageTypeCreation = !confirmingPackageTypeCreation"
        >
            Create New Package type
        </PrimaryButton>
    </div>

    <DialogModal
        :maxWidth="'xl'"
        :show="confirmingPackageTypeCreation"
        @close="closeModal"
    >
        <template #title> Create New Package Type</template>

        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Name"/>
                    <TextInput v-model="form.name" class="w-full" placeholder="Package Type Name"/>
                    <InputError :message="form.errors.name"/>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Description"/>
                    <label class="relative flex">
                        <textarea
                            v-model="form.description"
                            class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Type Description..."
                            rows="4"
                        ></textarea>
                    </label>
                    <InputError :message="form.errors.description"/>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal"> Cancel</SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="createPackageType"
            >
                Create Package Type
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
