<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    driverAreas: {
        type: Object,
        default: () => {
        },
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
    zoneIds: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    id: props.driverAreas.id,
    name: props.driverAreas.name,
    zone_ids: props.zoneIds,
});

const updateDriverArea = () => {
    form.put(route("setting.driver-areas.update", props.driverAreas.id), {
        onSuccess: () => {
            router.visit(route("setting.driver-areas.index"));
            form.reset();
            push.success("Driver Area Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div class="card px-4 py-4 sm:px-5">
        <div>
            <h2
                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                Update Driver Area
            </h2>
            <br/>
        </div>
        <form @submit.prevent="updateDriverArea">
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
            <br/>
            <div class="flex col-span-2 justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="ms-3"
                    type="submit"
                >
                    Update Driver Area
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

