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
  zones: {
    type: Object,
    default: () => {},
  },
});

console.log(props.user);

const form = useForm({
  name: props.user.name,
  contact: props.user.contact,
  working_hours_start: props.user.working_hours_start,
  working_hours_end: props.user.working_hours_end,
  preferred_zone: props.user.preferred_zone.split(","),
});

const handleUpdateDriver = () => {
  form.put(route("users.driver.update", props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      notification({
        text: "Driver Details Updated Successfully!",
        variant: "success",
      });
      // router.visit(route("users.edit", props.user.id));
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
        Driver Details
      </h2>
      <br />
    </div>

    <form @submit.prevent="handleUpdateDriver">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
          <InputError :message="form.errors.name" />
        </div>

        <div>
          <InputLabel value="Mobile" />
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
              <svg
                class="size-4.5 transition-colors duration-200"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
          </label>
          <span class="text-tiny+ text-slate-400 dark:text-navy-300"
            >This number will appear in SMS of customers.</span
          >
          <InputError :message="form.errors.contact" />
        </div>

        <div>
          <InputLabel value="Working Hours Start" />
          <label class="relative flex">
            <input
              v-model="form.working_hours_start"
              class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
              placeholder="Working Hours Start"
              type="text"
            />
          </label>
          <span class="text-tiny+ text-slate-400 dark:text-navy-300"
            >For driver location tracking purpose</span
          >
          <InputError :message="form.errors.working_hours_start" />
        </div>

        <div>
          <InputLabel value="Working Hours End" />
          <label class="relative flex">
            <input
              v-model="form.working_hours_end"
              class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
              placeholder="Working Hours End"
              type="text"
            />
          </label>
          <span class="text-tiny+ text-slate-400 dark:text-navy-300"
            >Tracking will be only in working hours</span
          >
          <InputError :message="form.errors.working_hours_end" />
        </div>

        <div class="col-span-1 sm:col-span-2">
          <label class="block">
            <InputLabel value="Preferred Zone" />
            <select
              v-model="form.preferred_zone"
              autocomplete="off"
              multiple
              placeholder="Preferred Zone..."
              x-init="$el._tom = new Tom($el,{create:true,plugins: ['caret_position','input_autogrow','remove_button']})"
            >
              <option v-for="zone in zones" :key="zone.id" :value="zone.name">
                {{ zone.name }}
              </option>
            </select>
          </label>
          <span class="text-tiny+ text-slate-400 dark:text-navy-300"
            >Comma separated values. Auto Zone Assignments, it will come as a
            notification</span
          >
          <InputError :message="form.errors.preferred_zone" />
        </div>

        <div class="flex col-span-2 justify-end">
          <PrimaryButton
            type="submit"
            class="ms-3"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Update Driver Details
          </PrimaryButton>
        </div>
      </div>
    </form>
  </div>
</template>
