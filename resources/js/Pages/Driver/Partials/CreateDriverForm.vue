<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";

const confirmingDriverCreation = ref(false);

const closeModal = () => {
    confirmingDriverCreation.value = false;
};

const form = useForm({
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
    working_hours_start: '',
    working_hours_end: '',
    preferred_zone: [],
    contact: '',
    role: 'driver',
});

const createDriver = () => {
    form.post(route("users.drivers.store"), {
        onSuccess: () => {
            closeModal();
            router.visit(route("users.drivers.index"));
            form.reset();
            push.success('Driver Created Successfully!');
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
    <div class="flex justify-end mx-5 mt-4">
        <PrimaryButton
            @click="confirmingDriverCreation = !confirmingDriverCreation"
        >
            Create New Driver
        </PrimaryButton>
    </div>

    <DialogModal :maxWidth="'5xl'" :show="confirmingDriverCreation" @close="closeModal">
        <template #title>
            Create New Driver
        </template>

        <template #content>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>
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
                            <svg class="size-4.5 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </label>
                    <InputError :message="form.errors.name"/>
                </div>

                <div>
                    <InputLabel value="Mobile"/>
                    <label class="relative flex">
                        <input
                            v-model="form.contact"
                            class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Mobile Number"
                            type="text"
                        />
                        <div
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                        >
                            <svg class="size-4.5 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </label>
                    <span class="text-tiny+ text-slate-400 dark:text-navy-300"
                    >This number will appear in SMS of customers.</span>
                    <InputError :message="form.errors.contact"/>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <InputLabel value="Username"/>
                    <label class="relative flex">
                        <input
                            v-model="form.username"
                            class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Username"
                            type="text"
                        />
                        <div
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                        >
                            <svg class="size-4.5 transition-colors duration-200" fill="none" stroke="currentColor"
                                 stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </label>
                    <InputError :message="form.errors.username"/>
                </div>

                <div>
                    <InputLabel value="Password"/>
                    <label class="relative flex">
                        <input
                            v-model="form.password"
                            class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Password"
                            type="password"
                        />
                        <div
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                        >
                            <svg class="size-4.5 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </label>
                    <InputError :message="form.errors.password"/>
                </div>

                <div>
                    <InputLabel value="Re-enter Password"/>
                    <label class="relative flex">
                        <input
                            v-model="form.password_confirmation"
                            class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Confirm Password"
                            type="password"
                        />
                        <div
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                        >
                            <svg class="size-4.5 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.5"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </label>
                    <InputError :message="form.errors.password_confirmation"/>
                </div>

                <div>
                    <InputLabel value="Working Hours Start"/>
                    <label class="relative flex">
                        <input
                            v-model="form.working_hours_start"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Working Hours Start"
                            type="text"
                        />
                    </label>
                    <span class="text-tiny+ text-slate-400 dark:text-navy-300"
                    >For driver location tracking purpose</span>
                    <InputError :message="form.errors.working_hours_start"/>
                </div>

                <div>
                    <InputLabel value="Working Hours End"/>
                    <label class="relative flex">
                        <input
                            v-model="form.working_hours_end"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Working Hours End"
                            type="text"
                        />
                    </label>
                    <span class="text-tiny+ text-slate-400 dark:text-navy-300"
                    >Tracking will be only in working hours</span>
                    <InputError :message="form.errors.working_hours_end"/>
                </div>

                <div class="col-span-1 sm:col-span-2">
                    <label class="block">
                        <InputLabel value="Preferred Zone"/>
                        <input
                            v-model="form.preferred_zone"
                            class="w-full"
                            placeholder="Preferred Zone"
                            type="text"
                            x-init="$el._tom = new Tom($el,{create:true,plugins: ['caret_position','input_autogrow','remove_button']})"
                        />
                    </label>
                    <span class="text-tiny+ text-slate-400 dark:text-navy-300"
                    >Comma separated values. Auto Zone Assignments, it will come as a notification</span>
                    <InputError :message="form.errors.preferred_zone"/>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="ms-3"
                @click="createDriver"
            >
                Create Driver
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
