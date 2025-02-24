<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";



const form = useForm({
    company_name: "",
    website: "",
    contact_number_1: "",
    contact_number_2: "",
    email: "",
    address: "",
    logo: null,
    invoice_header: "",
    invoice_footer: "",
});

const submitForm = () => {
    form.post("/courier-agents", {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout title="Create Courier Agent">
        <Breadcrumb>
            <div class="flex items-center justify-between p-2 my-5">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Create Courier Agent
                </h2>
            </div>
            <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="company_name">
                            Company Name
                        </label>
                        <input
                            v-model="form.company_name"
                            type="text"
                            id="company_name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="website">
                            Website
                        </label>
                        <input
                            v-model="form.website"
                            type="url"
                            id="website"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_number_1">
                            Contact Number 1
                        </label>
                        <input
                            v-model="form.contact_number_1"
                            type="tel"
                            id="contact_number_1"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_number_2">
                            Contact Number 2
                        </label>
                        <input
                            v-model="form.contact_number_2"
                            type="tel"
                            id="contact_number_2"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            id="email"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                            Address
                        </label>
                        <textarea
                            v-model="form.address"
                            id="address"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required
                        ></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="logo">
                            Logo
                        </label>
                        <input
                            type="file"
                            @input="form.logo = $event.target.files[0]"
                            id="logo"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="invoice_header">
                            Invoice Header
                        </label>
                        <textarea
                            v-model="form.invoice_header"
                            id="invoice_header"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        ></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="invoice_footer">
                            Invoice Footer
                        </label>
                        <textarea
                            v-model="form.invoice_footer"
                            id="invoice_footer"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        ></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button
                            type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            Create Courier Agent
                        </button>
                    </div>
                </form>
            </div>
        </Breadcrumb>
    </AppLayout>
</template>

<style scoped>
/* Add any scoped styles here */
</style>
