<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { router, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { push } from "notivue";

const props = defineProps({
  driverareas: {
    type: Object,
    default: () => {},
  },
});

const form = useForm({
  id: props.driverareas.id,
  name: props.driverareas.name,
  branch_id: props.driverareas.branch_id,
});

const updatedriverarea = () => {
  form.post(route("setting.driver-areas.update"), {
    onSuccess: () => {
      router.visit(route("setting.driver-areas.index"));
      form.reset();
      push.success("Driver Area Updated Successfully!");
    },
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <div class="card px-4 py-4 sm:px-5">
    <div>
      <h2
        class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
      >
        Update Driver Area
      </h2>
      <br />
    </div>
    <form @submit.prevent="updatedriverarea">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="col-span-1 sm:col-span-2">
          <InputLabel value="Driver Area Name" />
          <label class="relative flex">
            <input
              v-model="form.name"
              class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
              placeholder="Driver Area Name"
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
      </div>
      <br />
      <div class="flex col-span-2 justify-end">
        <PrimaryButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          class="ms-3"
          type="submit"
        >
          Update Wareahouse Zone
        </PrimaryButton>
      </div>
    </form>
  </div>
</template>

