<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Popper from "vue3-popper";
import {computed, onMounted, reactive, ref} from "vue";
import {Grid, h} from "gridjs";
import {RowSelection} from "gridjs/plugins/selection";
import {push} from "notivue";
import moment from "moment";

export default {
    components: {AppLayout, Breadcrumb, Popper, RowSelection},
    props: {
        drivers: {},
        officers: {},
    },
    setup(props) {
        const showFilters = ref(false);
        const fromDate = moment(new Date()).subtract(1, 'months').format('YYYY-MM-DD');
        const toDate = moment(new Date()).format('YYYY-MM-DD');
        const wrapperRef = ref(null);
        let grid = null;

        const filters = reactive({
            fromDate: fromDate,
            toDate: toDate,
            drivers: {},
            officers: {},
            deliveryType: ['UBP', "Door to Door", "Gift"],
            cargoMode: ["Air Cargo", "Sea Cargo"],
        })

        // onMounted(() => {
        //     initializeGrid();
        // })

        const data = reactive({
            columnVisibility: {
                hbl: true,
                hbl_name: true,
                address: false,
                picked_date: true,
                weight: true,
                volume: true,
                grand_total: true,
                paid_amount: true,
                cargo_type: true,
                hbl_type: false,
                officer: false,
                actions: true,
            },
            selectedData: {},
        });

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

        const selectedData = ref([]);


        const createColumns = () => [
            {
                name: '#',
                formatter: (_, row) => {
                    return h('input',
                        {
                            type: 'checkbox',
                            className: 'form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent',
                            onChange: (event) => {
                                const isChecked = event.target.checked;
                                if (isChecked) {
                                    const rowData = row.cells.map(cell => cell.data); // Extract data from cells array
                                    selectedData.value.push(rowData); // Push extracted data into selectedData
                                } else {
                                    // Remove the specific row from selectedData (assuming uniqueness of rows)
                                    const index = selectedData.value.findIndex(selectedRow => {
                                        const rowData = row.cells.map(cell => cell.data);
                                        return JSON.stringify(selectedRow) === JSON.stringify(rowData);
                                    });
                                    if (index !== -1) {
                                        selectedData.value.splice(index, 1);
                                    }
                                }
                            }
                        });
                }
            },
            {name: 'HBL', hidden: !data.columnVisibility.hbl},
            {name: 'Name', hidden: !data.columnVisibility.hbl_name},
            {name: 'Address', hidden: !data.columnVisibility.address},
            {name: 'Picked Date', hidden: !data.columnVisibility.picked_date},
            {name: 'Weight', hidden: !data.columnVisibility.weight},
            {name: 'Volume', hidden: !data.columnVisibility.volume},
            {name: 'Amount', hidden: !data.columnVisibility.grand_total},
            {name: 'Paid', hidden: !data.columnVisibility.paid_amount},
            {name: 'Cargo Mode', hidden: !data.columnVisibility.cargo_type},
            {name: 'Delivery Type', hidden: !data.columnVisibility.hbl_type},
            {name: 'Officer', hidden: !data.columnVisibility.officer},
            {name: 'Actions', hidden: !data.columnVisibility.actions, sort: false},
        ];


        const baseUrl = ref('/cash-settlement-list')

        const constructUrl = () => {
            const params = new URLSearchParams();
            for (const key in filters) {
                if (filters.hasOwnProperty(key)) {
                    params.append(key, filters[key].toString());
                }
            }
            return baseUrl.value + '?' + params.toString();
        }

        const initializeGrid = () => {
            const visibleColumns = Object.keys(data.columnVisibility);

            grid = new Grid({
                columns: createColumns(),
                search: {
                    debounceTimeout: 1000,
                    server: {
                        url: (prev, keyword) => `${prev}?search=${keyword}`
                    }
                },
                sort: {
                    multiColumn: false,
                    server: {
                        url: (prev, columns) => {
                            if (!columns.length) return prev;
                            const col = columns[0];
                            const dir = col.direction === 1 ? 'asc' : 'desc';
                            let colName = Object.keys(data.columnVisibility).filter(key => data.columnVisibility[key])[col.index];

                            return `${prev}&order=${colName}&dir=${dir}`;
                        }
                    }
                },
                pagination: {
                    limit: 10,
                    server: {
                        url: (prev, page, limit) => `${prev}&limit=${limit}&offset=${page * limit}`
                    }
                },
                server: {
                    url: constructUrl(),
                    then: data => data.data.map(item => {
                        const row = [];
                        row.push({id: item.id})
                        visibleColumns.forEach(column => {
                            row.push(item[column]);
                        });
                        return row;
                    }),
                    total: response => {
                        if (response && response.meta) {
                            return response.meta.total;
                        } else {
                            throw new Error('Invalid total count in server response');
                        }
                    }
                }
            });
            grid.render(wrapperRef.value);
        };

        const applyFilters = () => {
            showFilters.value = false;
            const newUrl = constructUrl();
            const visibleColumns = Object.keys(data.columnVisibility);
            grid.updateConfig({
                server: {
                    url: newUrl,
                    then: data => data.data.map(item => {
                        const row = [];
                        visibleColumns.forEach(column => {
                            row.push(item[column]);
                        });
                        return row;
                    }),
                }
            });
            grid.forceRender();
        }

        const totalRecord = ref(0);
        const totalGrandAmount = ref(0);
        const totalPaidAmount = ref(0);

        const getCashSettlementSummary = async (filters) => {
            try {
                const response = await fetch("/cash-settlement-summery", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify(filters)
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }

                const data = await response.json();
                totalRecord.value = data.totalRecords;
                totalGrandAmount.value = data.sumAmount;
                totalPaidAmount.value = data.sumPaidAmount;
            } catch (error) {
                console.error('Error:', error);
            }
        }

        const cashReceived = async () => {
            const idList = selectedData.value.map(item => item[0]);
            try {
                const response = await fetch("/cash-received", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify({'hbl_ids': idList})
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                } else {
                    window.location.reload();
                    push.success('Cash collected successfully!')
                }

            } catch (error) {
                console.error('Error:', error);
            }
        }

        const isDataEmpty = computed(() => selectedData.value.length === 0);
        const countOfSelectedData = computed(() => selectedData.value.length);
        const valueOfSelectedData = computed(() => {
            return selectedData.value.reduce((total, item) => {
                const grandTotal = parseFloat(item[7] || 0);
                return total + grandTotal;
            }, 0);
        });
        const paidValueOfSelectedData = computed(() => {
            return selectedData.value.reduce((total, item) => {
                const grandTotal = parseFloat(item[8] || 0);
                return total + grandTotal;
            }, 0);
        });

        const pageReady = async () => {
            await getCashSettlementSummary();
            if (totalRecord.value > 0) {
                initializeGrid();
            } else {
                console.log("no data");
            }
        }


        pageReady();


        return {
            showFilters,
            applyFilters,
            filters,
            wrapperRef,
            toggleColumnVisibility,
            data,
            selectedData,
            cashReceived,
            isDataEmpty,
            countOfSelectedData,
            valueOfSelectedData,
            paidValueOfSelectedData,
            totalRecord,
            totalGrandAmount,
            totalPaidAmount,

        }
    }
}

