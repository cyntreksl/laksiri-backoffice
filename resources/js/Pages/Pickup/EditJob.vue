<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref, watch} from "vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DatePicker from "@/Components/DatePicker.vue";
import {push} from "notivue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    noteTypes: {
        type: Object,
        default: () => {
        },
    },
    cargoTypes: {
        type: Object,
        default: () => {
        },
    },
    zones: {
        type: Object,
        default: () => {
        },
    },
    pickupTypes: {
        type: Object,
        default: () => {
        },
    },
    pickup: {
        type: Object,
        default: () => {
        },
    },
});

const form = useForm({
    name: props.pickup.name,
    email: props.pickup.email,
    contact_number: props.pickup.contact_number,
    address: props.pickup.address,
    note_type: props.pickup.notes,
    notes: props.pickup.notes,
    pickup_type: props.pickup.pickup_type,
    pickup_note: props.pickup.pickup_note,
    cargo_type: props.pickup.cargo_type,
    location: "",
    zone_id: props.pickup.zone_id,
    pickup_date: props.pickup.pickup_date,
    pickup_time_start: props.pickup.pickup_time_start,
    pickup_time_end: props.pickup.pickup_time_end,
});

// Method to find zone ID based on address
const findZoneIdByAddress = (address) => {
    for (const zone of props.zones) {
        for (const area of zone.areas) {
            if (address.includes(area.name)) {
                return zone.id;
            }
        }
    }
    return null;
};

// Watcher to update zone_id based on address
watch(
    () => form.address,
    (newAddress) => {
        form.zone_id = findZoneIdByAddress(newAddress);
    }
);

