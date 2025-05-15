<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import logo from "../../../images/logo_main.png";
import DashboardMeet from "../../../images/illustrations/dashboard-meet.svg";
import DashboardMeetDark from "../../../images/illustrations/dashboard-meet-dark.svg";

const recovery = ref(false);

const isDarkMode = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Two-factor Confirmation" />

    <!-- Outer Layout -->
    <div class="min-h-screen flex grow bg-slate-50 dark:bg-navy-900">
        <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
            <a class="flex items-center space-x-2" href="#">
                <img :src="logo" alt="logo" class="size-12"/>
                <p
                    class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100"
                >
                    Laksiri
                </p>

            </a>
        </div>

        <div class="hidden w-1/2 lg:block">
            <img
                :src="DashboardMeet"
                alt="image"
                class="w-[500px] ml-auto mr-16 mt-40"
                x-show="!$store.global.isDarkModeEnabled"
            />
            <img
                :src="DashboardMeetDark"
                alt="image"
                class="w-[500px] ml-auto mr-16 mt-40"
                x-show="$store.global.isDarkModeEnabled"
            />
        </div>


        <div class="flex w-full flex-col items-center justify-center bg-white dark:bg-navy-700 lg:w-1/4 lg:ml-auto">
            <!-- Authentication Card Wrapper -->
            <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
                <!-- Logo Section -->
                <div class="text-center">
                    <img :src="logo" alt="logo" class="mx-auto h-16 w-16"/>
                    <div class="mt-4">
                        <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">Confirm Two-Factor</h2>
                        <p class="text-slate-400 dark:text-navy-300">Enter your authentication or recovery code</p>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-4 mt-6 text-sm text-slate-500 dark:text-navy-300">
                    <template v-if="! recovery">
                        Please confirm access to your account by entering the authentication code provided by your authenticator application.
                    </template>

                    <template v-else>
                        Please confirm access to your account by entering one of your emergency recovery codes.
                    </template>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit">
                    <!-- Authentication Code Field -->
                    <div v-if="! recovery">
                        <label class="relative flex mt-4">
                            <input
                                ref="codeInput"
                                v-model="form.code"
                                type="text"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                autofocus
                                placeholder="Authentication Code"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                aria-label="Authentication Code"
                            />
                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <!-- Shield icon for 2FA -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.code" />
                    </div>

                    <!-- Recovery Code Field -->
                    <div v-else>
                        <label class="relative flex mt-4">
                            <input
                                ref="recoveryCodeInput"
                                v-model="form.recovery_code"
                                type="text"
                                autocomplete="one-time-code"
                                placeholder="Recovery Code"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                aria-label="Recovery Code"
                            />
                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <!-- Key icon for recovery code -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.recovery_code" />
                    </div>

                    <!-- Toggle & Submit Buttons (Separate) -->
                    <div class="mt-6 flex flex-col space-y-4">
                        <!-- Toggle Button -->
                        <button
                            type="button"
                            @click.prevent="toggleRecovery"
                            class="text-sm text-slate-500 transition-colors hover:text-slate-800 focus:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100 dark:focus:text-navy-100"
                        >
                            {{ recovery ? 'Use authentication code' : 'Use a recovery code' }}
                        </button>

                        <!-- Submit Button -->
                        <PrimaryButton
                            class="h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Confirm
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
