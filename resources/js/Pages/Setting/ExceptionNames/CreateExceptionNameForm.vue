<script setup>
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import TextInput from "@/Components/TextInput.vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits(["update:visible"]);

const form = useForm({
    name: "",
});

const createExceptionName = () => {
    form.post(route("setting.exception-names.store"), {
        onSuccess: () => {
            form.reset();
            emit('close');
            router.visit(route("setting.exception-names.index"));
            push.success("Exception Name Created Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Create New Exception Name" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Name"/>
                <TextInput v-model="form.name" class="w-full" placeholder="Exception Name"/>
                <InputError :message="form.errors.name"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Exception Name" type="button"
                    @click="createExceptionName"></Button>
        </div>
    </Dialog>
</template>