const handlePickupUpdate = () => {
    form.put(route("pickups.update", props.pickup.id), {
        onSuccess: () => {
            form.reset();
            router.visit(route("pickups.index"));
            push.success("Pickup updated successfully!");
        },
        onError: () => {
            push.error("Something went to wrong!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};

watch(
    () => form.note_type,
    (newValue) => {
        form.notes += newValue;
    }
);

const isImportant = ref(false);
const isUrgentPickup = ref(false);

watch(isImportant, (newValue) => {
    form.is_from_important_customer = newValue;
});

watch(isUrgentPickup, (newValue) => {
    form.is_urgent_pickup = newValue;
});

const planeIcon = ref(`
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="24"
  height="24"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="icon icon-tabler icons-tabler-outline icon-tabler-plane"
>
  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
  <path d="M16 10h4a2 2 0 0 1 0 4h-4l-4 7h-3l2 -7h-4l-2 2h-3l2 -4l-2 -4h3l2 2h4l-2 -7h3z" />
</svg>
`);

const shipIcon = ref(`
<svg
  xmlns="http://www.w3.org/2000/svg"
  width="24"
  height="24"
  viewBox="0 0 24 24"
  fill="none"
  stroke="currentColor"
  stroke-width="2"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="icon icon-tabler icons-tabler-outline icon-tabler-ship"
>
  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
  <path d="M2 20a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1a2.4 2.4 0 0 1 2 -1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2 -1" />
  <path d="M4 18l-1 -5h18l-2 4" />
  <path d="M5 13v-6h8l4 6" />
  <path d="M7 7v-4h-1" />
</svg>
`);
</script>

<template>
    <AppLayout title="Pick Up Job - Edit">
        <template #header>Pick Up Job - Edit</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handlePickupUpdate">
            <div class="grid grid-cols-1 sm:grid-cols-5 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Basic Details
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <InputLabel value="Name"/>
                                <label class="relative flex">
                                    <input
                                        v-model="form.name"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Name"
                                        type="text"
                                    />
                                    <div
                                        class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                                    >
                                        <svg
                                            class="size-4.5 transition-colors duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <InputError :message="form.errors.name"/>
                            </div>

                            <div>
                                <InputLabel value="Email"/>
                                <label class="relative flex">
                                    <input
                                        v-model="form.email"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Email"
                                        type="email"
                                    />
                                    <div
                                        class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                                    >
                                        <svg
                                            class="size-4.5 transition-colors duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <InputError :message="form.errors.email"/>
                            </div>

                            <div>
                                <InputLabel value="Mobile Number"/>
                                <label class="relative flex">
                                    <input
                                        v-model="form.contact_number"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Contact Number"
                                        type="text"
                                    />
                                    <div
                                        class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                                    >
                                        <svg class="icon icon-tabler icons-tabler-outline icon-tabler-phone" fill="none" height="24"
                                             stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                             viewBox="0 0 24 24" width="24"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                            <path
                                                d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"/>
                                        </svg>
                                    </div>
                                </label>
                                <InputError :message="form.errors.contact_number"/>
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Address"/>
                                <label class="block">
                  <textarea
                      v-model="form.address"
                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      placeholder="Type address here..."
                      rows="4"
                  ></textarea>
                                </label>
                                <InputError :message="form.errors.address"/>
                            </div>
                        </div>
                    </div>

                    <div class="card px-4 py-4 sm:px-5">
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Packages
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <label class="block">
                                    <InputLabel value="Package Type"/>
                                    <select
                                        v-model="form.note_type"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option :value="null" disabled>Select One</option>
                                        <option
                                            v-for="noteType in noteTypes"
                                            :key="noteType"
                                            :value="noteType.note_type"
                                        >
                                            {{ noteType.note_type }}
                                        </option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.note_type"/>
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Packages"/>
                                <label class="block">
                  <textarea
                      v-model="form.notes"
                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      placeholder="Type Packages here..."
                      rows="4"
                  ></textarea>
                                </label>
                                <InputError :message="form.errors.notes"/>
                            </div>

                            <div class="col-span-2">
                                <label class="block">
                                    <InputLabel value="Pickup Type"/>
                                    <select
                                        v-model="form.pickup_type"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option disabled value="">Select One</option>
                                        <option v-for="pickupType in pickupTypes" :key="pickupType">
                                            {{ pickupType }}
                                        </option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.pickup_type"/>
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Pickup Note"/>
                                <label class="block">
                  <textarea
                      v-model="form.pickup_note"
                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      placeholder="Type Pickup Note here..."
                      rows="4"
                  ></textarea>
                                </label>
                                <InputError :message="form.errors.pickup_note"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Cargo Type
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="my-5">
                                <div class="space-x-5">
                                    <label
                                        v-for="cargoType in cargoTypes"
                                        class="inline-flex items-center space-x-2"
                                    >
                                        <input
                                            v-model="form.cargo_type"
                                            :value="cargoType"
                                            class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                            name="cargo_type"
                                            type="radio"
                                        />
                                        <p>{{ cargoType }}</p>
                                        <span v-if="cargoType == 'Sea Cargo'">
                      <div v-html="shipIcon"></div>
                    </span>
                                        <span v-if="cargoType == 'Air Cargo'">
                      <div v-html="planeIcon"></div>
                    </span>
                                    </label>
                                </div>
                                <InputError :message="form.errors.cargo_type"/>
                            </div>
                        </div>
                    </div>

                    <div class="card px-4 py-4 sm:px-5">
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Additional Details
                            </h2>
                        </div>
                        <div class="my-5 space-y-5">
                            <!--                            <div-->
                            <!--                                class="flex justify-between items-center space-y-5 space-x-5"-->
                            <!--                            >-->
                            <!--                                <div class="w-full">-->
                            <!--                                    <label class="block">-->
                            <!--                                        <span>Location</span>-->
                            <!--                                        <input-->
                            <!--                                            v-model="form.location"-->
                            <!--                                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"-->
                            <!--                                            placeholder="Location"-->
                            <!--                                            type="text"-->
                            <!--                                        />-->
                            <!--                                    </label>-->
                            <!--                                    <div-->
                            <!--                                        v-if="form.errors.location"-->
                            <!--                                        class="text-tiny+ text-error"-->
                            <!--                                    >{{ form.errors.location }}-->
                            <!--                                    </div>-->
                            <!--                                </div>-->

                            <!--                                <div>-->
                            <!--                                    <button-->
                            <!--                                        class="btn size-9 rounded-full bg-success p-0 font-medium text-white hover:bg-success-focus focus:bg-success-focus active:bg-success-focus/90"-->
                            <!--                                    >-->
                            <!--                                        <svg-->
                            <!--                                            xmlns="http://www.w3.org/2000/svg"-->
                            <!--                                            fill="none"-->
                            <!--                                            viewBox="0 0 24 24"-->
                            <!--                                            stroke-width="1.5"-->
                            <!--                                            stroke="currentColor"-->
                            <!--                                            class="size-5"-->
                            <!--                                        >-->
                            <!--                                            <path-->
                            <!--                                                stroke-linecap="round"-->
                            <!--                                                stroke-linejoin="round"-->
                            <!--                                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"-->
                            <!--                                            />-->
                            <!--                                            <path-->
                            <!--                                                stroke-linecap="round"-->
                            <!--                                                stroke-linejoin="round"-->
                            <!--                                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"-->
                            <!--                                            />-->
                            <!--                                        </svg>-->
                            <!--                                    </button>-->
                            <!--                                </div>-->
                            <!--                            </div>-->

                            <div>
                                <label class="block">
                                    <InputLabel value="Zone"/>
                                    <select
                                        v-model="form.zone_id"
                                        class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option :value="null" disabled>Select Zone</option>
                                        <option
                                            v-for="zone in zones"
                                            :key="zone.id"
                                            :value="zone.id"
                                        >
                                            {{ zone.name }}
                                        </option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.zone_id"/>
                            </div>

                            <div>
                                <InputLabel value="Pickup Date"/>
                                <DatePicker v-model="form.pickup_date"/>
                                <InputError :message="form.errors.pickup_date"/>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel value="Start Pickup Time"/>
                                    <TextInput
                                        v-model="form.pickup_time_start"
                                        class="w-full"
                                        placeholder="Choose Time"
                                        type="time"
                                    />
                                    <InputError :message="form.errors.pickup_time_start"/>
                                </div>

                                <div>
                                    <InputLabel value="End Pickup Time"/>
                                    <TextInput
                                        v-model="form.pickup_time_end"
                                        class="w-full"
                                        placeholder="Choose Time"
                                        type="time"
                                    />
                                    <InputError :message="form.errors.pickup_time_end"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end bottom-0 space-x-5">
                        <DangerOutlineButton @click="router.visit(route('pickups.index'))"
                        >Cancel
                        </DangerOutlineButton
                        >
                        <PrimaryButton
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                            class="space-x-2"
                            type="submit"
                        >
                            <span>Update Job</span>
                            <svg
                                class="size-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
