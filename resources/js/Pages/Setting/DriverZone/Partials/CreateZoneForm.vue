<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import notification from "@/magics/notification.js";
import {push} from "notivue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import TextInput from "@/Components/TextInput.vue";

defineProps({
    areas: {
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
    areas: "",
});

const createZone = () => {
    form.post(route("setting.zones.store"), {
        onSuccess: () => {
            router.visit(route("setting.driver-zones.index"));
            form.reset();
            emit('close');
            push.success('Zone Created Successfully!');
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="Create New Driver Zone" modal
            @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Zone Name"/>
                <TextInput v-model="form.name" class="w-full" placeholder="Enter Zone Name"/>
                <InputError :message="form.errors.name"/>
            </div>

            <div class="col-span-1 sm:col-span-2 gap-5">
                <label class="block">
                    <InputLabel value="Zone Areas"/>
                    <select v-model="form.areas"
                            autocomplete="off"
                            multiple
                            placeholder="Zone Areas"
                            x-init="$el._tom = new Tom($el,{create:true,plugins: ['caret_position','input_autogrow','remove_button']})"
                    >
                        <option v-for="area in areas" :key="area.id" :value="area.name">{{ area.name }}</option>
                    </select>
                </label>
                <span class="text-tiny+ text-slate-400 dark:text-navy-300"
                >Comma separated values. Auto Zone Area Assignments, it will come as
            a notification</span
                >
                <InputError :message="form.errors.areas"/>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
            <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Create Driver Zone"
                    type="button"
                    @click="createZone"></Button>
        </div>
    </Dialog>
</template>
