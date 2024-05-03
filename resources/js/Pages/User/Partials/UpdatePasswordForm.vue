<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import notification from "@/magics/notification.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    user: {
        type: Object,
        default: () => {
        }
    },
})

const form = useForm({
    password: '',
    password_confirmation: '',
});

const handleUpdatePassword = () => {
    form.put(route("users.password.change", props.user.id), {
        onSuccess: () => {
            form.reset();
            notification({
                text: 'Password Updated Successfully!',
                variant: 'success',
            })
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <div class="card px-4 py-4 sm:px-5">
        <div>
            <h2
                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                Change Password
            </h2>
        </div>
        <form @submit.prevent="handleUpdatePassword" class="grid grid-cols-2 gap-5 mt-3">
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4.5 transition-colors duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-4.5 transition-colors duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                    </div>
                </label>
                <InputError :message="form.errors.password_confirmation"/>
            </div>

            <div class="flex col-span-2 justify-end">
                <PrimaryButton
                    type="submit"
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Change Password
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
