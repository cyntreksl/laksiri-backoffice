<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import {watchEffect} from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    drivers: {
        type: Object,
        default: () => {
        },
    },
    idList: {
        type: Array,
        default: () => [],
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    driver_id: null,
    job_ids: null,
});

// Watch for changes in props.idList and update form.job_ids
watchEffect(() => {
    form.job_ids = props.idList;
});

const handleAssignDriver = () => {
    let routeName = '';

    if (route().current() === 'pickups.index') {
        routeName = 'pickups.driver.assign';
    } else {
        routeName = 'pickups.exceptions.driver.assign'
    }

    form.post(route(routeName), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            push.success('Driver Assigned!')
            const currentRoute = route().current();
            router.visit(route(currentRoute));
        },
    })
}
</script>

<template>
    <DialogModal :maxWidth="'xl'" :show="show" @close="$emit('close')">
        <template #title>
            Assign Driver
        </template>

        <template #content>
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <InputLabel value="Select Driver"/>
                    <span v-if="drivers.length === 0" class="text-red-500">Please add at least one driver to system.</span>
                    <div class="space-x-5 mt-1">
                        <label class="block">
                            <select
                                v-model="form.driver_id"
                                class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option :value="null" disabled>
                                    Select Driver
                                </option>
                                <option v-for="driver in drivers" :key="driver.id"
                                        :value="driver.id">{{ driver.name }}
                                </option>
                            </select>
                        </label>
                    </div>
                    <InputError :message="form.errors.driver_id"/>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing || drivers.length === 0"
                class="ms-3"
                @click="handleAssignDriver"
            >
                Assign Driver
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
