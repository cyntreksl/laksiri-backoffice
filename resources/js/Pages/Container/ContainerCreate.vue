<template>
  <AppLayout title="Container Create">
    <template #header>Container List</template>
    <Breadcrumb></Breadcrumb>

    <form @submit.prevent="handleCreate">
      <div class="grid grid-cols-1 sm:grid-cols-5 my-4 gap-4">
        <div class="sm:col-span-2 space-y-5">
          <!--                    Cargo Type-->
          <div class="card px-4 py-4 sm:px-5">
            <div>
              <h2
                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
              >
                Cargo Type
              </h2>
            </div>
            <div class="my-5">
              <div class="space-x-5">
                <label
                  v-for="cargoType in cargoTypes"
                  class="inline-flex items-center space-x-2"
                >
                  <input
                    v-model="form.cargo_type"
                    :value="cargoType"
                    class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                    name="cargo_type"
                    type="radio"
                  />
                  <p>{{ cargoType }}</p>
                </label>
              </div>
              <InputError :message="form.errors.cargo_type" />
            </div>
          </div>

          <!--                    Container Type-->
          <div class="card px-4 py-4 sm:px-5">
            <div>
              <h2
                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
              >
                Container Specs
              </h2>
            </div>
            <div class="my-5">
              <div class="grid sm:grid-cols-2 md:grid-cols-2">
                <label
                  v-for="containerType in containerTypes"
                  class="inline-flex items-center space-x-2 my-2"
                >
                  <input
                    v-model="form.container_type"
                    :checked="containerType === 'Custom'"
                    :value="containerType"
                    class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                    name="container_type"
                    type="radio"
                  />
                  <p>{{ containerType }}</p>
                </label>
              </div>
              <InputError :message="form.errors.container_type" />
            </div>
            <hr class="my-5 border-slate-400 dark:border-navy-500" />

            <div class="grid grid-cols-2 gap-5">
              <!--                            show volume and weight specs details-->
            </div>
          </div>
        </div>

        <div class="sm:col-span-3 space-y-5">
          <div class="card px-4 py-4 sm:px-5">
            <div>
              <h2
                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
              >
                Container Details
              </h2>
            </div>
            <div class="grid grid-cols-4 gap-5 mt-3">
              <div class="col-span-2">
                <span>Reference</span>

                <label class="block">
                  <input
                    v-model="form.reference"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    min="0"
                    type="text"
                  />
                </label>
                <InputError :message="form.errors.reference" />
              </div>
              <div class="col-span-2">
                <span>Container Number</span>
                <label class="block">
                  <input
                    v-model="form.container_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    min="0"
                    type="number"
                  />
                </label>
                <InputError :message="form.errors.container_number" />
              </div>
              <div class="col-span-2">
                <span>Seal Number</span>
                <label class="block">
                  <input
                    v-model="form.seal_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    min="0"
                    type="number"
                  />
                </label>
                <InputError :message="form.errors.seal_number" />
              </div>

              <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                <span>BL Number</span>
                <label class="block">
                  <input
                    v-model="form.bl_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    min="0"
                    type="number"
                  />
                </label>
                <InputError :message="form.errors.bl_number" />
              </div>

              <div v-else class="col-span-3">
                <span>AWB Number</span>
                <label class="block">
                  <input
                    v-model="form.awb_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    min="0"
                    type="number"
                  />
                </label>
                <InputError :message="form.errors.awb_number" />
              </div>

              <div class="col-span-2">
                <span>Estimated Departure Date</span>
                <label class="block">
                  <input
                    v-model="form.estimated_time_of_departure"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    type="date"
                  />
                </label>
                <InputError
                  :message="form.errors.estimated_time_of_departure"
                />
              </div>

              <div class="col-span-2">
                <span>Estimated Arrival Date to Destination</span>
                <label class="block">
                  <input
                    v-model="form.estimated_time_of_arrival"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    type="date"
                  />
                </label>
                <InputError :message="form.errors.estimated_time_of_arrival" />
              </div>
            </div>
          </div>

          <!--                    ship/flight details-->
          <div
            v-if="form.cargo_type === 'Sea Cargo'"
            class="sm:col-span-3 space-y-5"
          >
            <div class="card px-4 py-4 sm:px-5">
              <div>
                <h2
                  class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                >
                  Vessel Details
                </h2>
              </div>
              <div class="grid grid-cols-4 gap-5 mt-3">
                <div class="col-span-2">
                  <span>Vessel Name</span>
                  <label class="block">
                    <input
                      v-model="form.vessel_name"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.vessel_name" />
                </div>
                <div class="col-span-2">
                  <span>Voyage Number</span>
                  <label class="block">
                    <input
                      v-model="form.voyage_number"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.voyage_number" />
                </div>
                <div class="col-span-4">
                  <span>Shipping Line</span>
                  <label class="block">
                    <input
                      v-model="form.shipping_line"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.shipping_line" />
                </div>
                <div class="col-span-2">
                  <span>Port of Loading</span>
                  <label class="block">
                    <input
                      v-model="form.port_of_loading"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.port_of_loading" />
                </div>
                <div class="col-span-2">
                  <span>Port of Discharge</span>
                  <label class="block">
                    <input
                      v-model="form.port_of_discharge"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.port_of_discharge" />
                </div>
              </div>
            </div>
          </div>
          <div v-else class="sm:col-span-3 space-y-5">
            <div class="card px-4 py-4 sm:px-5">
              <div>
                <h2
                  class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                >
                  Flight Details
                </h2>
              </div>
              <div class="grid grid-cols-4 gap-5 mt-3">
                <div class="col-span-1">
                  <span>Flight Number</span>
                  <label class="block">
                    <input
                      v-model="form.flight_number"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.flight_number" />
                </div>
                <div class="col-span-3">
                  <span>Airline Name</span>
                  <label class="block">
                    <input
                      v-model="form.airline_name"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.airline_name" />
                </div>
                <div class="col-span-2">
                  <span>Airport of Departure</span>
                  <label class="block">
                    <input
                      v-model="form.airport_of_departure"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.airport_of_departure" />
                </div>
                <div class="col-span-2">
                  <span>Airport of Arrival</span>
                  <label class="block">
                    <input
                      v-model="form.airport_of_arrival"
                      class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                      type="text"
                    />
                  </label>
                  <InputError :message="form.errors.airport_of_arrival" />
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-5">
            <DangerOutlineButton
              @click="router.visit(route('loading.loading-containers.index'))"
              >Cancel</DangerOutlineButton
            >
            <PrimaryButton
              :class="{ 'opacity-50': form.processing }"
              :disabled="form.processing"
              class="space-x-2"
              type="submit"
            >
              <span>Create Container</span>
              <svg
                class="size-5"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </PrimaryButton>
          </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>
