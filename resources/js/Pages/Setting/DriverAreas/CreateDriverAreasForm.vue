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

defineProps({
    zones: {
        type: Object,
        default: () => {
        },
    },
});

const confirmingDriverAreasCreation = ref(false);

const closeModal = () => {
    confirmingDriverAreasCreation.value = false;
};

const form = useForm({
    name: "",
    zone_ids: [],
});

const createDriverArea = () => {
    form.post(route("setting.driver-areas.store"), {
        onSuccess: () => {
            router.visit(route("setting.driver-areas.index"));
            form.reset();
            push.success("Driver Area Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="flex justify-end mx-5 mt-4">
        <PrimaryButton
            @click="confirmingDriverAreasCreation = !confirmingDriverAreasCreation"
        >
            Create New Driver Area
        </PrimaryButton>
    </div>

    <DialogModal
        :maxWidth="'5xl'"
        :show="confirmingDriverAreasCreation"
        @close="closeModal"
    >
        <template #title> Create New Driver Area</template>

        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Zone"/>
                    <div class="space-x-5">
                        <label class="block">
                            <select
                                v-model="form.zone_ids"
                                autocomplete="off"
                                class="mt-1.5 w-full"
                                multiple
                                placeholder="Select Zone..."
                                x-init="$el._tom = new Tom($el, {plugins: ['remove_button']})"
                            >
                                <option value="">Select zone...</option>
                                <option v-for="(zone, index) in zones" :key="index" :value="zone.id">
                                    {{ zone.name }}
                                </option>
                            </select>
                        </label>
                    </div>
                    <InputError :message="form.errors.zone_ids"/>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Driver Area Name"/>
                    <TextInput v-model="form.name" class="w-full" placeholder="Driver Area Name"/>
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
                @click="createDriverArea"
            >
                Create Driver Area
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
