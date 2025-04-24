<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import MultiSelect from 'primevue/multiselect';
import InputText from 'primevue/inputtext';
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import {watch} from "vue";

const props = defineProps({
    driverArea: {
        type: Object,
        default: () => {
        },
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
    visible: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(["update:visible"]);

const form = useForm({
    id: null,
    name: "",
    zone_ids: null,
});

watch(
    () => props.driverArea,
    (newVal) => {
        if (props.visible && newVal) {
            form.id = newVal.id;
            form.name = newVal.name;
            form.zone_ids = Array.isArray(newVal.zones)
                ? newVal.zones.map(zone => zone.id)
                : [];
        }
    },
    { immediate: true, deep: true }
);

const updateDriverArea = () => {
    form.put(route("setting.driver-areas.update", props.driverArea.id), {
        onSuccess: () => {
            router.visit(route("setting.driver-areas.index"));
            form.reset();
            emit('close');
            push.success("Driver Area Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Edit Driver Area" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">
        <form @submit.prevent="updateDriverArea">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Zone"/>
                    <MultiSelect
                        v-model="form.zone_ids"
                        :maxSelectedLabels="3"
                        :options="zones"
                        class="w-full"
                        filter
                        option-label="name"
                        option-value="id"
                        placeholder="Select Zones"
                    />
                    <InputError :message="form.errors.zone_ids"/>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Driver Area Name"/>
                    <InputText v-model="form.name" class="w-full" placeholder="Enter Driver Area Name" type="text"/>
                    <InputError :message="form.errors.name"/>
                </div>
            </div>

            <div class="flex col-span-2 justify-end mt-3">
                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Save Driver Area"
                        type="submit" />
            </div>
        </form>
    </Dialog>
</template>

