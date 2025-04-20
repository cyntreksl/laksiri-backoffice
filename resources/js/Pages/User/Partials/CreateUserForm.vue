<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm, usePage} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Dialog from "primevue/dialog";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    roles: {
        type: Object,
        default: () => {
        }
    },
    branches: {
        type: Object,
        default: () => {
        }
    },
    userRole: {
        type: String,
        required: false
    },
    userBranch: {
        type: Number,
        required: false
    },
    isSuperAdmin: {
        type: Boolean,
        required: false
    },
})

const emit = defineEmits(["update:visible"]);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    primary_branch_id: props.userRole === 'admin' ? '' : props.userBranch,
    secondary_branches: props.userRole === 'admin' ? [] : [props.userBranch],
    role: '',
});

const createUser = () => {
    form.post(route("users.store"), {
        onSuccess: () => {
            closeModal();
            router.visit(route("users.index"));
            form.reset();
            push.success('User Created Successfully!');
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
    <Dialog :style="{ width: '80rem' }" :visible="visible" header="Create New User" modal @update:visible="(newValue) => $emit('update:visible', newValue)">
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
                        <svg class="size-4.5 transition-colors duration-200" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                </label>
                <InputError :message="form.errors.username"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Email"/>
                <label class="relative flex">
                    <input
                        v-model="form.email"
                        class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        placeholder="Email Address"
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

            <div v-if="userRole === 'admin'">
                <label class="block z-50">
                    <InputLabel value="Select Primary Branch"/>
                    <select
                        v-model="form.primary_branch_id"
                        autocomplete="off"
                        class="w-full"
                        placeholder="Select a primary branch..."
                        x-init="$el._tom = new Tom($el)"
                    >
                        <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </label>
                <InputError :message="form.errors.primary_branch_id"/>
            </div>

            <div v-if="isSuperAdmin">
                <label class="block z-50">
                    <InputLabel value="Select Secondary Branch"/>
                    <select
                        v-model="form.secondary_branches"
                        autocomplete="off"
                        class="w-full"
                        multiple
                        placeholder="Select a secondary branch..."
                        x-init="$el._tom = new Tom($el,{
            plugins: ['remove_button'],
            create: true,
          })"
                    >
                        <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </label>
                <InputError :message="form.errors.secondary_branches"/>
            </div>

            <div class="col-span-1 sm:col-span-2">
                <InputLabel value="Select Role" />
                <div class="grid grid-cols-3 gap-4 mt-1">
                    <template v-for="(role, index) in roles" :key="index">
                        <label
                            v-if="usePage().props?.auth.user.active_branch_type !== 'destination' || (role.name !== 'call center' && role.name !== 'boned area')"
                            class="inline-flex items-center space-x-2"
                        >
                            <input
                                v-model="form.role"
                                :value="role.name"
                                class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                name="role"
                                type="radio"
                            />
                            <p class="capitalize">{{ role.name }}</p>
                        </label>
                    </template>
                </div>
                <InputError :message="form.errors.role" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <SecondaryButton @click="emit('close')">
                Cancel
            </SecondaryButton>
            <PrimaryButton
                class="ms-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="createUser"
            >
                Create User
            </PrimaryButton>
        </div>
    </Dialog>
</template>
