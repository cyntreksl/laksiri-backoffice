<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import logo from "../../../images/logo_main.png";
import DashboardCheck from "../../../images/illustrations/dashboard-check.svg";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

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
  <Head title="Log in" />

  <!--    <AuthenticationCard>-->
  <!--        <template #logo>-->
  <!--            <AuthenticationCardLogo />-->
  <!--        </template>-->

  <!--        <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">-->
  <!--            {{ status }}-->
  <!--        </div>-->

  <!--        <form @submit.prevent="submit">-->
  <!--            <div>-->
  <!--                <InputLabel for="email" value="Email" />-->
  <!--                <TextInput-->
  <!--                    id="email"-->
  <!--                    v-model="form.email"-->
  <!--                    type="email"-->
  <!--                    class="mt-1 block w-full"-->
  <!--                    required-->
  <!--                    autofocus-->
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
  <!--                    autocomplete="current-password"-->
  <!--                />-->
  <!--                <InputError class="mt-2" :message="form.errors.password" />-->
  <!--            </div>-->

  <!--            <div class="block mt-4">-->
  <!--                <label class="flex items-center">-->
  <!--                    <Checkbox v-model:checked="form.remember" name="remember" />-->
  <!--                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>-->
  <!--                </label>-->
  <!--            </div>-->

  <!--            <div class="flex items-center justify-end mt-4">-->
  <!--                <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">-->
  <!--                    Forgot your password?-->
  <!--                </Link>-->

  <!--                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">-->
  <!--                    Log in-->
  <!--                </PrimaryButton>-->
  <!--            </div>-->
  <!--        </form>-->
  <!--    </AuthenticationCard>-->

  <div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
    <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
      <a href="#" class="flex items-center space-x-2">
        <img class="size-12" :src="logo" alt="logo" />
        <p
          class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100"
        >
          Laksiri
        </p>
      </a>
    </div>
    <div class="hidden w-full place-items-center lg:grid">
      <div class="w-full max-w-lg p-6">
        <img
          class="w-full"
          x-show="!$store.global.isDarkModeEnabled "
          :src="DashboardCheck"
          alt="image"
        />
        <img
          class="w-full"
          x-show="$store.global.isDarkModeEnabled"
          src="{{asset('images/illustrations/dashboard-check-dark.svg')}}"
          alt="image"
        />
      </div>
    </div>
    <main
      class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md"
    >
      <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
        <div class="text-center">
          <img class="mx-auto size-16 lg:hidden" :src="logo" alt="logo" />
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
        <form @submit.prevent="submit" class="mt-16">
          <label class="relative flex">
            <input
              id="username"
              v-model="form.username"
              required
              autofocus
              autocomplete="username"
              class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
              placeholder="Username"
              type="text"
            />
            <span
              class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-5 transition-colors duration-200"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                />
              </svg>
            </span>
          </label>
          <InputError class="mt-2" :message="form.errors.username" />
          <label class="relative mt-4 flex">
            <input
              id="password"
              v-model="form.password"
              required
              autocomplete="current-password"
              class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
              placeholder="Password"
              type="password"
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
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
              </svg>
            </span>
          </label>
          <InputError class="mt-2" :message="form.errors.password" />
          <div class="mt-4 flex items-center justify-between space-x-2">
            <label class="inline-flex items-center space-x-2">
              <input
                v-model="form.remember"
                name="remember"
                class="form-checkbox is-outline size-5 rounded border-slate-400/70 bg-slate-100 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent"
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
            :disabled="form.processing"
            :class="{ 'opacity-25': form.processing }"
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
