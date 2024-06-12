<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import { router, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import notification from "@/magics/notification.js";

const props = defineProps({
  user: {
    type: Object,
    default: () => {},
  },
  roles: {
    type: Object,
    default: () => {},
  },
  branches: {
    type: Object,
    default: () => {},
  },
});

// console.log(props.user);

const form = useForm({
  name: props.user.name,
  username: props.user.username,
  email: props.user.email,
  role_id: props.user.roles[0]?.id,
});

const handleUpdateUser = () => {
  form.put(route("users.update", props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      notification({
        text: "Basic Details Updated Successfully!",
        variant: "success",
      });
      router.visit(route("users.edit", props.user.id));
    },
    onError: () => {
      form.reset();
    },
  });
};
</script>

