<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

interface Props {
    visible: boolean;
}

interface Emits {
    'update:visible': [value: boolean];
    'contact-saved': [contact: any];
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const dialogVisible = computed({
    get: () => props.visible,
    set: (value: boolean) => emit('update:visible', value)
});

// Form handling with Inertia.js
const form = useForm({
    name: '',
    phone: ''
});

// Local validation errors
const errors = ref({
    name: '',
    phone: ''
});

// Reset form when dialog opens/closes
watch(dialogVisible, (newValue) => {
    if (newValue) {
        form.reset();
        errors.value = { name: '', phone: '' };
    }
});

// Validate form fields
const validateForm = (): boolean => {
    errors.value = { name: '', phone: '' };
    let isValid = true;

    // Validate name
    if (!form.name.trim()) {
        errors.value.name = 'Name is required';
        isValid = false;
    } else if (form.name.trim().length < 2) {
        errors.value.name = 'Name must be at least 2 characters';
        isValid = false;
    }

    // Validate phone
    if (!form.phone.trim()) {
        errors.value.phone = 'Phone number is required';
        isValid = false;
    } else {
        // Basic phone validation
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        const cleanPhone = form.phone.replace(/[\s\-\(\)]/g, '');
        if (!phoneRegex.test(cleanPhone)) {
            errors.value.phone = 'Please enter a valid phone number';
            isValid = false;
        }
    }

    return isValid;
};

// Format phone number as user types
const formatPhoneNumber = (value: string): string => {
    const cleaned = value.replace(/[^\d+]/g, '');

    if (cleaned.length <= 10 && !cleaned.startsWith('+')) {
        return cleaned.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    }

    return cleaned;
};

// Handle phone input formatting
const handlePhoneInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const formatted = formatPhoneNumber(target.value);
    form.phone = formatted;
};

// Save contact
const saveContact = () => {
    if (!validateForm()) {
        return;
    }

    // Clean phone number for storage
    const cleanPhone = form.phone.replace(/[\s\-\(\)]/g, '');

    form.transform((data) => ({
        ...data,
        phone: cleanPhone
    })).post(route('whatsapp.contacts.store'), {
        onSuccess: (page) => {
            dialogVisible.value = false;
            emit('contact-saved', page.props.flash?.contact);
        },
        onError: (serverErrors) => {
            // Handle server-side validation errors
            Object.keys(serverErrors).forEach(key => {
                if (key in errors.value) {
                    errors.value[key as keyof typeof errors.value] = serverErrors[key];
                }
            });
        }
    });
};

// Cancel dialog
const cancelDialog = () => {
    dialogVisible.value = false;
};
</script>

<template>
    <Dialog v-model:visible="dialogVisible" modal header="Add Contact" :style="{ width: '25rem' }">
        <span class="text-surface-500 dark:text-surface-400 block mb-8">Add a new contact.</span>

        <div class="flex items-center gap-4 mb-4">
            <label for="name" class="font-semibold w-24">Name</label>
            <div class="flex-auto">
                <InputText
                    id="name"
                    v-model="form.name"
                    class="w-full"
                    :class="{ 'p-invalid': errors.name || form.errors.name }"
                    autocomplete="off"
                    placeholder="Enter contact name"
                />
                <small v-if="errors.name || form.errors.name" class="p-error block mt-1">
                    {{ errors.name || form.errors.name }}
                </small>
            </div>
        </div>

        <div class="flex items-center gap-4 mb-8">
            <label for="number" class="font-semibold w-24">Number</label>
            <div class="flex-auto">
                <InputText
                    id="number"
                    v-model="form.phone"
                    class="w-full"
                    :class="{ 'p-invalid': errors.phone || form.errors.phone }"
                    autocomplete="off"
                    placeholder="Enter phone number"
                    @input="handlePhoneInput"
                />
                <small v-if="errors.phone || form.errors.phone" class="p-error block mt-1">
                    {{ errors.phone || form.errors.phone }}
                </small>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="cancelDialog"
                :disabled="form.processing"
            />
            <Button
                type="button"
                label="Save"
                @click="saveContact"
                :loading="form.processing"
                :disabled="form.processing"
            />
        </div>
    </Dialog>
</template>
