<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";

defineProps({
    hblTypes: {
        type: Object,
        default: () => {
        }
    },
    cargoTypes: {
        type: Object,
        default: () => {
        }
    },
    warehouses: {
        type: Object,
        default: () => {
        }
    }
})

const form = useForm({
    hbl: "",
    hbl_name: "",
    email: "",
    contact_number: "",
    nic: "",
    iq_number: "",
    address: "",
    consignee_name: "",
    consignee_nic: "",
    consignee_contact: "",
    consignee_address: "",
    consignee_note: "",
    cargo_type: "",
    hbl_type: "",
    warehouse: "",
    freight_charge: 0,
    bill_charge: 0,
    other_charge: 0,
    discount: 0,
    paid_amount: 0,
});

const handleHBLCreate = () => {
    form.post(route("hbls.store"), {
        onSuccess: () => {
            router.visit(route("hbls.index"));
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
    <AppLayout title="HBL Create">
        <template #header>HBL - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb/>

        <!-- Create Pickup Form -->
        <form @submit.prevent="handleHBLCreate">
            <div class="grid grid-cols-1 sm:grid-cols-5 my-4 gap-4">
                <div class="sm:col-span-3 space-y-5">
                    <div class="card px-4 py-4 sm:px-5">
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Basic Details
                            </h2>
                        </div>
                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-1">
                                <span>HBL</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.hbl"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="HBL"
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
                                    v-if="form.errors.hbl"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.hbl }}</span>
                            </div>

                            <div class="col-span-2">
                                <span>Name</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.hbl_name"
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
                                    v-if="form.errors.hbl_name"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.hbl_name }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
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

                            <div>
                                <span>NIC/Passport Number</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.nic"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="ID/PP Number"
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
                                    v-if="form.errors.nic"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.nic }}</span>
                            </div>

                            <div>
                                <span>IQ Number</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.iq_number"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="IQ Number"
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
                                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <span
                                    v-if="form.errors.iq_number"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.iq_number }}</span>
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
                                Consignee Details
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-3">
                            <div class="col-span-2">
                                <span>Name</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.consignee_name"
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
                                    v-if="form.errors.consignee_name"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.consignee_name }}</span>
                            </div>

                            <div>
                                <span>NIC/Passport Number</span>
                                <label class="relative flex">
                                    <input
                                        v-model="form.consignee_nic"
                                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="NIC/PP Number"
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
                                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                            />
                                        </svg>
                                    </div>
                                </label>
                                <span
                                    v-if="form.errors.consignee_nic"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.consignee_nic }}</span>
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
                                        v-model="form.consignee_contact"
                                        class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                        placeholder="123 4567 890"
                                        type="text"
                                    />
                                </div>
                                <span
                                    v-if="form.errors.consignee_contact"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.consignee_contact }}</span>
                            </div>

                            <div class="col-span-2">
                                <span>Address</span>
                                <label class="block">
                                    <textarea
                                        v-model="form.consignee_address"
                                        rows="4"
                                        placeholder="Type address here..."
                                        class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    ></textarea>
                                </label>
                                <span
                                    v-if="form.errors.consignee_address"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.consignee_address }}</span>
                            </div>

                            <div class="col-span-2">
                                <span>Note</span>
                                <label class="block">
                                    <textarea
                                        v-model="form.consignee_note"
                                        rows="4"
                                        placeholder="Type note here..."
                                        class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                    ></textarea>
                                </label>
                                <span
                                    v-if="form.errors.consignee_name"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.consignee_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 space-y-5">
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-5">
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
                            <span>Create a HBL</span>
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

                    <!-- Cargo Type -->
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
                                    v-for="cargoType in cargoTypes"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.cargo_type"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="cargo_type"
                                        :value="cargoType"
                                        type="radio"
                                    />
                                    <p>{{ cargoType }}</p>
                                </label>
                            </div>
                            <span
                                v-if="form.errors.cargo_type"
                                class="text-tiny+ text-error"
                            >{{ form.errors.cargo_type }}</span>
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="card px-4 py-4 sm:px-5">
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Type
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label
                                    v-for="hblType in hblTypes"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.hbl_type"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="hbl_type"
                                        :value="hblType"
                                        type="radio"
                                    />
                                    <p>{{ hblType }}</p>
                                </label>
                            </div>
                            <span
                                v-if="form.errors.hbl_type"
                                class="text-tiny+ text-error"
                            >{{ form.errors.hbl_type }}</span>
                        </div>
                    </div>

                    <!-- Warehouse -->
                    <div class="card px-4 py-4 sm:px-5">
                        <div>
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Warehouse
                            </h2>
                        </div>
                        <div class="my-5">
                            <div class="space-x-5">
                                <label
                                    v-for="warehouse in warehouses"
                                    class="inline-flex items-center space-x-2"
                                >
                                    <input
                                        v-model="form.warehouse"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                        name="warehouse"
                                        :value="warehouse"
                                        type="radio"
                                    />
                                    <p>{{ warehouse }}</p>
                                </label>
                            </div>
                            <span
                                v-if="form.errors.warehouse"
                                class="text-tiny+ text-error"
                            >{{ form.errors.warehouse }}</span>
                        </div>
                    </div>

                    <!-- Price & Payment -->
                    <div class="card px-4 py-4 sm:px-5">
                        <div class="flex justify-between items-center">
                            <h2
                                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                            >
                                Price and Payment
                            </h2>
                            <button
                                class="btn border border-primary font-medium text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white active:bg-primary/90"
                            >
                                Calculate Payment
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-5 mt-5">
                            <div>
                                <span>Freight Charge</span>
                                <label class="block">
                                    <input
                                        v-model="form.freight_charge"
                                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        type="number"
                                        min="0"
                                    />
                                </label>
                                <span
                                    v-if="form.errors.freight_charge"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.freight_charge }}</span>
                            </div>

                            <div>
                                <span>Bill Charge</span>
                                <label class="block">
                                    <input
                                        v-model="form.bill_charge"
                                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        type="number"
                                        min="0"
                                    />
                                </label>
                                <span
                                    v-if="form.errors.bill_charge"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.bill_charge }}</span>
                            </div>

                            <div>
                                <span>Other Charge</span>
                                <label class="block">
                                    <input
                                        v-model="form.other_charge"
                                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        type="number"
                                        min="0"
                                    />
                                </label>
                                <span
                                    v-if="form.errors.other_charge"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.other_charge }}</span>
                            </div>

                            <div>
                                <span>Discount</span>
                                <label class="block">
                                    <input
                                        v-model="form.discount"
                                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        placeholder="0"
                                        type="number"
                                    />
                                </label>
                                <span
                                    v-if="form.errors.discount"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.discount }}</span>
                            </div>

                            <div class="col-span-2">
                                <span>Paid Amount</span>
                                <label class="block">
                                    <input
                                        v-model="form.paid_amount"
                                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        type="number"
                                        min="0"
                                    />
                                </label>
                                <span
                                    v-if="form.errors.paid_amount"
                                    class="text-tiny+ text-error"
                                >{{ form.errors.paid_amount }}</span>
                            </div>

                            <div class="col-start-2 mt-2 space-y-2.5 font-bold">
                                <div class="flex justify-between">
                                    <p class="line-clamp-1">Packages</p>
                                    <p class="text-slate-700 dark:text-navy-100">
                                        2
                                    </p>
                                </div>
                                <div class="flex justify-between">
                                    <p class="line-clamp-1">Weight</p>
                                    <p class="text-slate-700 dark:text-navy-100">
                                        1
                                    </p>
                                </div>
                                <div class="flex justify-between">
                                    <p class="line-clamp-1">Volume</p>
                                    <p class="text-slate-700 dark:text-navy-100">
                                        0.01
                                    </p>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <div class="flex justify-between text-2xl text-success font-bold">
                                    <p class="line-clamp-1">Grand Total</p>
                                    <p>
                                        48 SAR
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>

<style scoped></style>
