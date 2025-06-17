<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import hblImage from "../../../images/illustrations/hblimage.png";
import Fieldset from "primevue/fieldset";
import SelectButton from "primevue/selectbutton";
import Card from "primevue/card";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import Select from "primevue/select";
import InputLabel from "@/Components/InputLabel.vue";
import ContainerCreateDialog from "@/Pages/Container/ContainerCreateDialog.vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import {ref} from "vue";

const props = defineProps({
  agents: {
    type: Object,
    default: () => {
    },
  },
  hblTypes: {
    type: Object,
    default: () => {
    },
  },
  cargoTypes: {
    type: Object,
    default: () => {
    },
  },
  shipments: {
    type: Object,
    default: () => {
    },
  },
  airLines: {
    type: Object,
    default: () => {
    },
  },
});

const form = useForm({
  cargo_type: null,
  hbl_type: null,
  warehouse: null,
  agent: null,
  shipment: null,
  errors: {}
});

const showCreateShipmentDialog = ref(false);
</script>

<template>
  <AppLayout title="Create Third Party Shipment">
    <template #header>Create Third Party Shipment</template>

    <main class="kanban-app w-full">
      <div
          class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
        <div class="flex items-center space-x-1">
          <h3 class="text-lg font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
            Create Third Party Shipment
          </h3>
        </div>
      </div>

      <form @submit.prevent="handleHBLCreate">
        <div class="grid grid-cols-3 my-4 gap-4">
          <div class="grid grid-rows gap-4">
            <Card>
              <template #title>Primary Details</template>
              <template #content>
                <Fieldset legend="Cargo Type">
                  <SelectButton v-model="form.cargo_type" :options="cargoTypes" name="Cargo Type">
                    <template #option="slotProps">
                      <div class="flex items-center">
                        <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                        <i v-else class="ti ti-plane mr-2"></i>
                        <span>{{ slotProps.option }}</span>
                      </div>
                    </template>
                  </SelectButton>
                  <InputError :message="form.errors.cargo_type"/>
                </Fieldset>

                <Fieldset legend="Type">
                  <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type"/>
                  <InputError :message="form.errors.hbl_type"/>
                </Fieldset>

                <Fieldset legend="Agent">
                  <div class="col-span-4">
                    <Select
                        v-model="form.agent"
                        :options="agents"
                        :option-label="(option) => option.name"
                        :required="true"
                        class="w-full"
                        filter
                        option-value="id"
                        placeholder="Select Agent"
                    />
                  </div>
                </Fieldset>
                <div class="flex justify-center mt-36">
                  <img :src="hblImage" alt="hbl-image" class="w-3/4">
                </div>
              </template>
            </Card>
          </div>

          <div class="col-span-2 ">
            <Card class="mb-4">
              <template #title>
                <div class="flex items-center justify-between w-full">
                  <span>Shipment Details</span>
                  <Button :disabled="!form.cargo_type" label="Create Shipment" class="p-button-success ml-auto" @click="showCreateShipmentDialog = true" />
                </div>
              </template>
              <template #content>
                <Fieldset legend="Shipment">
                  <div class="col-span-4">
                    <Select
                        v-model="form.shipment"
                        :options="props.shipments"
                        :option-label="(option) => option.container_number"
                        :required="true"
                        class="w-full"
                        filter
                        option-value="id"
                        placeholder="Select Shipment"
                    />
                  </div>
                </Fieldset>

                <div>

                </div>
              </template>
            </Card>

            <Card class="mb-4">
              <template #title>Package Details</template>
              <template #content>
              </template>
            </Card>

            <Card  class="mb-4">
              <template #title>Payment Details</template>
              <template #content>
              </template>
            </Card>
          </div>
        </div>
      </form>

    </main>

    <Dialog v-model:visible="showCreateShipmentDialog" header="Create new shipment" modal>
      <div class="flex items-center gap-4 mb-4">
        <ContainerCreateDialog
            :warehouse="2"
            :air-lines="props.airLines"
            :cargo-type="form.cargo_type"
            @containerCreated="(shipment) => {
              showCreateShipmentDialog = false;
            }"
        />
      </div>
    </Dialog>
  </AppLayout>
</template>
