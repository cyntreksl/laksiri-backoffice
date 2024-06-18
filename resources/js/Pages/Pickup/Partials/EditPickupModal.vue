<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {onMounted, ref, watch} from "vue";
import TextInput from "@/Components/TextInput.vue";
import {push} from "notivue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    pickupId: {
        type: Number,
        required: true,
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
});

const emit = defineEmits(['close']);

const pickup = ref(null);
const loading = ref(false);

// Helper function to convert "HH:mm:ss" to "HH:mm"
const formatTime = (time) => {
    return time ? time.substring(0, 5) : '';
};

const getPickup = async () => {
    if (props.pickupId) {
        loading.value = true;
        try {
            const response = await fetch(`/pickups/${props.pickupId}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
            });

            if (!response.ok) {
                throw new Error('Network response was not ok.');
            } else {
                pickup.value = await response.json();
            }

        } catch (error) {
            console.error(error.message);
        } finally {
            loading.value = false;
        }
    } else {
        console.warn("pickupId is null");
    }
}

const form = useForm({
    pickup_date: null,
    pickup_time_start: null,
    pickup_time_end: null,
    zone_id: null,
});

watch(pickup, (newPickup) => {
    if (newPickup) {
        form.pickup_date = newPickup.pickup_date;
        form.pickup_time_start = formatTime(newPickup.pickup_time_start);
        form.pickup_time_end = formatTime(newPickup.pickup_time_end);
        form.zone_id = newPickup.zone_id;
    }
});

watch(() => props.pickupId, (newPickupId) => {
    if (newPickupId) {
        getPickup();
    }
});

onMounted(() => {
    getPickup();
});

const handleUpdatePickup = () => {
    form.put(route("pickups.update", props.pickupId), {
        onSuccess: () => {
            push.success('Job Updated Successfully!');
            router.visit(route('pickups.index'));
        },
        onError: () => {
            push.error('Something went to wrong!')
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <DialogModal :maxWidth="'xl'" :show="show" @close="$emit('close')">
        <template #title>
            Edit Pending Job
        </template>

        <template #content>
            <div v-if="loading">
                Loading...
            </div>

            <div v-else class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block">
                        <InputLabel value="Zone"/>
                        <select
                            v-model="form.zone_id"
                            class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option :value="null" disabled>
                                Select Zone
                            </option>
                            <option v-for="zone in zones" :key="zone.id" :value="zone.id">
                                {{ zone.name }}
                            </option>
                        </select>
                    </label>
                    <InputError :message="form.errors.zone_id"/>
                </div>

                <div>
                    <InputLabel value="Pickup Date"/>
                    <TextInput v-model="form.pickup_date" class="w-full" type="date"/>
                    <InputError :message="form.errors.pickup_date"/>
                </div>

                <div>
                    <InputLabel value="Start Pickup Time"/>
                    <TextInput v-model="form.pickup_time_start" :value="form.pickup_time_start" class="w-full" placeholder="Choose Time"
                               type="time"/>
                    <InputError :message="form.errors.pickup_time_start"/>
                </div>

                <div>
                    <InputLabel value="End Pickup Time"/>
                    <TextInput v-model="form.pickup_time_end" class="w-full" placeholder="Choose Time"
                               type="time"/>
                    <InputError :message="form.errors.pickup_time_end"/>
                </div>
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
                @click="handleUpdatePickup"
            >
                Update Job
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
