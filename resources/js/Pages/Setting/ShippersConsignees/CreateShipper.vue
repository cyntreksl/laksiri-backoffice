<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {computed, ref} from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
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
  type: "shipper",
  name: "",
  email: "",
  mobile_number: computed(() => countryCode.value + contactNumber.value),
  pp_or_nic_no: "",
  residency_no: "",
  address: ""

});
const createShipper = () => {
  form.post(route("setting.shipper-consignees.store"), {
    onSuccess: () => {
      closeModal();
      form.reset();
      router.visit(route("setting.shipper-consignees.index"));
      push.success("Shipper Created Successfully");
    },
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <div class="flex justify-end mx-5 mt-4">
    <PrimaryButton @click="confirmShipperCreation = !confirmShipperCreation">
      Create New Shipper
    </PrimaryButton>
  </div>

  <DialogModal :maxWidth="'xl'" :show="confirmShipperCreation" @close="closeModal">
    <template #title> Create New Shipper</template>
    <template #content>
      <div class="grid grid-cols-1 gap-5">
        <!-- Name Field -->
        <div>
          <InputLabel for="name" value="Name"/>
          <TextInput
              v-model="form.name"
              id="name"
              type="text"
              class="w-full border border-gray-300 rounded-md focus:ring-0"
              placeholder="Name"
          />
          <InputError :message="form.errors.name"/>
        </div>

        <!-- Email Field -->
        <div>
          <InputLabel for="email" value="Email"/>
          <TextInput
              v-model="form.email"
              id="email"
              type="email"
              class="w-full border border-gray-300 rounded-md focus:ring-0"
              placeholder="Email"
          />
          <InputError :message="form.errors.email"/>
        </div>
          <!-- Mobile Number Field -->
          <div >
              <div class="grid grid-cols-1 sm:grid-cols-3">
                  <InputLabel class="col-span-3" for="mobile_number" value="Mobile Number"/>
                  <div>
                      <select
                          v-model="countryCode"
                          x-init="$el._tom = new Tom($el)"
                          class="w-full rounded-r-0"
                      >
                          <option v-for="(countryCode, index) in countryCodes" :key="index" :value="countryCode">
                              {{ countryCode }}
                          </option>
                      </select>
                  </div>
                  <div class="col-span-2">
                      <input
                          v-model="contactNumber"
                          class="form-input rounded-l-lg w-full border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent rounded-r-lg"
                          placeholder="123 4567 890"
                          type="text"
                      />
                  </div>
                  <InputError class="col-span-3" :message="form.errors.mobile_number"/>
              </div>
          </div>
        <!-- Passport/NIC Field -->
        <div>
          <InputLabel for="pp_or_nic_no" value="PP or NIC No"/>
          <TextInput
              v-model="form.pp_or_nic_no"
              id="pp_or_nic_no"
              type="text"
              class="w-full"
              placeholder="PP or NIC No"
          />
          <InputError :message="form.errors.pp_or_nic_no"/>
        </div>

        <!-- Residency No Field -->
        <div>
          <InputLabel for="residency_no" value="Residency No"/>
          <TextInput
              v-model="form.residency_no"
              id="residency_no"
              type="text"
              class="w-full"
              placeholder="Residency No"
          />
          <InputError :message="form.errors.residency_no"/>
        </div>

        <!-- Address Field -->
        <div>
          <InputLabel for="address" value="Address"/>
          <textarea
              v-model="form.address"
              id="address"
              class="w-full border-gray-300 rounded-md"
              placeholder="Type address here..."
              rows="3"
          ></textarea>
          <InputError :message="form.errors.address"/>
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
        Create Shipper
      </PrimaryButton>
    </template>
  </DialogModal>
</template>


<style>
.custom-width {
    width: 530px;
}
</style>
