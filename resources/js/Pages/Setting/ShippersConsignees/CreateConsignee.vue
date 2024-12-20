<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { router, useForm } from "@inertiajs/vue3";
import {computed, ref} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { push } from "notivue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
  confirmShipperCreation: {
    type: Boolean,
    required: true,
  },
  countryCodes: {
    type: Array,
    default: () => [],
  }
})
const confirmShipperCreation = ref(false);
const countryCode = ref('+94');
const contactNumber = ref("");

const closeModal = () => (confirmShipperCreation.value = false);

const form = useForm({
  type: "consignee",
  name: "",
  email: "",
  mobile_number: computed(() => countryCode.value + contactNumber.value),
  address: "",
  description: "",
});



const createShipper = () => {
  form.post(route("setting.shipper-consignees.store"), {
    onSuccess: () => {
      closeModal();
      form.reset();
      router.visit(route("setting.shipper-consignees.index"));
      push.success("Consignee Created Successfully");
    },
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <div class="flex justify-end mx-5 mt-4">
    <PrimaryButton @click="confirmShipperCreation = !confirmShipperCreation">
      Create New Consignee
    </PrimaryButton>
  </div>

  <DialogModal :maxWidth="'xl'" :show="confirmShipperCreation" @close="closeModal">
    <template #title> Create New Consignee </template>
    <template #content>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <!-- Name Field -->
        <div class="col-span-2">
          <InputLabel for="name" value="Name" />
          <TextInput
              v-model="form.name"
              id="name"
              type="text"
              class="w-full"
              placeholder="Name"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- Passport/NIC Number Field -->
        <div class="col-span-2">
          <InputLabel for="pp_or_nic_no" value="PP or NIC No" />
          <TextInput
              v-model="form.pp_or_nic_no"
              id="pp_or_nic_no"
              type="text"
              class="w-full"
              placeholder="PP or NIC No"
          />
          <InputError :message="form.errors.pp_or_nic_no" />
        </div>

        <!-- Mobile Number Field -->
        <div class="col-span-2">
          <InputLabel for="mobile_number" value="Mobile Number" />
          <div class="flex items-center space-x-2">
            <select
                v-model="countryCode"
                class="form-select rounded-md border border-gray-300 bg-white px-8 py-2 focus:ring-primary focus:border-primary"
            >
              <option v-for="code in countryCodes" :key="code" :value="code">
                {{ code }}
              </option>
            </select>
            <TextInput
                v-model="contactNumber"
                id="mobile_number"
                type="text"
                class="w-full border border-gray-300 rounded-md focus:ring-primary focus:border-primary"
                placeholder="123 4567 890"
            />
          </div>
          <InputError :message="form.errors.mobile_number" />
        </div>

        <!-- Address Field -->
        <div class="col-span-2">
          <InputLabel for="address" value="Address" />
          <textarea
              v-model="form.address"
              id="address"
              class="w-full border-gray-300 rounded-md"
              placeholder="Type address here..."
              rows="3"
          ></textarea>
          <InputError :message="form.errors.address" />
        </div>

        <!-- Note Field -->
        <div class="col-span-2">
          <InputLabel for="note" value="Note" />
          <textarea
              v-model="form.description"
              id="note"
              class="w-full border-gray-300 rounded-md"
              placeholder="Type note here..."
              rows="3"
          ></textarea>
          <InputError :message="form.errors.description" />
        </div>
      </div>
    </template>
    <template #footer>
      <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
      <PrimaryButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          class="ms-3"
          @click="createShipper"
      >
        Create Consignee
      </PrimaryButton>
    </template>
  </DialogModal>
</template>

<style scoped></style>
