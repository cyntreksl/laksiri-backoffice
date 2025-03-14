<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed} from "vue";
import TextInput from "@/Components/TextInput.vue";
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    selectedCouriers: {
        type: Object,
        default: () => {
        },
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    couriers: [],
    status: '',
});

const handleChangeStatus = () => {
    form.couriers = props.selectedCouriers;
    form.post(route("couriers.change-status"), {
        onSuccess: () => {
            push.success('Courier status successfully!');
            emit('close');
            router.visit(route("couriers.index"));
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'4xl'" :show="show" @close="$emit('close')">
        <template #title>
            <div class="flex justify-between items-center">
                <div>Change Status Review</div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

        </template>
        <template #content>

            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                    <tr>
                        <th
                            class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Courier Number
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Name
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Courier Agent
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Cargo Type
                        </th>
                        <th
                            class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            HBL Type
                        </th>
                        <th
                            class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                        >
                            Status
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(courier, index) in props.selectedCouriers" class="border border-transparent border-b-slate-200 dark:border-b-navy-500">
                        <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">{{ courier[2] }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">{{ courier[3] }}</td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ courier[14] }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ courier[15] }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                            {{ courier[16] }}
                        </td>
                        <td class="whitespace-nowrap rounded-r-lg px-4 py-3 sm:px-5">
                            {{ courier[17] }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="grid grid-cols-4 gap-5 mt-8">
                <div class="col-span-2">
                    <InputLabel class="col-span-3" value="Change Status To"/>
                    <div class="my-2">
                        <div class="space-x-5">
                            <select
                                v-model="form.status"
                                class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option :value="null" disabled>Select Courier Agent</option>
                                <option value="pending">Pending</option>
                                <option value="on courier">On Courier</option>
                                <option value="delivered">Delivered</option>
                            </select>

                        </div>
                        <InputError :message="form.errors.status"/>
                    </div>
                    <InputError :message="form.errors.status"/>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Cancel
                </SecondaryButton>
                <PrimaryButton @click.prevent="handleChangeStatus">
                    Change Status
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
