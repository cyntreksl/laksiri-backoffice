<script setup>
import {useForm, usePage} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import Card from "primevue/card";
import Button from "primevue/button";
import MultiSelect from "primevue/multiselect";
import Select from "primevue/select";
import {computed} from "vue";

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

// Filter branches based on user's assigned branches
const filteredBranches = computed(() => {
    const userRole = usePage().props?.auth.user.role;
    
    // Only Super admin can see all branches
    if (userRole === 'super admin') {
        return props.branches;
    }
    
    // Get the user's assigned branch IDs from auth.user.branches
    const userBranches = usePage().props?.auth?.user?.branches || [];
    
    // If user has no assigned branches, return empty array
    if (userBranches.length === 0) {
        return [];
    }
    
    // Extract branch IDs from user's branches
    const userBranchIds = userBranches.map(branch => branch.id);
    
    // Filter branches to only show the ones the user is assigned to
    return props.branches.filter(branch => userBranchIds.includes(branch.id));
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
    <form
        @submit.prevent="updateUserBranch"
    >
        <Card>
            <template #title>Change Branches</template>
            <template #content>
                <div class="grid grid-cols-2 gap-5 mt-3">
                    <div>
                        <InputLabel value="Select Primary Branch"/>
                        <Select v-model="form.primary_branch_id" :options="filteredBranches" class="w-full" option-label="name" option-value="id" placeholder="Select a primary branch" />
                        <InputError :message="form.errors.primary_branch_id"/>
                    </div>

                    <div>
                        <InputLabel value="Select Secondary Branch"/>
                        <MultiSelect v-model="form.secondary_branches" :maxSelectedLabels="3" :options="filteredBranches" class="w-full" display="chip" filter option-label="name" option-value="id" placeholder="Select secondary branches"/>
                        <InputError :message="form.errors.secondary_branches"/>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="inline-block float-right mt-3">
                    <Button :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full"
                            label="Change Branches" type="submit"/>
                </div>
            </template>
        </Card>
    </form>
</template>
