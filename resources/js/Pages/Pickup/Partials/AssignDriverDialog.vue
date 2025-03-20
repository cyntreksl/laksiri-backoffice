<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import Select from 'primevue/select';
import Button from "primevue/button";
import Dialog from 'primevue/dialog';
import {push} from "notivue";
import {watchEffect} from "vue";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    drivers: {
        type: Object,
        default: () => {
        },
    },
    selectedPickups: {
        type: Array,
        default: () => [],
    }
});

const emit = defineEmits(["update:visible"]);

const form = useForm({
    driver_id: null,
    job_ids: null,
});

// Watch for changes in props.idList and update form.job_ids
watchEffect(() => {
    form.job_ids = (props.selectedPickups || []).map((item) => item.id);
});

const handleAssignDriver = () => {
    let routeName = '';
    let params = {};

    const currentRoute = route().current();

    if (currentRoute === 'pickups.index') {
        routeName = 'pickups.driver.assign';
    } else if (currentRoute === 'call-center.hbls.door-to-door-list') {
        routeName = 'delivery.driver.assign';
    } else if (currentRoute === 'pickups.all') {
        routeName = 'pickups.driver.assign';
    } else if ( currentRoute === 'pickups.get-pending-jobs-by-user') {
        routeName = 'pickups.driver.assign';
        params = route().params;
    } else {
        routeName = 'pickups.exceptions.driver.assign';
    }

    form.post(route(routeName), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            push.success('Driver Assigned!')
            router.visit(route(currentRoute, params));
        },
    })
}
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Assign Driver" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <span class="text-surface-500 dark:text-surface-400 block mb-8">Assign Driver to Pickup(s).</span>

        <div class="flex items-center gap-4 mb-4">
            <label class="font-semibold w-24" for="username">Select Driver</label>
            <Select v-model="form.driver_id" :options="drivers" class="w-full md:w-56" option-value="id" optionLabel="name" placeholder="Select a Driver" />
            <InputError :message="form.errors.driver_id"/>
        </div>

        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing || drivers.length === 0" label="Assign Driver" type="button"
                    @click="handleAssignDriver"></Button>
        </div>
    </Dialog>
</template>
