<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import logo from "../../../images/app-logo.svg";
import dashboardMeet from "../../../images/illustrations/dashboard-meet.svg";
import dashboardMeetDark from "../../../images/illustrations/dashboard-meet-dark.svg";

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <!--    <AuthenticationCard>-->
    <!--        <template #logo>-->
    <!--            <AuthenticationCardLogo />-->
    <!--        </template>-->

    <!--        <form @submit.prevent="submit">-->
    <!--            <div>-->
    <!--                <InputLabel for="name" value="Name" />-->
    <!--                <TextInput-->
    <!--                    id="name"-->
    <!--                    v-model="form.name"-->
    <!--                    type="text"-->
    <!--                    class="mt-1 block w-full"-->
    <!--                    required-->
    <!--                    autofocus-->
    <!--                    autocomplete="name"-->
    <!--                />-->
    <!--                <InputError class="mt-2" :message="form.errors.name" />-->
    <!--            </div>-->

    <!--            <div class="mt-4">-->
    <!--                <InputLabel for="email" value="Email" />-->
    <!--                <TextInput-->
    <!--                    id="email"-->
    <!--                    v-model="form.email"-->
    <!--                    type="email"-->
    <!--                    class="mt-1 block w-full"-->
    <!--                    required-->
    <!--                    autocomplete="username"-->
    <!--                />-->
    <!--                <InputError class="mt-2" :message="form.errors.email" />-->
    <!--            </div>-->

    <!--            <div class="mt-4">-->
    <!--                <InputLabel for="password" value="Password" />-->
    <!--                <TextInput-->
    <!--                    id="password"-->
    <!--                    v-model="form.password"-->
    <!--                    type="password"-->
    <!--                    class="mt-1 block w-full"-->
    <!--                    required-->
    <!--                    autocomplete="new-password"-->
    <!--                />-->
    <!--                <InputError class="mt-2" :message="form.errors.password" />-->
    <!--            </div>-->

    <!--            <div class="mt-4">-->
    <!--                <InputLabel for="password_confirmation" value="Confirm Password" />-->
    <!--                <TextInput-->
    <!--                    id="password_confirmation"-->
    <!--                    v-model="form.password_confirmation"-->
    <!--                    type="password"-->
    <!--                    class="mt-1 block w-full"-->
    <!--                    required-->
    <!--                    autocomplete="new-password"-->
    <!--                />-->
    <!--                <InputError class="mt-2" :message="form.errors.password_confirmation" />-->
    <!--            </div>-->

    <!--            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">-->
    <!--                <InputLabel for="terms">-->
    <!--                    <div class="flex items-center">-->
    <!--                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />-->

    <!--                        <div class="ms-2">-->
    <!--                            I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">Privacy Policy</a>-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <InputError class="mt-2" :message="form.errors.terms" />-->
    <!--                </InputLabel>-->
    <!--            </div>-->

    <!--            <div class="flex items-center justify-end mt-4">-->
    <!--                <Link :href="route('login')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">-->
    <!--                    Already registered?-->
    <!--                </Link>-->

    <!--                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">-->
    <!--                    Register-->
    <!--                </PrimaryButton>-->
    <!--            </div>-->
    <!--        </form>-->
    <!--    </AuthenticationCard>-->
    <div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
        <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
            <a href="#" class="flex items-center space-x-2">
                <img class="size-12 " :src="logo" alt="logo"/>
                <p class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100">
                    Lasksiri
                </p>
            </a>
        </div>
        <div class="hidden w-full place-items-center lg:grid">
            <div class="w-full max-w-lg p-6">
                <img class="w-full" x-show="!$store.global.isDarkModeEnabled" :src="dashboardMeet"
                     alt="image"/>
                <img class="w-full" x-show="$store.global.isDarkModeEnabled"
                     :src="dashboardMeetDark" alt="image"/>
            </div>
        </div>
        <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md">
            <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
                <div class="text-center">
                    <img class="mx-auto size-16 lg:hidden " src="{{asset('images/app-logo.svg')}}" alt="logo"/>
                    <div class="mt-4">
                        <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                            Welcome To Laksiri
                        </h2>
                        <p class="text-slate-400 dark:text-navy-300">
                            Please sign up to continue
                        </p>
                    </div>
                </div>

                <div class="my-7 flex items-center space-x-3">
                    <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                    <p class="text-tiny+ uppercase">Sign up with email</p>

                    <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                </div>
                <form @submit.prevent="submit">
                    <div class="mt-4 space-y-4">
                        <label class="relative flex">
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                placeholder="Name"
                                autocomplete="name"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            />
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.name"/>
                        <label class="relative flex">
                            <input
                                id="email"
                                v-model="form.email"
                                required
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                placeholder="Email" type="email"/>
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.email"/>
                        <label class="relative flex">
                            <input
                                id="password"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                placeholder="Password" type="password"/>
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.password"/>
                        <label class="relative flex">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                                placeholder="Repeat Password" type="password"/>
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.password_confirmation"/>
                        <div class="mt-4 flex items-center space-x-2">
                            <input
                                v-model="form.terms"
                                id="terms"
                                name="terms" required
                                class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                type="checkbox"/>
                            <p class="line-clamp-1">
                                I agree with
                                <a :href="route('policy.show')" target="_blank"
                                   class="text-slate-400 hover:underline dark:text-navy-300">
                                    privacy policy
                                </a>
                            </p>
                            <InputError class="mt-2" :message="form.errors.terms"/>
                        </div>
                    </div>
                    <button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                        class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                        Sign Up
                    </button>
                </form>
                <div class="mt-4 text-center text-xs+">
                    <p class="line-clamp-1">
                        <span>Already have an account? </span>
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="route('login')">Sign In
                        </Link>
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
