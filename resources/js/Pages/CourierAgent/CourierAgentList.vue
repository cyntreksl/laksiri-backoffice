<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link, router} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {push} from "notivue";
import {ref, computed, watch} from "vue";
import DeleteCourierAgentConfirmationModal from "@/Pages/CourierAgent/Partials/DeleteCourierAgentConfirmationModal.vue";

const props = defineProps({
    courierAgents: {
        type: Object,
        default: () => {}
    }
});

// Delete functionality
const showDeleteCourierAgentConfirmationModal = ref(false);
const CourierAgentId = ref(null);

const confirmDeleteAgent = (id) => {
    CourierAgentId.value = id;
    showDeleteCourierAgentConfirmationModal.value = true;
};

const closeModal = () => {
    showDeleteCourierAgentConfirmationModal.value = false;
    CourierAgentId.value = null;
};

const handleDeleteCourierAgent = () => {
    router.delete(route("courier-agents.destroy", CourierAgentId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Officer Deleted Successfully!");
            router.visit(route("courier-agents.index"));
        },
    });
};

// Search functionality
const searchQuery = ref('');
const filteredAgents = computed(() => {
    if (!searchQuery.value) return props.courierAgents;

    const query = searchQuery.value.toLowerCase();
    return props.courierAgents.filter(agent =>
        agent.company_name.toLowerCase().includes(query) ||
        (agent.website && agent.website.toLowerCase().includes(query)) ||
        (agent.contact_number_1 && agent.contact_number_1.toLowerCase().includes(query)) ||
        (agent.email && agent.email.toLowerCase().includes(query)) ||
        (agent.address && agent.address.toLowerCase().includes(query))
    );
});

// Sort functionality
const sortField = ref('company_name');
const sortDirection = ref('asc');

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
};

const sortedAgents = computed(() => {
    return [...filteredAgents.value].sort((a, b) => {
        const fieldA = a[sortField.value] ? a[sortField.value].toString().toLowerCase() : '';
        const fieldB = b[sortField.value] ? b[sortField.value].toString().toLowerCase() : '';

        if (sortDirection.value === 'asc') {
            return fieldA.localeCompare(fieldB);
        } else {
            return fieldB.localeCompare(fieldA);
        }
    });
});

// Reset search and sort
const resetFilters = () => {
    searchQuery.value = '';
    sortField.value = 'company_name';
    sortDirection.value = 'asc';
};
</script>

<template>
    <AppLayout title="Courier Agents">
        <template #header> Courier Agents</template>

        <Breadcrumb/>

        <div class="flex items-center justify-between p-2 my-5">
            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                Courier Agents
            </h2>

            <Link :href="route('courier-agents.create')">
                <PrimaryButton>
                    Create New Courier Agent
                </PrimaryButton>
            </Link>
        </div>

        <!-- Search and Filter Controls -->
        <div class="mb-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Search agents..."
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <button
                    v-if="searchQuery"
                    @click="searchQuery = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">{{ sortedAgents.length }} agents found</span>
                <button
                    @click="resetFilters"
                    class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                >
                    Reset Filters
                </button>
            </div>
        </div>

        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                <tr>
                    <th
                        @click="sortBy('company_name')"
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 cursor-pointer select-none"
                    >
                        <div class="flex items-center">
                            Company Name
                            <span v-if="sortField === 'company_name'" class="ml-1">
                                    <svg v-if="sortDirection === 'asc'" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </div>
                    </th>
                    <th
                        @click="sortBy('website')"
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 cursor-pointer select-none"
                    >
                        <div class="flex items-center">
                            Website
                            <span v-if="sortField === 'website'" class="ml-1">
                                    <svg v-if="sortDirection === 'asc'" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </div>
                    </th>
                    <th
                        @click="sortBy('contact_number_1')"
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 cursor-pointer select-none"
                    >
                        <div class="flex items-center">
                            Contact Number-1
                            <span v-if="sortField === 'contact_number_1'" class="ml-1">
                                    <svg v-if="sortDirection === 'asc'" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </div>
                    </th>
                    <th
                        @click="sortBy('email')"
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 cursor-pointer select-none"
                    >
                        <div class="flex items-center">
                            Email
                            <span v-if="sortField === 'email'" class="ml-1">
                                    <svg v-if="sortDirection === 'asc'" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </div>
                    </th>
                    <th
                        @click="sortBy('address')"
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 cursor-pointer select-none"
                    >
                        <div class="flex items-center">
                            Address
                            <span v-if="sortField === 'address'" class="ml-1">
                                    <svg v-if="sortDirection === 'asc'" xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                        </div>
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Action
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="sortedAgents.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        No courier agents found matching your search criteria.
                    </td>
                </tr>
                <tr
                    v-else
                    v-for="courierAgent in sortedAgents"
                    :key="courierAgent?.id"
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
                >
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{courierAgent.company_name}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{courierAgent.website}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{courierAgent.contact_number_1}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{courierAgent.email}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        {{courierAgent.address}}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <div class="flex space-x-2">
                            <Link
                                :href="route('courier-agents.edit', courierAgent.id)"
                                class="btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25"
                            >
                                <svg
                                    class="size-4.5"
                                    fill="none"
                                    viewBox="0 0 512 512"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M471 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152V424c0 48.6 39.4 88 88 88H360c48.6 0 88-39.4 88-88V312c0-13.3-10.7-24-24-24s-24 10.7-24 24V424c0 22.1-17.9 40-40 40H88c-22.1 0-40-17.9-40-40V152c0-22.1 17.9-40 40-40H200c13.3 0 24-10.7 24-24s-10.7-24-24-24H88z"
                                        fill="currentColor"
                                    ></path>
                                </svg>
                            </Link>
                            <button
                                class="btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                                @click="confirmDeleteAgent(courierAgent.id)"
                            >
                                <svg
                                    class="size-4.5"
                                    fill="none"
                                    viewBox="0 0 448 512"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z"
                                        fill="currentColor"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <DeleteCourierAgentConfirmationModal
            :show="showDeleteCourierAgentConfirmationModal"
            @close="closeModal"
            @deleteCourierAgent="handleDeleteCourierAgent"
        />
    </AppLayout>
</template>
