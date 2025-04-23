<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import {watch} from "vue";

const props = defineProps({
    exceptionName: {
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
    id: props.exceptionName.id,
    name: props.exceptionName.name,
});

watch(
    () => props.exceptionName,
    (newVal) => {
        form.id = newVal.id;
        form.name = newVal.name;
    },
    { immediate: true, deep: true }
);

const updateExceptionName = () => {
    form.put(route("setting.exception-names.update", props.exceptionName.id), {
        onSuccess: () => {
            router.visit(route("setting.exception-names.index"));
            form.reset();
            emit('update:visible', false);
            push.success("Exception Name Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Edit Exception Name" modal @update:visible="(newValue) => $emit('update:visible', newValue)">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Name"/>
                <TextInput v-model="form.name" class="w-full" placeholder="Exception Name"/>
                <InputError :message="form.errors.name"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Save Exception Name" type="button"
                    @click="updateExceptionName"></Button>
        </div>
    </Dialog>
</template>

