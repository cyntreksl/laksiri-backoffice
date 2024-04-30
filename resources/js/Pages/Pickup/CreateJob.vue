<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
    noteTypes: {
        type: Object,
        default: () => {
        }
    }
})

const form = useForm({
    name: "",
    email: "",
    contact_number: "",
    address: "",
    note_type: "",
    note: "",
    cargo_type: "",
    location: "",
    zone_id: "",
    pickup_date: "",
});

const handlePickupCreate = () => {
    form.post(route("pickups.store"), {
        onSuccess: () => {
            router.visit(route("pickups.index"));
            form.reset();
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};
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
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Basic Details
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <span>Name</span>
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
                                <span
                                    v-if="form.errors.name"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.name }}</span>
                            </div>

                            <div>
                                <span>Email</span>
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
                                <span
                                    v-if="form.errors.email"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.email }}</span>
                            </div>

                            <div>
                                <span>Mobile Number</span>
                                <div class="flex -space-x-px">
                                    <select
                                        class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option>+94</option>
                                        <option>+95</option>
                                        <option>+96</option>
                                    </select>

                                    <input
                                        v-model="form.contact_number"
                                        class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                        placeholder="123 4567 890"
                                        type="text"
                                    />
                                </div>
                                <span
                                    v-if="form.errors.contact_number"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.contact_number }}</span>
                            </div>

                            <div class="col-span-2">
                                <span>Address</span>
                                <label class="block">
                                    <textarea
                                        v-model="form.address"
                                        rows="4"
                                        placeholder="Type address here..."
                                        class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    ></textarea>
                                </label>
                                <span
                                    v-if="form.errors.address"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.address }}</span>
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
                                    <span>Note Type</span>
                                    <select
                                        v-model="form.note_type"
                                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option selected disabled>
                                            Select One
                                        </option>
                                        <option v-for="noteType in noteTypes" :key="noteType"
                                                :value="noteType.note_type">{{ noteType.note_type }}
                                        </option>
                                    </select>
                                </label>
                                <span
                                    v-if="form.errors.note_type"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.name }}</span>
                            </div>

                            <div class="col-span-2">
                                <span>Note</span>
                                <label class="block">
                                    <textarea
                                        v-model="form.note"
                                        rows="4"
                                        placeholder="Type note here..."
                                        class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    ></textarea>
                                </label>
                                <span
                                    v-if="form.errors.note"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.note }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Cargo Type
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.cargo_type"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="cargo_type"
                                        value="air"
                                        type="radio"
                                    />
                                    <p>Air Cargo</p>
                                </label>
                                <label
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.cargo_type"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="cargo_type"
                                        value="sea"
                                        type="radio"
                                    />
                                    <p>Sea Cargo</p>
                                </label>
                            </div>
                            <span
                                v-if="form.errors.cargo_type"
                                class="text-tiny+ text-error"
                            >{{ form.errors.cargo_type }}</span>
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
                            <div
                                class="flex justify-between items-center space-y-5 space-x-5"
                            >
                                <div class="w-full">
                                    <label class="block">
                                        <span>Location</span>
                                        <input
                                            v-model="form.location"
                                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                            placeholder="Location"
                                            type="text"
                                        />
                                    </label>
                                    <div
                                        v-if="form.errors.location"
                                        class="text-tiny+ text-error"
                                    >{{ form.errors.location }}
                                    </div>
                                </div>

                                <div>
                                    <button
                                        class="btn size-9 rounded-full bg-success p-0 font-medium text-white hover:bg-success-focus focus:bg-success-focus active:bg-success-focus/90"
                                    >
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
                                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block">
                                    <span>Zone</span>
                                    <select
                                        v-model="form.zone_id"
                                        class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                    >
                                        <option selected disabled>
                                            Select Zone
                                        </option>
                                        <option value="1">Zone 1</option>
                                        <option value="2">Zone 2</option>
                                        <option value="3">Zone 3</option>
                                    </select>
                                </label>
                                <div
                                    v-if="form.errors.zone_id"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.zone_id }}
                                </div>
                            </div>

                            <div>
                                <span class="">Pickup Date</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.pickup_date"
                                        x-init="$el._x_flatpickr = flatpickr($el)"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="Choose date..."
                                        type="text"
                                    />
                                    <span
                                        class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-5 transition-colors duration-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </span>
                                </label>
                                <div
                                    v-if="form.errors.pickup_date"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.pickup_date }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end py-10 space-x-5">
                <button
                    class="btn border border-error font-medium text-error hover:bg-error hover:text-white focus:bg-error focus:text-white active:bg-error/90"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="{ 'opacity-50': form.processing }"
                    class="btn space-x-2 border border-warning/30 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
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
                </button>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped></style>
