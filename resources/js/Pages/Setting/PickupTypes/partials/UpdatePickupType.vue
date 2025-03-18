<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {ref, watch} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    pickupType: {
        type: Object,
        default: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close"]);

const closeModal = () => {
    emit("close");
};

const form = useForm({
    pickup_type_name: props.pickupType.pickup_type_name,
});

// Watch for changes in pickupType and update the form
watch(
    () => props.pickupType,
    (newVal) => {
        if (newVal) {
            form.pickup_type_name = newVal.pickup_type_name || "";
        }
    },
    { immediate: true, deep: true }
);

const updatePickupType = () => {
    console.log(form.data());
    form.put(route("setting.pickup-types.update", props.pickupType.id), {
        onSuccess: () => {
            form.reset();
            closeModal();
            router.visit(route("setting.pickup-types.index"));
            push.success("Pickup Type Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <DialogModal
        :maxWidth="'xl'"
        :show="show"
        @close="closeModal"
    >
        <template #title> Update Pickup Type</template>

        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Pickup Type"/>
                    <TextInput v-model="form.pickup_type_name" class="w-full"/>
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
                @click="updatePickupType"
            >
                Update Pickup Type
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
