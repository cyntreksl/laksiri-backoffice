<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, reactive, ref} from "vue";
import {Grid} from "gridjs";
import Popper from "vue3-popper";
import DialogModal from "@/Components/DialogModal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {router, useForm} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    users: {
        type: Object,
        default: () => {
        }
    },
    roles: {
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

const wrapperRef = ref(null);
let grid = null;
const confirmingUserCreation = ref(false);
console.log(props.users)
const data = reactive({
    UserData: props.users,
    columnVisibility: {
        username: true,
        primary_branch_id: true,
        created_at: true,
        status: true,
        last_login_at: true,
        last_logout_at: true,
    }
});

const initializeGrid = () => {
    grid = new Grid({
        search: true,
        pagination: {
            limit: 20
        },
        sort: true,
        columns: createColumns(),
        data: createData(),
    });

    grid.render(wrapperRef.value);
};

const createColumns = () => [
    {name: 'Username', hidden: !data.columnVisibility.username},
    {name: 'Primary Branch', hidden: !data.columnVisibility.primary_branch_id},
    {name: 'Created At', hidden: !data.columnVisibility.created_at},
    {name: 'Status', hidden: !data.columnVisibility.status},
    {name: 'Last Login', hidden: !data.columnVisibility.last_login_at},
    {name: 'Last Logout', hidden: !data.columnVisibility.last_logout_at},
];

const createData = () =>
    data.UserData.map(pickup => [
        pickup.username,
        pickup.primary_branch_id,
        pickup.created_at,
        pickup.status,
        pickup.last_login_at,
        pickup.last_logout_at,
    ]);

const updateGridConfig = () => {
    grid.updateConfig({
        columns: createColumns(),
    });
};

const toggleColumnVisibility = columnName => {
    data.columnVisibility[columnName] = !data.columnVisibility[columnName];
    updateGridConfig();
    grid.forceRender();
};

const showColumn = ref(false);

onMounted(() => {
    initializeGrid();
})

const closeModal = () => {
    confirmingUserCreation.value = false;
};

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    primary_branch_id: '',
    secondary_branches: [],
    role_id: '',
});

const createUser = () => {
    form.post(route("users.store"), {
        onSuccess: () => {
            router.visit(route("users.index"));
            form.reset();
        },
        onError: () => console.log("error"),
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <AppLayout title="User Management">
        <template #header>User Management</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        User Management
                    </h2>

                    <div class="flex">
                        <Popper>
                            <button
                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <template #content>
                                <div class="max-w-[16rem]">
                                    <div
                                        class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                                        <div
                                            class="rounded-md border border-slate-150 bg-white p-4 dark:border-navy-600 dark:bg-navy-700">
                                            <h3 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                                Select Columns
                                            </h3>
                                            <p class="mt-1 text-xs+">Choose which columns you want to see </p>
                                            <div class="mt-4 flex flex-col space-y-4 text-slate-600 dark:text-navy-100">
                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.username"
                                                        @change="toggleColumnVisibility('username', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Reference</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.primary_branch_id"
                                                        @change="toggleColumnVisibility('primary_branch_id', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Name</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Popper>

                        <button
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end mx-5">
                    <button
                        @click="confirmingUserCreation = !confirmingUserCreation"
                        class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                    >
                        New User
                    </button>
                </div>

                <div class=" mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <div ref="wrapperRef"></div>
                    </div>
                </div>
            </div>
        </div>

        <DialogModal :show="confirmingUserCreation" @close="closeModal" :maxWidth="'5xl'">
            <template #title>
                Create New User
            </template>

            <template #content>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <span>Username</span>
                        <label class="relative flex">
                            <input
                                v-model="form.username"
                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Username"
                                type="text"
                            />
                            <div
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4.5 transition-colors duration-200"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                    />
                                </svg>
                            </div>
                        </label>
                        <span
                            v-if="form.errors.username"
                            class="text-tiny+ text-error"
                        >{{ form.errors.username }}</span>
                    </div>

                    <div>
                        <span>Email</span>
                        <label class="relative flex">
                            <input
                                v-model="form.email"
                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Email Address"
                                type="email"
                            />
                            <div
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4.5 transition-colors duration-200"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                    />
                                </svg>
                            </div>
                        </label>
                        <span
                            v-if="form.errors.email"
                            class="text-tiny+ text-error"
                        >{{ form.errors.email }}</span>
                    </div>

                    <div>
                        <span>Password</span>
                        <label class="relative flex">
                            <input
                                v-model="form.password"
                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Password"
                                type="password"
                            />
                            <div
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4.5 transition-colors duration-200"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                    />
                                </svg>
                            </div>
                        </label>
                        <span
                            v-if="form.errors.password"
                            class="text-tiny+ text-error"
                        >{{ form.errors.password }}</span>
                    </div>

                    <div>
                        <span>Re-enter Password</span>
                        <label class="relative flex">
                            <input
                                v-model="form.password_confirmation"
                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Confirm Password"
                                type="password"
                            />
                            <div
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4.5 transition-colors duration-200"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"
                                    />
                                </svg>
                            </div>
                        </label>
                        <span
                            v-if="form.errors.password_confirmation"
                            class="text-tiny+ text-error"
                        >{{ form.errors.password_confirmation }}</span>
                    </div>

                    <div>
                        <label class="block z-50">
                            <span>Select Primary Branch</span>
                            <select
                                x-init="$el._tom = new Tom($el)"
                                class="mt-1.5 w-full"
                                placeholder="Select a primary branch..."
                                autocomplete="off"
                                v-model="form.primary_branch_id"
                            >
                                <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </label>
                        <span
                            v-if="form.errors.primary_branch_id"
                            class="text-tiny+ text-error"
                        >{{ form.errors.primary_branch_id }}</span>
                    </div>

                    <div>
                        <label class="block z-50">
                            <span>Select Secondary Branch</span>
                            <select
                                v-model="form.secondary_branches"
                                x-init="$el._tom = new Tom($el)"
                                multiple
                                class="mt-1.5 w-full"
                                placeholder="Select a secondary branch..."
                                autocomplete="off"
                            >
                                <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </label>
                        <span
                            v-if="form.errors.secondary_branches"
                            class="text-tiny+ text-error"
                        >{{ form.errors.secondary_branches }}</span>
                    </div>

                    <div class="col-span-2">
                        <div class="space-x-5">
                            <label
                                v-for="role in roles"
                                class="inline-flex items-center space-x-2"
                            >
                                <input
                                    v-model="form.role_id"
                                    class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-slate-100 checked:!border-success checked:!bg-success hover:!border-success focus:!border-success dark:border-navy-500 dark:bg-navy-900"
                                    name="role"
                                    :value="role.id"
                                    type="radio"
                                />
                                <p>{{ role.name }}</p>
                            </label>
                        </div>
                        <span
                            v-if="form.errors.role_id"
                            class="text-tiny+ text-error"
                        >{{ form.errors.role_id }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <ul class="space-y-4">
                        <li>
                            EMPTY
                        </li>
                        <li>
                            User can`t login to this branch. But can view details from other branches
                        </li>
                        <li>
                            VIEWER
                        </li>
                        <li>
                            User can view, create, edit. Based on permissions
                        </li>
                        <li>
                            ADMIN
                        </li>
                        <li>
                            All privileges. User can view, create, edit and delete
                        </li>
                    </ul>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="createUser"
                >
                    Create User
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
