<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { useForm, router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue"
import InputError from "@/Components/InputError.vue";
import { ref, watchEffect } from "vue";
import DangerOutlineButton from "@/Components/DangerOutlineButton.vue";
import {push} from "notivue";
import Card from 'primevue/card';
import Button from "primevue/button";
import SelectButton from "primevue/selectbutton";
import InputLabel from "@/Components/InputLabel.vue";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";

const props = defineProps({
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
    container: {
        type: Array,
        required: true,
    },
    warehouses: {
        type: Object,
        default: () => {},
    },
})

const form = useForm({
    cargo_type: props.container.cargo_type,
    container_type:  props.container.container_type,
    reference: props.container.reference,
    bl_number: props.container.bl_number,
    awb_number: props.container.awb_number,
    estimated_time_of_departure: props.container.estimated_time_of_departure,
    estimated_time_of_arrival: props.container.estimated_time_of_arrival,
    container_number: props.container.container_number,
    seal_number: props.container.seal_number,
    vessel_name: props.container.vessel_name,
    voyage_number: props.container.voyage_number,
    shipping_line: props.container.shipping_line,
    port_of_loading: props.container.port_of_loading,
    port_of_discharge: props.container.port_of_discharge,
    flight_number: props.container.flight_number,
    airline_name: props.container.airline_name,
    airport_of_departure: props.container.airport_of_departure,
    airport_of_arrival: props.container.airport_of_arrival,
    cargo_class: props.container.cargo_class,
    target_warehouse: props.container.target_warehouse,
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
    form.put(route("loading.loading-containers.update", props.container.id), {
        onSuccess: () => {
            router.visit(route("loading.loading-containers.index"));
            form.reset();
            push.success('Container Updated Successfully!');
        },
        onError: () => push.error('Something went to wrong!'),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};

</script>
<template>
  <AppLayout v-if="$page.props.currentBranch.type === 'Destination'" title="Container Edit">
    <template #header>Container Edit</template>
    <Breadcrumb :container="container"/>

    <form @submit.prevent="handleCreate">
      <div class="grid grid-cols-1 sm:grid-cols-5 my-4 gap-4">
        <div class="sm:col-span-2 space-y-5">
          <!--Cargo Type-->
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
            <div class="card px-4 py-4 sm:px-5">
                <div>
                    <h2
                        class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                    >
                        Target Warehouse
                    </h2>
                </div>
                <div class="my-5">
                    <div class="space-x-5">
                        <div class="grid grid-cols-3 gap-4">
                            <label
                                v-for="warehouse in warehouses"
                                :key="warehouse.id"
                                class="inline-flex items-center space-x-2"
                            >
                                <input
                                    v-model="form.target_warehouse"
                                    :value="warehouse.id"
                                    class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                    name="target_warehouse"
                                    type="radio"
                                />
                                <p>{{ warehouse.name }}</p>
                            </label>
                        </div>
                    </div>
                    <InputError :message="form.errors.target_warehouse" />
                </div>
            </div>

          <!-- Container Type-->
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
              <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                <span>Container Number</span>
                <label class="block">
                  <input
                    v-model="form.container_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    type="text"
                  />
                </label>
                <InputError :message="form.errors.container_number" />
              </div>
              <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                <span>Seal Number</span>
                <label class="block">
                  <input
                    v-model="form.seal_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    type="text"
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
                    type="text"
                  />
                </label>
                <InputError :message="form.errors.bl_number" />
              </div>

              <div v-else class="col-span-2">
                <span>AWB Number</span>
                <label class="block">
                  <input
                    v-model="form.awb_number"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    type="text"
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

          <!-- ship/flight details-->
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
              <span>Update Container</span>
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

    <AppLayout v-else title="Container Edit">
        <template #header>Container Edit</template>
        <Breadcrumb :container="container"/>

        <form @submit.prevent="handleCreate">
            <div class="grid grid-cols-1 sm:grid-cols-5 my-4 gap-4">
                <div class="sm:col-span-2 space-y-5">

                    <!--Cargo Type-->
                    <Card>
                        <template #title>Cargo Type</template>
                        <template #content>
                            <div class="my-3">
                                <SelectButton v-model="form.cargo_type" :options="cargoTypes" name="Cargo Type">
                                    <template #option="slotProps">
                                        <div class="flex items-center">
                                            <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                                            <i v-else class="ti ti-plane mr-2"></i>
                                            <span>{{ slotProps.option }}</span>
                                        </div>
                                    </template>
                                </SelectButton>
                                <InputError :message="form.errors.cargo_type" />
                            </div>
                        </template>
                    </Card>

                    <Card>
                        <template #title>Target Warehouse</template>
                        <template #content>
                            <div class="my-3">
                                <SelectButton v-model="form.target_warehouse" :options="warehouses" name="target_warehouse" option-label="name" option-value="id"/>
                                <InputError :message="form.errors.target_warehouse" />
                            </div>
                        </template>
                    </Card>

                    <!-- Container Type-->
                    <Card>
                        <template #title>Container Specs</template>
                        <template #content>
                            <div class="my-3">
                                <SelectButton v-model="form.container_type" :disabled="form.cargo_type === 'Air Cargo'" :options="containerTypes" name="container_type"/>
                                <InputError :message="form.errors.container_type" />
                            </div>
                        </template>
                    </Card>
                </div>

                <div class="sm:col-span-3 space-y-5">
                    <card>
                        <template #title>Container Details</template>
                        <template #content>
                            <div class="grid grid-cols-4 gap-5">
                                <div class="col-span-2">
                                    <InputLabel value="Reference"/>
                                    <InputText v-model="form.reference" class="w-full" placeholder="Enter Container Reference"/>
                                    <InputError :message="form.errors.reference" />
                                </div>
                                <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                                    <InputLabel value="Container Number"/>
                                    <InputText v-model="form.container_number" class="w-full" placeholder="Enter Container Number"/>
                                    <InputError :message="form.errors.container_number" />
                                </div>
                                <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                                    <InputLabel value="Seal Number"/>
                                    <InputText v-model="form.seal_number" class="w-full" placeholder="Enter Seal Number"/>
                                    <InputError :message="form.errors.seal_number" />
                                </div>
                                <div v-if="form.cargo_type === 'Sea Cargo'" class="col-span-2">
                                    <InputLabel value="BL Number"/>
                                    <InputText v-model="form.bl_number" class="w-full" placeholder="Enter BL Number"/>
                                    <InputError :message="form.errors.bl_number" />
                                </div>
                                <div v-else class="col-span-2">
                                    <InputLabel value="AWB Number"/>
                                    <InputText v-model="form.awb_number" class="w-full" placeholder="Enter AWB Number"/>
                                    <InputError :message="form.errors.awb_number" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Estimated Departure Date"/>
                                    <DatePicker v-model="form.estimated_time_of_departure" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set Estimated Departure Date" show-icon/>
                                    <InputError
                                        :message="form.errors.estimated_time_of_departure"
                                    />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Estimated Arrival Date to Destination"/>
                                    <DatePicker v-model="form.estimated_time_of_arrival" class="w-full mt-1" date-format="yy-mm-dd" icon-display="input" placeholder="Set Estimated Arrival Date" show-icon/>
                                    <InputError :message="form.errors.estimated_time_of_arrival" />
                                </div>

                            </div>
                        </template>
                    </card>

                    <!-- ship/flight details-->
                    <card v-if="form.cargo_type === 'Sea Cargo'">
                        <template #title>Vessel Details</template>
                        <template #content>
                            <div class="grid grid-cols-4 gap-5 mt-3">
                                <div class="col-span-2">
                                    <InputLabel value="Vessel Name"/>
                                    <InputText v-model="form.vessel_name" class="w-full" placeholder="Enter Vessel Name"/>
                                    <InputError :message="form.errors.vessel_name" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Voyage Number"/>
                                    <InputText v-model="form.voyage_number" class="w-full" placeholder="Enter Voyage Number"/>
                                    <InputError :message="form.errors.voyage_number" />
                                </div>
                                <div class="col-span-4">
                                    <InputLabel value="Shipping Line"/>
                                    <InputText v-model="form.shipping_line" class="w-full" placeholder="Enter Shipping Line"/>
                                    <InputError :message="form.errors.shipping_line" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Port of Loading"/>
                                    <InputText v-model="form.port_of_loading" class="w-full" placeholder="Enter Port of Loading"/>
                                    <InputError :message="form.errors.port_of_loading" />
                                </div>
                                <div class="col-span-2">
                                    <InputLabel value="Port of Discharge"/>
                                    <InputText v-model="form.port_of_discharge" class="w-full" placeholder="Enter Port of Discharge"/>
                                    <InputError :message="form.errors.port_of_discharge" />
                                </div>


                            </div>
                        </template>
                    </card>

                    <Card v-else>
                        <template #title>Flight Details</template>
                        <template #content>
                            <div class="grid grid-cols-4 gap-5 mt-3">

                                <div class="col-span-1">
                                    <InputLabel value="Flight Number"/>
                                    <InputText v-model="form.flight_number" class="w-full" placeholder="Enter Flight Number"/>
                                    <InputError :message="form.errors.flight_number" />
                                </div>

                                <div class="col-span-3">
                                    <InputLabel value="Airline Name"/>
                                    <InputText v-model="form.airline_name" class="w-full" placeholder="Enter Airline Name"/>
                                    <InputError :message="form.errors.airline_name" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Airport of Departure"/>
                                    <InputText v-model="form.airport_of_departure" class="w-full" placeholder="Enter Airport of Departure"/>
                                    <InputError :message="form.errors.airport_of_departure" />
                                </div>

                                <div class="col-span-2">
                                    <InputLabel value="Airport of Arrival"/>
                                    <InputText v-model="form.airport_of_arrival" class="w-full" placeholder="Enter Airport of Arrival"/>
                                    <InputError :message="form.errors.airport_of_arrival" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-5">
                        <Button label="Cancel" severity="danger" variant="outlined" @click="router.visit(route('loading.loading-containers.index'))" />

                        <Button :class="{ 'opacity-50': form.processing }" :disabled="form.processing" icon="pi pi-arrow-right" iconPos="right" label="Update Container" type="submit" />
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
