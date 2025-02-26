<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {Link, router} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";


import {push} from "notivue";
import {ref} from "vue";
import DeleteAgentConfirmationModal from "@/Pages/Agent/Partials/DeleteAgentConfirmationModal.vue";

defineProps({
    agents: {
        type: Object,
        default: () => {}
    }
})

const showDeleteAgentConfirmationModal = ref(false);
const AgentId = ref(null);

const confirmDeleteAgent = (id) => {
    AgentId.value = id;
    showDeleteAgentConfirmationModal.value = true;
};
const closeModal = () => {
    showDeleteAgentConfirmationModal.value = false;
    AgentId.value = null;
};

const handleDeleteAgent = () => {
    router.delete(route("agents.destroy", AgentId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            push.success("Officer Deleted Successfully!");
            router.visit(route("agents.index"));
        },
    });
};
const searchQuery = ref('');

const handleSearch = () => {
    router.get(route('agents.index'), { search: searchQuery.value }, { preserveState: true, replace: true });
};
</script>

<template>
    <AppLayout title="Agents">
        <template #header>Agents</template>

        <Breadcrumb/>

        <div class="flex items-center justify-between p-2 my-5">
            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                Agents
            </h2>

            <div class="flex items-center space-x-2">
                <input
                    v-model="searchQuery"
                    @input="handleSearch"
                    type="text"
                    placeholder="Search..."
                    class="form-input rounded-lg border border-slate-300 bg-slate-50 py-2 px-3 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring focus:ring-primary/50 dark:border-navy-500 dark:bg-navy-700 dark:placeholder:text-navy-300"
                />
                <Link :href="route('agents.create')">
                    <PrimaryButton>
                        Create New Agent
                    </PrimaryButton>
                </Link>
            </div>
        </div>

        <div v-if="agents.length > 0" class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                <tr>
                    <th
                        class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Name
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Type
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Branch Code
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Currency Name
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Currency Symbol
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Cargo Modes
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Delivery Types
                    </th>
                    <th
                        class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Package Types
                    </th>
                    <th
                        class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5"
                    >
                        Action
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="agent in agents" :key="agent?.id"
                    class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 last:border-0 bg-white"
                >
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{agent.name}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{agent.type}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{agent.branch_code}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{agent.currency_name}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        {{agent.currency_symbol}}
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        <span v-for="cmode in JSON.parse(agent.cargo_modes)" :key="cmode" class="badge bg-info/10 text-info dark:bg-info/15 ml-2">
                            {{cmode}}
                        </span>
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        <span v-for="dtype in JSON.parse(agent.delivery_types)" :key="dtype" class="badge bg-success/10 text-success dark:bg-success/15 ml-2">
                            {{dtype}}
                        </span>
                    </td>
                    <td
                        class="whitespace-nowrap px-4 py-3 sm:px-5"
                    >
                        <span v-for="ptype in JSON.parse(agent.package_types)" :key="ptype" class="badge bg-warning/10 text-warning dark:bg-warning/15 ml-2">
                            {{ptype}}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <div class="flex space-x-2">
                            <Link
                                :href="route('agents.edit', agent.id)"
                                class="btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25"
                            >
                                <svg
                                    class="size-4.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </Link>
                            <button
                                class="btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25"
                                @click="confirmDeleteAgent(agent.id)"
                            >
                                <svg
                                    class="size-4.5"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="text-center p-4">
            <p>No agents found.</p>
        </div>
        <DeleteAgentConfirmationModal
            :show="showDeleteAgentConfirmationModal"
            @close="closeModal"
            @deleteAgent="handleDeleteAgent"
        />
    </AppLayout>
</template>
