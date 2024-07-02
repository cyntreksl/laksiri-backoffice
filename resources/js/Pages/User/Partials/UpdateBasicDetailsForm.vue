<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import notification from "@/magics/notification.js";

const props = defineProps({
  user: {
    type: Object,
    default: () => {},
  },
  roles: {
    type: Object,
    default: () => {},
  },
  branches: {
    type: Object,
    default: () => {},
  },
});

const form = useForm({
  name: props.user.name,
  username: props.user.username,
  email: props.user.email,
  role_id: props.user.roles[0]?.id,
});

const handleUpdateUser = () => {
  form.put(route("users.update", props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      notification({
        text: "Basic Details Updated Successfully!",
        variant: "success",
      });
      router.visit(route("users.edit", props.user.id));
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
        Basic Details
      </h2>
    </div>
    <form
      @submit.prevent="handleUpdateUser"
      class="grid grid-cols-2 gap-5 mt-3"
    >
      <div>
        <InputLabel value="Name" />
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
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-4.5 transition-colors duration-200"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
              />
            </svg>
          </div>
        </label>
        <InputError :message="form.errors.name" />
      </div>

      <div>
        <InputLabel value="Username" />
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
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-4.5 transition-colors duration-200"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
              />
            </svg>
          </div>
        </label>
        <InputError :message="form.errors.username" />
      </div>

      <div>
        <InputLabel value="Email" />
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
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-4.5 transition-colors duration-200"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
              />
            </svg>
          </div>
        </label>
        <InputError :message="form.errors.email" />
      </div>

      <div>
        <InputLabel value="Select Role" />
        <div class="grid sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-3 mt-1">
          <label
            v-for="role in roles"
            class="inline-flex items-center space-x-2"
          >
            <input
              v-model="form.role_id"
              class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
              name="role"
              :value="role.id"
              type="radio"
            />
            <p class="capitalize">{{ role.name }}</p>
          </label>
        </div>
        <InputError :message="form.errors.role_id" />
      </div>

      <div class="flex col-span-2 justify-end">
        <PrimaryButton
          type="submit"
          class="ms-3"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Update User Basic Details
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>
