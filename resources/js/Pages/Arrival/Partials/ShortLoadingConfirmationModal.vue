<script setup>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["close", "shortLoading"]);
</script>

<template>
  <ConfirmationModal :show="show">
    <template #title> Short Load Confirmation </template>

    <template #content>Are you sure you want to Mark as Short Loaded? </template>

    <template #footer>
      <div class="space-x-2">
        <SecondaryButton @click="$emit('close')"> Nevermind </SecondaryButton>

        <PrimaryButton @click="$emit('shortLoading')">
          Short Load
        </PrimaryButton>
      </div>
    </template>
  </ConfirmationModal>
</template>
