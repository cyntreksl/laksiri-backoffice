<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import MultiSelect from 'primevue/multiselect';
import InputText from 'primevue/inputtext';

const props = defineProps({
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
    name: "",
    zone_ids: [],
});

const createDriverArea = () => {
    form.post(route("setting.driver-areas.store"), {
        onSuccess: () => {
            router.visit(route("setting.driver-areas.index"));
            form.reset();
            emit('close');
            push.success("Driver Area Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Create New Driver Area" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Zone"/>
                <MultiSelect v-model="form.zone_ids" :maxSelectedLabels="3" :options="zones" class="w-full" filter option-label="name"
                             option-value="id" placeholder="Select Zones" />
                <InputError :message="form.errors.zone_ids"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Driver Area Name"/>
                <InputText v-model="form.name" class="w-full" placeholder="Enter Driver Area Name" type="text"/>
                <InputError :message="form.errors.name"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Driver Area"
                    type="button"
                    @click="createDriverArea"></Button>
        </div>
    </Dialog>
</template>
