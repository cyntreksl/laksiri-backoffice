<script setup>
import {Head, Link, useForm} from "@inertiajs/vue3";
import logo from "../../../images/logo_main.png";
import DashboardMeet from "../../../images/illustrations/dashboard-meet.svg";
import DashboardMeetDark from "../../../images/illustrations/dashboard-meet-dark.svg";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    username: "",
    password: "",
    remember: false,
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            remember: form.remember ? "on" : "",
        }))
        .post(route("login"), {
            onFinish: () => form.reset("password"),
        });
};
</script>

<template>
    <Head title="Log in"/>
    <div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
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
        <div class="hidden w-full place-items-center lg:grid">
            <div class="w-full max-w-lg p-6">
                <h1 class="text-3xl font-bold text-center mb-5 text-black">Track Your HBL Status</h1>

                <div class="card mt-5 rounded-lg p-5 lg:p-7">
                    <div class="flex space-x-2">
                        <input
                            class="form-input peer rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent w-full"
                            placeholder="Enter HBL Reference" type="text" />
                        <PrimaryButton>
                            <svg  class="icon icon-tabler icons-tabler-outline icon-tabler-search mr-2"  fill="none"  height="24"  stroke="currentColor"  stroke-linecap="round"  stroke-linejoin="round"  stroke-width="2"  viewBox="0 0 24 24"  width="24"  xmlns="http://www.w3.org/2000/svg"><path d="M0 0h24v24H0z" fill="none" stroke="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            Find
                        </PrimaryButton>
                    </div>

                    <ol v-if="2 > 3" class="mt-10 timeline max-w-sm">
                        <li class="timeline-item">
                            <div
                                class="timeline-item-point rounded-full bg-slate-300 dark:bg-navy-400"
                            ></div>
                            <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                    <p
                                        class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                    >
                                        User Photo Changed
                                    </p>
                                    <span class="text-xs text-slate-400 dark:text-navy-300"
                                    >12 minute ago</span
                                    >
                                </div>
                                <p class="py-1">John Doe changed his avatar photo</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div
                                class="timeline-item-point rounded-full bg-primary dark:bg-accent"
                            ></div>
                            <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                    <p
                                        class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                    >
                                        Video Added
                                    </p>
                                    <span class="text-xs text-slate-400 dark:text-navy-300"
                                    >1 hour ago</span
                                    >
                                </div>
                                <p class="py-1">Mores Clarke added new video</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div class="timeline-item-point rounded-full bg-success">
        <span
            class="inline-flex h-full w-full animate-ping rounded-full bg-success opacity-80"
        ></span>
                            </div>
                            <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                    <p
                                        class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                    >
                                        Design Completed
                                    </p>
                                    <span class="text-xs text-slate-400 dark:text-navy-300"
                                    >3 hours ago</span
                                    >
                                </div>
                                <p class="py-1">
                                    Robert Nolan completed the design of the CRM application
                                </p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div class="timeline-item-point rounded-full bg-warning"></div>
                            <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                    <p
                                        class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                    >
                                        ER Diagram
                                    </p>
                                    <span class="text-xs text-slate-400 dark:text-navy-300"
                                    >a day ago</span
                                    >
                                </div>
                                <p class="py-1">Team completed the ER diagram app</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <div class="timeline-item-point rounded-full bg-error"></div>
                            <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                    <p
                                        class="pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0"
                                    >
                                        Weekly Report
                                    </p>
                                    <span class="text-xs text-slate-400 dark:text-navy-300"
                                    >a day ago</span
                                    >
                                </div>
                                <p class="py-1">The weekly report was uploaded</p>
                            </div>
                        </li>
                    </ol>
                </div>

                <img
                    :src="DashboardMeet"
                    alt="image"
                    class="w-full mt-10"
                    x-show="!$store.global.isDarkModeEnabled "
                />
                <img
                    :src="DashboardMeetDark"
                    alt="image"
                    class="w-full mt-10"
                    x-show="$store.global.isDarkModeEnabled"
                />
            </div>
        </div>
        <main
            class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md"
        >
            <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
                <div class="text-center">
                    <img :src="logo" alt="logo" class="mx-auto size-16"/>
                    <div class="mt-4">
                        <h2
                            class="text-2xl font-semibold text-slate-600 dark:text-navy-100"
                        >
                            Welcome Back
                        </h2>
                        <p class="text-slate-400 dark:text-navy-300">
                            Please sign in to continue
                        </p>
                    </div>
                </div>
                <form class="mt-16" @submit.prevent="submit">
                    <label class="relative flex">
                        <input
                            id="username"
                            v-model="form.username"
                            autocomplete="username"
                            autofocus
                            class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            placeholder="Username"
                            required
                            type="text"
                        />
                        <span
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                        >
              <svg
                  class="size-5 transition-colors duration-200"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
              >
                <path
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
              </svg>
            </span>
                    </label>
                    <InputError :message="form.errors.username" class="mt-2"/>
                    <label class="relative mt-4 flex">
                        <input
                            id="password"
                            v-model="form.password"
                            autocomplete="current-password"
                            class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            placeholder="Password"
                            required
                            type="password"
                        />
                        <span
                            class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                        >
              <svg
                  class="size-5 transition-colors duration-200"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
              >
                <path
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                />
              </svg>
            </span>
                    </label>
                    <InputError :message="form.errors.password" class="mt-2"/>
                    <div class="mt-4 flex items-center justify-between space-x-2">
                        <label class="inline-flex items-center space-x-2">
                            <input
                                v-model="form.remember"
                                class="form-checkbox is-outline size-5 rounded border-slate-400/70 bg-slate-100 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent"
                                name="remember"
                                type="checkbox"
                            />
                            <span class="line-clamp-1">Remember me</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs text-slate-400 transition-colors line-clamp-1 hover:text-slate-800 focus:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100 dark:focus:text-navy-100"
                        >
                            Forgot Password?
                        </Link>
                    </div>
                    <button
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="btn mt-10 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                    >
                        Sign In
                    </button>
                </form>
            </div>
            <div
                class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300"
            >
                <a href="#">Privacy Notice</a>
                <div class="mx-3 my-1 w-px bg-slate-200 dark:bg-navy-500"></div>
                <a href="#">Term of service</a>
            </div>
        </main>
    </div>
</template>
