<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblId: {
        type: Number,
        required: true,
    },
    warehouseZones: {
        type: Object,
        default: () => {
        },
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    warehouse_zone_id: null,
});

const handleAssignZone = () => {
    form.put(route("back-office.warehouses.assign.zone", props.hblId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            push.success('Warehouse Zone Assigned!')
            router.visit(route('back-office.warehouses.index'))
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
}
</script>

<template>
    <DialogModal :maxWidth="'lg'" :show="show" @close="$emit('close')">
        <template #title>
            Assign Warehouse Zone
        </template>

        <template #content>
            <div class="mt-4">
                <InputLabel value="Warehouse Zone" />
                <select
                    v-model="form.warehouse_zone_id"
                    class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                >
                    <option :value="null" disabled>Select Zone</option>
                    <option
                        v-for="zone in warehouseZones"
                        :key="zone.id"
                        :value="zone.id"
                    >
                        {{ zone.name }}
                    </option>
                </select>
                <InputError :message="form.errors.warehouse_zone_id"/>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="handleAssignZone"
            >
                Assign
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
