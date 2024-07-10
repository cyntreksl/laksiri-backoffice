<script setup>
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import {push} from "notivue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    hblPackageId: {
        type: Number,
        required: true,
    }
});

const emit = defineEmits(['close']);

const types = ref([
    'Crashed', 'Broken', 'Opened'
])

const form = useForm({
    hbl_package_id: null,
    issue: '',
    rtf: '',
    is_damaged: false,
    type: null,
});

const handleCreateUnloadingIssue = () => {
    form.hbl_package_id = props.hblPackageId;
    form.post(route('arrival.unloading-points.create.unloading-issue'), {
        onSuccess: () => {
            push.success('Unloading Issue Created!');
            emit('close');
            router.visit(route("arrival.unloading-points.index", {
                'container': route().params.container,
            }));
        },
        onError: () => {
            push.error('Something went to wrong!');
        }
    })
}
</script>

<template>
    <DialogModal :closeable="true" :maxWidth="'2xl'" :show="show" @close="$emit('close')">
        <template #title>
            <div class="flex justify-between items-center">
                <div>Create Unloading Issue</div>
                <button
                    class="text-gray-500 jus text-right hover:text-red-500 focus:outline-none"
                    @click="$emit('close')"
                >
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

        </template>
        <template #content>
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <InputLabel value="Issue"/>
                    <TextInput v-model="form.issue" class="w-full" placeholder="Type Issue"/>
                    <InputError :message="form.errors.issue"/>
                </div>

                <div>
                    <InputLabel value="Issue Type"/>
                    <select
                        v-model="form.type"
                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                    >
                        <option :value="null" disabled>Select One</option>
                        <option
                            v-for="type in types"
                            :key="type"
                            :value="type"
                        >
                            {{ type }}
                        </option>
                    </select>
                    <InputError :message="form.errors.type"/>
                </div>

                <div>
                    <InputLabel value="RTF"/>
                    <Checkbox v-model="form.rtf"/>
                    <InputError :message="form.errors.rtf"/>
                </div>

                <div>
                    <InputLabel value="Is Damage"/>
                    <Checkbox v-model="form.is_damaged"/>
                    <InputError :message="form.errors.is_damaged"/>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex space-x-2">
                <SecondaryButton @click="$emit('close')">
                    Cancel
                </SecondaryButton>
                <PrimaryButton @click.prevent="handleCreateUnloadingIssue">
                    Create Issue
                </PrimaryButton>
            </div>
        </template>
    </DialogModal>
</template>