</script>

<template>
    <AppLayout title="Cash Settlements">
        <template #header>Cash Settlements</template>

        <Breadcrumb/>

        <div class="grid grid-cols-6 gap-4 mt-4">
            <div class="rounded-lg bg-white p-4 dark:bg-navy-600">
                <div class="flex justify-between space-x-1">
                    <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ totalRecord }}
                    </p>
                </div>
                <p class="mt-1 text-xs+">HBL Count</p>
            </div>

            <div class="rounded-lg bg-white p-4 dark:bg-navy-600">
                <div class="flex justify-between space-x-1">
                    <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ totalGrandAmount.toLocaleString() }}
                    </p>
                </div>
                <p class="mt-1 text-xs+">HBL Amount</p>
            </div>

            <div class="rounded-lg bg-white p-4 dark:bg-navy-600">
                <div class="flex justify-between space-x-1">
                    <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ totalPaidAmount.toLocaleString() }}
                    </p>
                </div>
                <p class="mt-1 text-xs+">HBL Paid Amount</p>
            </div>

            <div class="rounded-lg bg-white p-4 dark:bg-navy-600">
                <div class="flex justify-between space-x-1">
                    <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ countOfSelectedData }}
                    </p>
                </div>
                <p class="mt-1 text-xs+">Selected HBL Count</p>
            </div>

            <div class="rounded-lg bg-white p-4 dark:bg-navy-600">
                <div class="flex justify-between space-x-1">
                    <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ valueOfSelectedData.toLocaleString() }}
                    </p>
                </div>
                <p class="mt-1 text-xs+">Selected HBL Amount</p>
            </div>

            <div class="rounded-lg bg-white p-4 dark:bg-navy-600">
                <div class="flex justify-between space-x-1">
                    <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ paidValueOfSelectedData.toLocaleString() }}
                    </p>
                </div>
                <p class="mt-1 text-xs+">Selected HBL Paid Amount</p>
            </div>
        </div>


        <div class="card mt-4">
            <div>
                <div class="flex items-center justify-between p-2">
                    <div class="">
                        <div class="flex">
                            <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                                Cash Settlement List
                            </h2>
                        </div>

                        <div class="flex items-center mt-2 text-sm text-slate-500 dark:text-gray-300">
                            <div class="mr-4 cursor-pointer" x-tooltip.info.placement.bottom="'Applied Filters'">
                                Filter Options:
                            </div>
                            <div class="flex -space-x-px">
                                <div>
                                    <div
                                        class="tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        From Date
                                    </div>
                                    <div
                                        class="tag rounded-l-none bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        {{ filters.fromDate }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="ml-4 tag rounded-r-none bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                                        To Date
                                    </div>
                                    <div
                                        class="tag rounded-l-none bg-warning text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                                        {{ filters.toDate }}
                                    </div>
                                </div>
                                <div>
                                    <div v-if="filters.cargoMode" v-for="(mode, index) in filters.cargoMode"
                                         :key="index" class="badge bg-navy-700 text-white dark:bg-navy-900 ml-2">{{
                                            mode
                                        }}
                                    </div>
                                    <div v-if="filters.deliveryType" v-for="(type, index) in filters.deliveryType"
                                         :key="index" class="badge bg-success text-white ml-2">{{ type }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="flex">

                        <Popper>
                            <button x-tooltip.placement.top="'View columns'"
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
                                                        :checked="data.columnVisibility.address"
                                                        @change="toggleColumnVisibility('address', $event)"
                                                        class="form-switch mr-2 h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                                        type="checkbox"
                                                    />
                                                    Address
                                                </label>
                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.cargo_type"
                                                        @change="toggleColumnVisibility('cargo_type', $event)"
                                                        class="form-switch mr-2 h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                                        type="checkbox"
                                                    />
                                                    Cargo Mode
                                                </label>
                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.hbl_type"
                                                        @change="toggleColumnVisibility('hbl_type', $event)"
                                                        class="form-switch mr-2 h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                                        type="checkbox"
                                                    />
                                                    Delivery Type
                                                </label>
                                                <label class="inline-flex items-center space-x-2">
                                                    <input
                                                        :checked="data.columnVisibility.officer"
                                                        @change="toggleColumnVisibility('officer', $event)"
                                                        class="form-switch mr-2 h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                                                        type="checkbox"
                                                    />
                                                    Officer
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Popper>

                        <button x-tooltip.placement.top="'Filter result'" @click="showFilters=true"
                                class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                            <i class="fa-solid fa-filter"></i>
                        </button>

                        <button
                            @click="cashReceived"
                            :disabled="isDataEmpty"
                            class="btn font-medium text-white ml-2"
                            :class="{
            'bg-primary hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90': !isDataEmpty,
            'bg-gray-300 cursor-not-allowed': isDataEmpty
        }">
                            Cash Received
                        </button>
                    </div>
                </div>
                <div class=" mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto p-3">
                        <div v-if="totalRecord > 0" ref="wrapperRef"></div>
                        <div v-else class="flex items-center text-center justify-center h-48 rounded border border-2 border-dashed">
                            <div class="text-gray-600 ">
                                <h3 class="text-lg font-semibold mt-2">No records found</h3>
                                <p class="text-sm text-gray-500">Sorry, we couldn't find any records matching your criteria.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="block" v-show="showFilters">
            <div class="fixed inset-0 z-[100] bg-slate-900/60 transition-opacity duration-200 show block"
                 x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in" x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>
            <div class="fixed right-0 top-0 z-[101] h-full w-72">
                <div
                    class="flex h-full p-5 w-full transform-gpu flex-col bg-white transition-transform duration-200 dark:bg-navy-700 show block"
                    x-transition:enter="ease-out" x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0" x-transition:leave="ease-in"
                    x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                    <div class="my-3 flex h-5 items-center justify-between">
                        <h2 class="font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100 lg:text-base">
                            Filter Cash Settlement
                        </h2>

                        <button x-tooltip.placement.bottom.error="'Close filter drawer'" @click="showFilters = false"
                                class="btn -mr-1.5 size-7 rounded-full p-0 hover:bg-red-500/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="my-4 mx-5 h-px bg-slate-200 dark:bg-navy-500"></div>

                    <!--Filters-->
                    <div>
                        <span class="">From</span>
                        <label class="relative flex">
                            <input
                                v-model="filters.fromDate"
                                x-init="$el._x_flatpickr = flatpickr($el)"
                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Choose date..."
                                type="date"
                            />
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-5 transition-colors duration-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </span>
                        </label>
                    </div>

                    <div>
                        <span class="">to</span>
                        <label class="relative flex">
                            <input
                                v-model="filters.toDate"
                                x-init="$el._x_flatpickr = flatpickr($el)"
                                class="form-input peer w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="Choose date..."
                                type="date"
                            />
                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-5 transition-colors duration-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </span>
                        </label>
                    </div>
                    <div class="my-4 mx-5 h-px bg-slate-200 dark:bg-navy-500"></div>

                    <div>
                        <span class="font-medium">Cargo Mode</span>
                    </div>
                    <label class="inline-flex items-center space-x-2 mt-2">
                        <input v-model="filters.cargoMode" value="Air Cargo"
                               class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox"/>
                        <span>Air Cargo</span>
                    </label>

                    <label class="inline-flex items-center space-x-2 mt-2">
                        <input v-model="filters.cargoMode" value="Sea Cargo"
                               class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox"/>
                        <span>Sea Cargo</span>
                    </label>
                    <div class="my-4 mx-5 h-px bg-slate-200 dark:bg-navy-500"></div>
                    <div>
                        <span class="font-medium">Delivery Mode</span>
                    </div>
                    <label class="inline-flex items-center space-x-2 mt-2">
                        <input v-model="filters.deliveryType" value="UBP"
                               class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox"/>
                        <span>UPB</span>
                    </label>

                    <label class="inline-flex items-center space-x-2 mt-2">
                        <input v-model="filters.deliveryType" value="Door to Door"
                               class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox"/>
                        <span>Door to Door</span>
                    </label>
                    <label class="inline-flex items-center space-x-2 mt-2">
                        <input v-model="filters.deliveryType" value="Gift"
                               class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white"
                               type="checkbox"/>
                        <span>Gift</span>
                    </label>
                    <div class="my-4 mx-5 h-px bg-slate-200 dark:bg-navy-500"></div>
                    <label class="block">
                        <span class="font-medium">Select Drivers</span>
                        <select
                            v-model="filters.drivers"
                            x-init="$el._tom = new Tom($el,{   plugins: ['remove_button']})"
                            class="mt-1.5 w-full"
                            placeholder="Select drivers..."
                            autocomplete="off">
                            <option value="">Select drivers...</option>
                            <option v-for="(driver,id) in drivers" :key="id" :value="driver.id">{{ driver.name }}
                            </option>
                        </select>
                    </label>
                    <div class="my-4 mx-5 h-px bg-slate-200 dark:bg-navy-500"></div>
                    <label class="block">
                        <span class="font-medium">Select Officers</span>
                        <select
                            v-model="filters.officers"
                            x-init="$el._tom = new Tom($el,{   plugins: ['remove_button']})"
                            class="mt-1.5 w-full"
                            placeholder="Select officers..."
                            autocomplete="off">
                            <option value="">Select officers...</option>
                            <option v-for="(officer,id) in officers" :key="id" :value="officer.id">{{ officer.name }}
                            </option>
                        </select>
                    </label>

                    <!--Filter Now Action Button-->
                    <div class="my-4 mx-5 h-px bg-slate-200 dark:bg-navy-500"></div>
                    <button @click="applyFilters"
                            class="btn w-full space-x-2 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25">
                        <i class="fa-solid fa-filter"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>
[type='checkbox']:checked {
    background-image: none !important;
}

[type='checkbox']:focus, [type='radio']:focus {
    --tw-ring-offset-width: 0 !important;
}

.popper {
    inset: 0 auto auto -15px !important;
}

:root {
    --popper-theme-box-shadow: 0 6px 30px -6px rgba(0, 0, 0, 0.25);
}
</style>
