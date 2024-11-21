<script setup>
import {useForm} from "@inertiajs/vue3";
import notification from "@/magics/notification.js";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {push} from "notivue";

const props = defineProps({
    user: {
        type: Object,
        default: () => {
        }
    },
    branches: {
        type: Object,
        default: () => {
        }
    },
})

const form = useForm({
    primary_branch_id: props.user.primary_branch_id,
    secondary_branches: props.user?.branches.map(b => b.id),
});

const updateUserBranch = () => {
    form.put(route("users.branch.update", props.user.id), {
        onSuccess: () => {
            push.success('User Branches Updated Successfully!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <div class="card px-4 py-4 sm:px-5">
        <div>
            <h2
                class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
            >
                Change Branches
            </h2>
        </div>
        <form @submit.prevent="updateUserBranch" class="grid grid-cols-2 gap-5 mt-3">
            <div>
                <label class="block z-50">
                    <InputLabel value="Select Primary Branch"/>
                    <select
                        x-init="$el._tom = new Tom($el)"
                        class="w-full"
                        placeholder="Select a primary branch..."
                        autocomplete="off"
                        v-model="form.primary_branch_id"
                    >
                        <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </label>
                <InputError :message="form.errors.primary_branch_id"/>
            </div>

            <div>
                <label class="block z-50">
                    <InputLabel value="Select Secondary Branch"/>
                    <select
                        v-model="form.secondary_branches"
                        x-init="$el._tom = new Tom($el,{
            plugins: ['remove_button'],
            create: true,
          })"
                        multiple
                        class="w-full"
                        placeholder="Select a secondary branch..."
                        autocomplete="off"
                    >
                        <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                    </select>
                </label>
                <InputError :message="form.errors.secondary_branches"/>
            </div>

            <div class="flex col-span-2 justify-end">
                <PrimaryButton
                    type="submit"
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Change Branches
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
