<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, ref, watch, watchEffect} from "vue";
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
        }
    },
    cargoTypes: {
        type: Object,
        default: () => {
        }
    },
    zones: {
        type: Object,
        default: () => {
        }
    }
})

const currentBranch = usePage().props?.auth.user.primary_branch.slug;

const findCountryCodeByBranch = computed(() => {
    switch (currentBranch) {
        case 'riyadh':
            return '+966';
        case 'sri-lanka':
            return '+94';
        case 'dubai':
            return '+971';
        case 'kuwait':
            return '+965';
    }
})

const countryCodes = [
    '+94',
    '+966',
    '+971',
    '+965',
]

const countryCode = ref(findCountryCodeByBranch.value);
const contactNumber = ref('');

// Get today's date in yyyy-mm-dd format
const today = new Date();
const formattedToday = today.toISOString().split('T')[0];

const form = useForm({
    name: "",
    email: "",
    contact_number: computed(() => countryCode.value + contactNumber.value),
    address: "",
    note_type: null,
    notes: "",
    cargo_type: "",
    location: "",
    zone_id: null,
    pickup_date: formattedToday,
    pickup_time_start: '',
    pickup_time_end: '',
    is_from_important_customer: false,
    is_urgent_pickup: false,
});

const handlePickupCreate = () => {
    console.log(form.pickup_time_end, form.pickup_time_start);
    form.post(route("pickups.store"), {
        onSuccess: () => {
            form.reset();
            router.visit(route("pickups.index"));
            push.success('Pickup added successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
};

watch(() => form.note_type, (newValue) => {
    form.notes += newValue;
});

const isImportant = ref(false);
const isUrgentPickup = ref(false);

watch(isImportant, (newValue) => {
    form.is_from_important_customer = newValue;
});

watch(isUrgentPickup, (newValue) => {
    form.is_urgent_pickup = newValue;
});
</script>

<template>
    <AppLayout title="Pick Up Job">
        <template #header>Pick Up Job - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handlePickupCreate">
            <div class="grid grid-cols-1 sm:grid-cols-5 mt-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="grid grid-cols-2">
                            <h2 class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Basic Details
                            </h2>
                            <div class="flex justify-end">
                                <button type="button" x-data="{ isImportant: true }"
                                        x-tooltip.placement.left-start.success="'This pickup is from important customer'"
                                        @click.stop="isImportant = !isImportant"
                                        class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 flex items-center space-x-2">
                                    <svg v-if="isImportant" xmlns="http://www.w3.org/2000/svg"
                                         class="size-5.5 text-success dark:text-accent" viewBox="0 0 20 20"
                                         fill="currentColor">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </button>
                            </div>
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
                                        class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-4.5 transition-colors duration-200"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
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
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-4.5 transition-colors duration-200"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <InputError :message="form.errors.email"/>
                            </div>

                            <div>
                                <InputLabel value="Mobile Number"/>
                                <div class="flex -space-x-px">
                                    <select
                                        v-model="countryCode"
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option v-for="(countryCode, index) in countryCodes" :key="index">{{
                                                countryCode
                                            }}
                                        </option>
                                    </select>

                                    <input
                                        v-model="contactNumber"
                                        class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                        placeholder="123 4567 890"
                                        type="text"
                                    />
                                </div>
                                <InputError :message="form.errors.contact_number"/>
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Address"/>
                                <label class="block">
                                    <textarea
                                        v-model="form.address"
                                        rows="4"
                                        placeholder="Type address here..."
                                        class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
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
                                Notes
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <label class="block">
                                    <InputLabel value="Note Type"/>
                                    <select
                                        v-model="form.note_type"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option :value="null" disabled>
                                            Select One
                                        </option>
                                        <option v-for="noteType in noteTypes" :key="noteType"
                                                :value="noteType.note_type">{{ noteType.note_type }}
                                        </option>
                                    </select>
                                </label>
                                <InputError :message="form.errors.note_type"/>
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Note"/>
                                <label class="block">
                                    <textarea
                                        v-model="form.notes"
                                        rows="4"
                                        placeholder="Type note here..."
                                        class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    ></textarea>
                                </label>
                                <InputError :message="form.errors.notes"/>
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
                            <div class="flex justify-end">
                                <button type="button" x-data="{ isImportant: true }"
                                        x-tooltip.placement.left-start.secondary="'This is need to pickup within today'"
                                        @click.stop="isUrgentPickup = !isUrgentPickup"
                                        class="btn size-7 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 flex items-center space-x-2">
                                    <svg v-if="isUrgentPickup" xmlns="http://www.w3.org/2000/svg"
                                         class="size-5.5 text-secondary dark:text-accent" viewBox="0 0 20 20"
                                         fill="currentColor">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </button>
                            </div>
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
                                        <option :value="null" disabled>
                                            Select Zone
                                        </option>
                                        <option v-for="zone in zones" :key="zone.id" :value="zone.name">
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
                                    <TextInput v-model="form.pickup_time_start" class="w-full" placeholder="Choose Time"
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
                        </div>
                    </div>

                    <div class="flex justify-end bottom-0 space-x-5">
                        <DangerOutlineButton @click="router.visit(route('pickups.index'))">Cancel</DangerOutlineButton>
                        <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing"
                                       class="space-x-2"
                                       type="submit"
                        >
                            <span>Create a Job</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="size-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                />
                            </svg>
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped></style>
