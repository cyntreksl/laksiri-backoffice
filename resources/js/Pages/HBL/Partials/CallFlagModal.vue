<script setup>
import {useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {watch} from "vue";
import moment from "moment";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    visible: {
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
    form.date = moment(form.date).format("YYYY-MM-DD");

    form.followup_date = moment(form.followup_date).format("YYYY-MM-DD");

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
    <Dialog :style="{ width: '25rem' }" :visible="visible" header="New Call Flag" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
        <div class="grid grid-cols-1 gap-5">
            <div>
                <InputLabel value="Caller Name"/>
                <InputText v-model="form.caller" class="w-full"
                           placeholder="Enter Caller Name" required />
                <InputError :message="form.errors.caller"/>
            </div>

            <div>
                <InputLabel value="Date"/>
                <DatePicker v-model="form.date" class="w-full" date-format="yy-mm-dd" input-id="from-date" required/>
                <InputError :message="form.errors.date"/>
            </div>

            <div>
                <InputLabel value="Notes"/>
                <Textarea v-model="form.notes" class="w-full" cols="30" placeholder="Type something here..." rows="5" />
                <InputError :message="form.errors.notes"/>
            </div>

            <div>
                <InputLabel value="Next Followup Date"/>
                <DatePicker v-model="form.followup_date" class="w-full" date-format="yy-mm-dd" input-id="followup-date" placeholder="Set Followup Date" required/>
                <InputError :message="form.errors.followup_date"/>
            </div>

            <div class="flex justify-end gap-2">
                <Button label="Cancel" severity="secondary" type="button" @click="emit('close')"></Button>
                <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" label="Add Call Flag" type="button"
                        @click="handleCreateCallFlag"></Button>
            </div>
        </div>
    </Dialog>
</template>
