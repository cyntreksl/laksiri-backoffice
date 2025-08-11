<!-- resources/js/Pages/Whatsapp/Partials/EditContact.vue -->
<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { push } from 'notivue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    contact: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:visible', 'contact-updated']);

const form = useForm({
    id: null,
    name: '',
    phone: '',
    profile_pic: ''
});

// Watch for changes in the contact prop to populate the form
watch(() => props.contact, (newContact) => {
    if (newContact) {
        form.id = newContact.id;
        form.name = newContact.name || '';
        form.phone = newContact.phone || '';
        form.profile_pic = newContact.profile_pic || '';
    }
}, { immediate: true });

const updateContact = () => {
    form.put(route('whatsapp.contacts.update', form.id), {
        onSuccess: () => {
            push.success('Contact updated successfully!');
            emit('contact-updated', form);
            emit('update:visible', false);
            form.reset();
        },
        onError: () => {
            push.error('Failed to update contact');
        },
        preserveScroll: true,
        preserveState: true,
    });
};

const closeDialog = () => {
    emit('update:visible', false);
    form.reset();
};
</script>

<template>
    <Dialog
        :visible="visible"
        :style="{ width: '25rem' }"
        header="Edit Contact"
        modal
        @update:visible="closeDialog"
    >
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <InputText
                    id="name"
                    v-model="form.name"
                    class="w-full"
                    placeholder="Contact name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <InputText
                    id="phone"
                    v-model="form.phone"
                    class="w-full"
                    placeholder="Phone number"
                />
                <InputError :message="form.errors.phone" />
            </div>

            <div>
                <label for="profile_pic" class="block text-sm font-medium text-gray-700 mb-1">Profile Picture URL</label>
                <InputText
                    id="profile_pic"
                    v-model="form.profile_pic"
                    class="w-full"
                    placeholder="Profile picture URL"
                />
                <InputError :message="form.errors.profile_pic" />
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <Button
                label="Cancel"
                severity="secondary"
                @click="closeDialog"
            />
            <Button
                :disabled="form.processing"
                label="Save Changes"
                @click="updateContact"
            />
        </div>
    </Dialog>
</template>

<style scoped>
</style>
