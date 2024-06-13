<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { push } from "notivue";

const props = defineProps({
  user: {
    type: Object,
    default: () => {},
  },
});

const form = useForm({
  username: props.user.username,
  password: "",
  password_confirmation: "",
});

const handleUpdateDriverPassword = () => {
  form.put(route("users.driver.password.update", props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      push.success("Driver Password Updated Successfully");
      router.visit(route("users.drivers.index"));
    },
    onError: () => {
      form.reset();
    },
  });
};
</script>


<template>
  <div class="card px-4 py-4 sm:px-5">
    <div>
      <h2
        class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
      >
        Change Driver Password
      </h2>
      <br />
    </div>
    <form @submit.prevent="handleUpdateDriverPassword">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="col-span-1 sm:col-span-2">
          <InputLabel value="Username" />
          <label class="relative flex">
            <input
              v-model="form.username"
              class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
              placeholder="Username"
              type="text"
              :disabled="true"
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
                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
          </label>
          <InputError :message="form.errors.username" />
        </div>

        <div>
          <InputLabel value="Password" />
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
              <svg
                class="size-4.5 transition-colors duration-200"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
          </label>
          <InputError :message="form.errors.password" />
        </div>

        <div>
          <InputLabel value="Re-enter Password" />
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
              <svg
                class="size-4.5 transition-colors duration-200"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
          </label>
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <div class="flex col-span-2 justify-end">
          <PrimaryButton
            type="submit"
            class="ms-3"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Change Driver Password
          </PrimaryButton>
        </div>
      </div>
    </form>
  </div>
</template>
