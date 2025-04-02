<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import Button from "primevue/button";

const props = defineProps({
    visible: {
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
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Assign Warehouse Zone" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <span class="text-surface-500 dark:text-surface-400 block mb-8">You can assign warehouse to zone.</span>

        <div>
            <InputLabel value="Warehouse Zone" />
            <Select v-model="form.warehouse_zone_id" :options="warehouseZones" class="w-full" option-value="id" optionLabel="name" placeholder="Select a Zone"/>
            <InputError :message="form.errors.warehouse_zone_id"/>
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>

            <Button
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                label="Assign"
                @click="handleAssignZone"></Button>
        </template>

    </Dialog>
</template>
