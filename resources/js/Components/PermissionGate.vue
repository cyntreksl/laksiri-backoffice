<script setup>
import { computed } from 'vue';
import { useAbility } from '../composables/useAbility';

const props = defineProps({
  // Render if the user has ALL of these permissions
  allOf: {
    type: [Array, String],
    default: () => [],
  },
  // Render if the user has ANY of these permissions
  anyOf: {
    type: [Array, String],
    default: () => [],
  },
});

const { can, canAny } = useAbility();

const allowed = computed(() => {
  const allOk = Array.isArray(props.allOf) ? (props.allOf.length === 0 ? true : can(props.allOf)) : (props.allOf ? can([props.allOf]) : true);
  const anyOk = Array.isArray(props.anyOf) ? (props.anyOf.length === 0 ? true : canAny(props.anyOf)) : (props.anyOf ? canAny([props.anyOf]) : true);
  // Both conditions must hold when both provided
  return allOk && anyOk;
});
</script>

<template>
  <slot v-if="allowed" />
</template>
