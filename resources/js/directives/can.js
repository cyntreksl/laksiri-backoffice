import { useAbility } from '../composables/useAbility';

// Helper to resolve array/string input
function toArray(val) {
  if (Array.isArray(val)) return val;
  if (val == null) return [];
  return [val];
}

function apply(el, binding) {
  const { can, canAny } = useAbility();
  const value = binding?.value;

  // Support modifiers: v-can.any="['a','b']" vs default ALL
  const isAny = binding?.modifiers?.any === true;

  const required = toArray(value);
  const allowed = isAny ? canAny(required) : can(required);

  // Hide element if is not allowed
  el.style.display = allowed ? '' : 'none';
}

export default {
  mounted(el, binding) {
    apply(el, binding);
  },
  updated(el, binding) {
    apply(el, binding);
  },
};
