<script setup>
import DestinationAppLayout from "@/Layouts/DestinationAppLayout.vue";
import {router, useForm} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import VerifyConfirmationModal from "@/Pages/CallCenter/Verification/Partials/VerifyConfirmationModal.vue";
import {ref} from "vue";
import {push} from "notivue";

const props = defineProps({
    verificationDocuments: {
        type: Array,
        default: () => []
    },
    customerQueue: {
        type: Object,
        default: () => {}
    }
})

const form = useForm({
    customer_queue: props.customerQueue,
    is_checked: {},
    note: ''
});

const updateChecked = (doc, isChecked) => {
    form.is_checked = { ...form.is_checked, [doc]: isChecked };
};

const showConfirmVerifyModal = ref(false);

const closeModal = () => {
    showConfirmVerifyModal.value = false;
};

const handleVerifyDocuments = () => {
    if (Object.keys(form.is_checked).length === 0) {
        push.error('Please check the documents first!');
        closeModal();
        return 0;
    }

    form.post(route("call-center.verification.store"), {
        onSuccess: () => {
            closeModal();
            router.visit(route("call-center.verification.queue.list"));
            form.reset();
            push.success('Verified Successfully!');
        },
        onError: () => {
            push.error('Something went to wrong!');
        },
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <DestinationAppLayout title="Documents Verification">
        <template #header>Documents Verification</template>

        <!-- Breadcrumb -->
        <Breadcrumb />

        <!-- Create Pickup Form -->
        <div class="grid grid-cols-1 mt-4 gap-4">
            <div class="sm:col-span-3 space-y-5">
                <div class="card px-4 py-4 sm:px-5">
                    <div class="grid grid-cols-2">
                        <h2
                            class="text-lg font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100"
                        >
                            Document Verification
                        </h2>
                    </div>
                    <div class="grid grid-cols-2 gap-5 mt-3">
                        <div class="space-y-4">
                            <InputLabel v-for="(doc, index) in verificationDocuments" :key="index" class="cursor-pointer">
                                <input
                                    :checked="form.is_checked[doc] || false"
                                    :value="doc"
                                    class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent mr-3"
                                    type="checkbox"
                                    @change="(event) => updateChecked(doc, event.target.checked)"
                                >
                                {{ doc }}
                            </InputLabel>
                        </div>

                        <div class="col-span-2">
                            <InputLabel value="Note" />
                            <label class="block">
                                  <textarea
                                      v-model="form.note"
                                      class="form-textarea w-full resize-none rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                      placeholder="Type note here..."
                                      rows="4"
                                  ></textarea>
                            </label>
                            <InputError :message="form.errors.note" />
                        </div>
                    </div>
                    <div class="text-right mt-3">
                        <PrimaryButton type="button" @click="showConfirmVerifyModal = !showConfirmVerifyModal">Verify</PrimaryButton>
                    </div>
                </div>
            </div>
        </div>

        <VerifyConfirmationModal :show="showConfirmVerifyModal" @close="closeModal" @verify-customer="handleVerifyDocuments"/>
    </DestinationAppLayout>
</template>