<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ColumnVisibilityPopover from "@/Components/ColumnVisibilityPopover.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import { ref, watch, watchEffect } from "vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";

export default {
  components: {
    DangerOutlineButton,
    InputError,
    Checkbox,
    ColumnVisibilityPopover,
    PrimaryButton,
    Link,
    Breadcrumb,
    AppLayout,
  },
  props: {
    cargoTypes: {
      type: Array,
      required: true,
    },
    seaContainerOptions: {
      type: Array,
      required: true,
    },
    airContainerOptions: {
      type: Array,
      required: true,
    },
    referenceNum: {
      type: Object,
    },
  },

  setup(props) {
    const form = useForm({
      cargo_type: ref("Sea Cargo"),
      container_type: ref(""),
      reference: props.referenceNum,
      bl_number: "",
      awb_number: "",
      estimated_time_of_departure: "",
      estimated_time_of_arrival: "",
      container_number: "",
      seal_number: "",
      vessel_name: "",
      voyage_number: "",
      shipping_line: "",
      port_of_loading: "",
      port_of_discharge: "",
      flight_number: "",
      airline_name: "",
      airport_of_departure: "",
      airport_of_arrival: "",
      departure_time: "",
      arrival_time: "",
      cargo_class: "",
    });

    const containerTypes = ref(props.seaContainerOptions);

    watchEffect(() => {
      if (form.cargo_type === "Sea Cargo") {
        containerTypes.value = props.seaContainerOptions;
      } else {
        containerTypes.value = props.airContainerOptions;
      }
    });

    const handleCreate = () => {
      form.post(route("loading.loading-containers.store"), {
        onSuccess: () => {
          router.visit(route("loading.loading-containers.index"));
          form.reset();
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
      });
    };

    return {
      form,
      handleCreate,
      containerTypes,
      router,
    };
  },
};
</script>
<style scoped>
</style>
