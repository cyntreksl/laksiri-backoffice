<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Textarea from 'primevue/textarea';

defineProps({
    visible: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(["update:visible"]);

const form = useForm({
    name: "",
    description: "",
});

const createWarehouseZone = () => {
    form.post(route("setting.warehouse-zones.store"), {
        onSuccess: () => {
            router.visit(route("setting.warehouse-zones.index"));
            form.reset();
            emit('close');
            push.success("Warehouse Zone Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Create New Warehouse Zone" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Warehouse Zone Name"/>
                <InputText v-model="form.name" class="w-full" placeholder="Enter Warehouse Zone Name" type="text"/>
                <InputError :message="form.errors.name"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Warehouse Zone Description"/>
                <Textarea v-model="form.description" class="w-full" cols="30" placeholder="Type Description..." rows="4"/>
                <InputError :message="form.errors.description"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Warehouse Zone"
                    type="button"
                    @click="createWarehouseZone"></Button>
        </div>
    </Dialog>
</template>
