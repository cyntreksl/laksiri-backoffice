<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {watch} from "vue";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Dialog from "primevue/dialog";

const props = defineProps({
    packageType: {
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
    id: props.packageType.id,
    name: props.packageType.name,
    description: props.packageType.description,
});

watch(
    () => props.packageType,
    (newVal) => {
        if (props.visible && newVal) {
            form.id = newVal.id;
            form.name = newVal.name;
            form.description = newVal?.description;
        }
    },
    {immediate: true, deep: true}
);

const updatePackageType = () => {
    form.put(route("setting.package-types.update", props.packageType.id), {
        onSuccess: () => {
            router.visit(route("setting.package-types.index"));
            form.reset();
            emit('close');
            push.success("Package Type Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Edit Package Type" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Package Type Name"/>
                <InputText v-model="form.name" class="w-full" placeholder="Enter Package Type Name" type="text"/>
                <InputError :message="form.errors.name"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Description"/>
                <Textarea v-model="form.description" class="w-full" cols="30" placeholder="Type Description..." rows="4"/>
                <InputError :message="form.errors.description"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Save Package Type" type="button"
                    @click="updatePackageType"></Button>
        </div>
    </Dialog>
</template>

