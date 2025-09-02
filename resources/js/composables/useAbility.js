import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Normalizes permission names so "User Create" and "users.create" both work.
 * Returns a set of variants to match against the user's permissions.
 */
function variantsOf(name) {
  if (!name) return [];
  const raw = String(name);
  const trimmed = raw.trim();
  const lower = trimmed.toLowerCase();

  // Common separators and shapes
  const dot = lower.replace(/\s+/g, '.').replace(/_/g, '.').replace(/-+/g, '.');
  const dash = lower.replace(/\s+/g, '-').replace(/\.+/g, '-').replace(/_+/g, '-');
  const underscore = lower.replace(/\s+/g, '_').replace(/\.+/g, '_').replace(/-+/g, '_');
  const spaced = lower.replace(/[._-]+/g, ' ');

  // If a format contains a resource/action like "users create", add "users.create" and "users-create"
  // Already covered by the above, but kept for clarity.

  return Array.from(new Set([trimmed, lower, dot, dash, underscore, spaced]));
}

function toSet(list) {
  const s = new Set();
  (list || []).forEach((n) => {
    variantsOf(n).forEach((v) => s.add(v));
  });
  return s;
}

export function useAbility() {
  const page = usePage();

  // Attempt common locations for permissions shared via Inertia
  const permissions = computed(() => {
    const props = page.props?.value || {};
    const auth = props.auth || {};
    const user = props.user || auth.user || {};
    // Try common keys in order
    const list =
      user.permissions ||
      auth.permissions ||
      auth.can ||
      props.permissions ||
      [];
    return toSet(list);
  });

  const roles = computed(() => {
    const props = page.props?.value || {};
    const auth = props.auth || {};
    const user = props.user || auth.user || {};
    const list =
      user.roles ||
      auth.roles ||
      [];
    return toSet(list);
  });

  function hasAll(names) {
    const perms = permissions.value;
    const arr = Array.isArray(names) ? names : [names];
    return arr.every((n) => {
      const vs = variantsOf(n);
      return vs.some((v) => perms.has(v));
    });
  }

  function hasAny(names) {
    const perms = permissions.value;
    const arr = Array.isArray(names) ? names : [names];
    return arr.some((n) => {
      const vs = variantsOf(n);
      return vs.some((v) => perms.has(v));
    });
  }

  return {
    permissions,
    roles,
    can: hasAll,
    canAny: hasAny,
  };
}
