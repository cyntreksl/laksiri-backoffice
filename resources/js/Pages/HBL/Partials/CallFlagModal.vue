<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {watch} from "vue";
import moment from "moment";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblId: {
        type: String,
        required: true,
    },
    callerName: {
        type: String,
        required: true,
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    caller: "",
    date: moment(new Date()).format("YYYY-MM-DD"),
    notes: "",
    followup_date: "",
});

watch(() => props.callerName, (newVal) => {
    form.caller = newVal
})

const handleCreateCallFlag = () => {
    form.post(route("hbls.create-call-flag", props.hblId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            push.success('Call Flag Added!');
            form.reset();
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
}
</script>

<template>
    <DialogModal :maxWidth="'xl'" :show="show" @close="$emit('close')">
        <template #title>
            Add Call Flag
        </template>

        <template #content>
            <div class="mt-4">
                <InputLabel value="Caller Name"/>
                <TextInput
                    v-model="form.caller"
                    class="w-full"
                    placeholder="Enter Caller Name"
                    required
                />
                <InputError :message="form.errors.caller"/>
            </div>

            <div class="mt-4">
                <InputLabel value="Date"/>
                <TextInput
                    v-model="form.date"
                    class="w-full"
                    required
                    type="date"
                />
                <InputError :message="form.errors.date"/>
            </div>

            <div class="mt-4">
                <InputLabel value="Notes"/>
                <label class="block">
                  <textarea
                      v-model="form.notes"
                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      placeholder="Type something here..."
                      rows="4"
                  ></textarea>
                </label>
                <InputError :message="form.errors.notes"/>
            </div>

            <div class="mt-4">
                <InputLabel value="Next Followup Date"/>
                <TextInput
                    v-model="form.followup_date"
                    class="w-full"
                    type="date"
                />
                <InputError :message="form.errors.followup_date"/>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="$emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="handleCreateCallFlag"
            >
                Add Call Flag
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
