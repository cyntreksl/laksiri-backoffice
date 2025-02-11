<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import {computed, ref} from "vue";


const props = defineProps({
    officer: {
        type: Object,
        default: () => {
        },
    },
    countryCodes: {
        type: Array,
        default: () => [],
    }
});

const splitCountryCode = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            return code;
        }
    }
};
const splitContactNumber = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            return fullNumber.slice(code.length);
        }
    }
};

const countryCode = ref(splitCountryCode(props.officer.mobile_number));
const contactNumber = ref(splitContactNumber(props.officer.mobile_number));

const form = useForm({
    id: props.officer.id,
    name: props.officer.name,
    type: props.officer.type,
    email: props.officer.email,
    mobile_number:computed(() => countryCode.value + contactNumber.value),
    pp_or_nic_no: props.officer.pp_or_nic_no,
    residency_no: props.officer.residency_no,
    address:props.officer.address,
    description:props.officer.description,
});


const updateOfficer = () => {
    form.put(route("setting.shipper-consignees.update", props.officer.id), {
        onSuccess: () => {
            router.visit(route("setting.shipper-consignees.index"));
            form.reset();
            push.success("Officer Updated Successfully!");
        },
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Officer ">
        <template #header>Officer Edit</template>

        <!-- Breadcrumb -->
        <Breadcrumb :ExceptionName="officer"/>

        <div class="grid grid-cols-1 mt-4 gap-4">
            <div class="card px-4 py-4 sm:px-5">
                <div>
                    <h2
                    >
                        Update {{ officer.type }}
                    </h2>
                    <br/>
                </div>
                <form @submit.prevent="updateOfficer">
                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-5" v-if="officer.type === 'shipper'">
                        <div class="col-span-3">
                            <InputLabel for="name" value="Name"/>
                            <div class="flex items-center border border-gray-300 rounded-md px-2">
                                <TextInput
                                    v-model="form.name"
                                    id="name"
                                    type="text"
                                    class="w-full border-0 focus:ring-0"
                                    placeholder="Name"
                                />
                            </div>
                            <InputError :message="form.errors.name"/>
                        </div>

                        <!-- Email Field -->
                        <div class="col-span-3">
                            <InputLabel for="email" value="Email"/>
                            <div class="flex items-center border border-gray-300 rounded-md px-2">
                                <TextInput
                                    v-model="form.email"
                                    id="email"
                                    type="email"
                                    class="w-full border-0 focus:ring-0"
                                    placeholder="Email"
                                />
                            </div>
                            <InputError :message="form.errors.email"/>
                        </div>

                        <!-- Mobile Number Field -->
                        <div class="col-span-2">
                            <div class="grid grid-cols-1 sm:grid-cols-3">
                                <InputLabel class="col-span-3" for="mobile_number" value="Mobile Number"/>
                                <div>
                                    <select
                                        v-model="countryCode"
                                        x-init="$el._tom = new Tom($el)"
                                        class="w-full rounded-r-0"
                                    >
                                        <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                                            {{ countryCode }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <input
                                        v-model="contactNumber"
                                        class="form-input rounded-l-lg w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                        placeholder="123 4567 890"
                                        type="text"
                                    />
                                </div>
                                <InputError class="col-span-3" :message="form.errors.mobile_number"/>
                            </div>
                        </div>

                        <!-- Passport/NIC Field -->
                        <div class="col-span-2">
                            <InputLabel for="pp_or_nic_no" value="PP or NIC No"/>
                            <TextInput
                                v-model="form.pp_or_nic_no"
                                id="pp_or_nic_no"
                                type="text"
                                class="w-full"
                                placeholder="PP or NIC No"
                            />
                            <InputError :message="form.errors.pp_or_nic_no"/>
                        </div>

                        <!-- Residency No Field -->
                        <div class="col-span-2">
                            <InputLabel for="residency_no" value="Residency No"/>
                            <TextInput
                                v-model="form.residency_no"
                                id="residency_no"
                                type="text"
                                class="w-full"
                                placeholder="Residency No"
                            />
                            <InputError :message="form.errors.residency_no"/>
                        </div>

                        <!-- Address Field -->
                        <div class="col-span-6">
                            <InputLabel for="address" value="Address"/>
                            <textarea
                                v-model="form.address"
                                id="address"
                                class="w-full border-gray-300 rounded-md"
                                placeholder="Type address here..."
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.address"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" v-else>
                        <div class="col-span-2">
                            <InputLabel for="name" value="Name"/>
                            <TextInput
                                v-model="form.name"
                                id="name"
                                type="text"
                                class="w-full"
                                placeholder="Name"
                            />
                            <InputError :message="form.errors.name"/>
                        </div>

                        <!-- Passport/NIC Number Field -->
                        <div class="col-span-1">
                            <InputLabel for="pp_or_nic_no" value="PP or NIC No"/>
                            <TextInput
                                v-model="form.pp_or_nic_no"
                                id="pp_or_nic_no"
                                type="text"
                                class="w-full"
                                placeholder="PP or NIC No"
                            />
                            <InputError :message="form.errors.pp_or_nic_no"/>
                        </div>

                        <!-- Mobile Number Field -->
                        <div class="col-span-1">
                            <InputLabel for="mobile_number" value="Mobile Number"/>
                            <div class="flex space-x-px">
                                <select
                                    v-model="countryCode"
                                    class="form-select rounded-l-lg border border-slate-300 bg-white px-3 py-2 pr-9 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option v-for="code in countryCodes" :key="code" :value="code">
                                        {{ code }}
                                    </option>
                                </select>
                                <input
                                    v-model="contactNumber"
                                    id="mobile_number"
                                    type="text"
                                    class="form-input w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                                    placeholder="123 4567 890"
                                />
                            </div>
                            <InputError :message="form.errors.mobile_number"/>
                        </div>

                        <!-- Address Field -->
                        <div class="col-span-1">
                            <InputLabel for="address" value="Address"/>
                            <textarea
                                v-model="form.address"
                                id="address"
                                class="w-full border-gray-300 rounded-md"
                                placeholder="Type address here..."
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.address"/>
                        </div>

                        <!-- Note Field -->
                        <div class="col-span-1">
                            <InputLabel for="note" value="Note"/>
                            <textarea
                                v-model="form.description"
                                id="note"
                                class="w-full border-gray-300 rounded-md"
                                placeholder="Type note here..."
                                rows="3"
                            ></textarea>
                            <InputError :message="form.errors.description"/>
                        </div>
                    </div>
                    <br/>
                    <div class="flex col-span-2 justify-end">
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            class="ms-3"
                            type="submit"
                        >
                            Update Officer
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

