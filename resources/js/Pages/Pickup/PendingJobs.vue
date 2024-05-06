<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import {Grid, h} from "gridjs";
import {onMounted, reactive, ref} from "vue";
import Popper from "vue3-popper";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AssignDriverModal from "@/Pages/Pickup/Partials/AssignDriverModal.vue";
import DeleteUserConfirmationModal from "@/Pages/User/Partials/DeleteUserConfirmationModal.vue";

export default {
    components: {DeleteUserConfirmationModal, AssignDriverModal, Breadcrumb, AppLayout, Popper},
    props: {
        pickups: {},
        drivers: {},
    },
    setup(props) {
        const wrapperRef = ref(null);
        let grid = null;

        const data = reactive({
            pickupsData: props.pickups,
            columnVisibility: {
                id: false,
                reference: true,
                name: true,
                email: false,
                address: true,
                contact: true,
                cargoMode: true,
                notes: false,
                pickupDate: true,
                pickupTimeStart: false,
                pickupTimeEnd: false,
                actions: true,
            },
        });

        onMounted(() => {
            initializeGrid();
        });

        const initializeGrid = () => {
            grid = new Grid({
                search: true,
                pagination: {
                    enabled: true,
                    limit: 10,
                },
                sort: true,
                columns: createColumns(),
                data: createData(),
            });

            grid.render(wrapperRef.value);
        };

        const createColumns = () => [
            {name: 'ID', hidden: !data.columnVisibility.id},
            {name: 'Reference', hidden: !data.columnVisibility.reference},
            {name: 'Name', hidden: !data.columnVisibility.name},
            {name: 'Address', hidden: !data.columnVisibility.address},
            {name: 'Contact', hidden: !data.columnVisibility.contact},
            {name: 'Cargo Mode', hidden: !data.columnVisibility.cargoMode},
            {name: 'Pickup Date', hidden: !data.columnVisibility.pickupDate},
            {name: 'Start Pickup Time', hidden: !data.columnVisibility.pickupTimeStart},
            {name: 'End Pickup Time', hidden: !data.columnVisibility.pickupTimeEnd},
            {name: 'Email', hidden: !data.columnVisibility.email},
            {name: 'Note', hidden: !data.columnVisibility.notes},
            {
                name: 'Actions',
                sort: false,
                hidden: !data.columnVisibility.actions,
                formatter: (_, row) => {
                    return h('div', {}, [
                        h('button', {
                            className: 'btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25',
                            onClick: () => confirmAssignDriver(row.cells[0].data)
                        }, [
                            h('svg', {
                                xmlns: 'http://www.w3.org/2000/svg',
                                viewBox: '0 0 24 24',
                                width: 24,
                                height: 24,
                                class: 'size-4.5 icon icon-tabler icons-tabler-outline icon-tabler-truck',
                                fill: 'none',
                                stroke: "currentColor",
                                strokeWidth: 2,
                                strokeLinecap: "round",
                                strokeLinejoin: "round",
                            }, [
                                h('path', {
                                    stroke: "none",
                                    d: 'M0 0h24v24H0z',
                                    fill: 'none',
                                }),
                                h('path', {
                                    d: 'M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0',
                                }),
                                h('path', {
                                    d: 'M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0',
                                }),
                                h('path', {
                                    d: 'M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5',
                                }),
                            ])
                        ]),
                    ]);
                },
            },
        ];

        const createData = () =>
            data.pickupsData.map(pickup => [
                pickup.id,
                pickup.reference,
                pickup.name,
                pickup.address,
                pickup.contact_number,
                pickup.cargo_type,
                pickup.pickup_date,
                pickup.pickup_time_start,
                pickup.pickup_time_end,
                pickup.email,
                pickup.notes,
            ]);

        const toggleColumnVisibility = columnName => {
            data.columnVisibility[columnName] = !data.columnVisibility[columnName];
            updateGridConfig();
            grid.forceRender();
        };

        const updateGridConfig = () => {
            grid.updateConfig({
                columns: createColumns(),
            });
        };

        const showColumn = ref(false);

        const showConfirmAssignDriverModal = ref(false);
        const jobId = ref(null);

        const confirmAssignDriver = (id) => {
            jobId.value = id;
            showConfirmAssignDriverModal.value = true;
        };

        const closeModal = () => {
            showConfirmAssignDriverModal.value = false;
            jobId.value = null;
        }

        const showFilters = ref(false);
        return {
            grid,
            wrapperRef,
            data,
            showColumn,
            toggleColumnVisibility,
            showConfirmAssignDriverModal,
            closeModal,
            jobId,
            showFilters
        };
    },
}
</script>
<template>
    <AppLayout title="Pending Pickups">
        <template #header>Pending Pickups</template>

        <Breadcrumb/>

        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Pending Pickups
                    </h2>

                    <div class="flex">
                        <Popper >
                            <button
                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                <i class="fa-solid fa-grip"></i>
                            </button>
                            <template #content>
                                <div class="max-w-[16rem]">
                                    <div class="popper-box w-64 rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700">
                                        <div
                                            class="rounded-md border border-slate-150 bg-white p-4 dark:border-navy-600 dark:bg-navy-700">
                                            <h3 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                                Select Columns
                                            </h3>
                                            <p class="mt-1 text-xs+">Choose which columns you want to see </p>
                                            <div class="mt-4 flex flex-col space-y-4 text-slate-600 dark:text-navy-100">
                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.reference"
                                                        @change="toggleColumnVisibility('reference', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Reference</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.name"
                                                        @change="toggleColumnVisibility('name', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Name</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.address"
                                                        @change="toggleColumnVisibility('address', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Address</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.contact"
                                                        @change="toggleColumnVisibility('contact', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Contact</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.cargoMode"
                                                        @change="toggleColumnVisibility('cargoMode', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Cargo Mode</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.notes"
                                                        @change="toggleColumnVisibility('notes', $event)"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                    />
                                                    <p>Note</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.pickupDate"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('pickupDate', $event)"
                                                    />
                                                    <p>Pickup Date</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.pickupTimeStart"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('pickupTimeStart', $event)"
                                                    />
                                                    <p>Start Pickup Date</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.pickupTimeEnd"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('pickupTimeEnd', $event)"
                                                    />
                                                    <p>End Pickup Date</p>
                                                </label>

                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.email"
                                                        class="form-checkbox is-basic size-5 rounded border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                                        type="checkbox"
                                                        @change="toggleColumnVisibility('email', $event)"
                                                    />
                                                    <p>Email</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Popper>

                        <button @click="showFilters=true"
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </div>



                <div class=" mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <div ref="wrapperRef"></div>
                    </div>
                </div>
            </div>
        </div>

        <AssignDriverModal :drivers="drivers" :job-id="jobId" :show="showConfirmAssignDriverModal" @close="closeModal"/>

        <div class="block" v-show="showFilters">
            <div class="fixed inset-0 z-[100] bg-slate-900/60 transition-opacity duration-200 show block" x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
            <div class="fixed right-0 top-0 z-[101] h-full w-72">
                <div class="flex h-full w-full transform-gpu flex-col bg-white transition-transform duration-200 dark:bg-navy-700 show block" x-transition:enter="ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
<!--                 TODO close button-->
<!--                 TODO Filters-->
<!--                 TODO Filter Now Action Button-->
                </div>
            </div>
        </div>
    </AppLayout>
</template>
