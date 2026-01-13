<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref, watch, computed} from "vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Button from "primevue/button";
import Card from "primevue/card";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import InputText from "primevue/inputtext";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Checkbox from "primevue/checkbox";
import axios from "axios";
import {debounce} from "lodash";
import {useForm, usePage} from "@inertiajs/vue3";
import {push} from "notivue";
import Textarea from "primevue/textarea";
import FileUpload from "primevue/fileupload";

const props = defineProps({
    containers: Array,
});

const page = usePage();

const form = useForm({
    container_id: null,
    hbl_search: '',
    issue_type: null,
    selected_packages: [],
    remarks: '',
    photos: [],
});

const issueTypes = ref([
    {label: 'Unmanifest', value: 'Unmanifest'},
    {label: 'Overland', value: 'Overland'},
    {label: 'Shortland', value: 'Shortland'},
    {label: 'Damage', value: 'Damage'},
    {label: 'Other', value: 'Other'},
]);

const searchResults = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(10);
const currentPage = ref(1);
const fileInput = ref(null);
const selectedFiles = ref([]);

// Check if remarks and photos are required
const requiresRemarksAndPhotos = computed(() => {
    return form.issue_type === 'Damage' || form.issue_type === 'Other';
});

const searchHBL = debounce(async (page = 1) => {
    if (!form.hbl_search || form.hbl_search.length < 3) {
        searchResults.value = [];
        totalRecords.value = 0;
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/search-hbl-packages', {
            params: {
                hbl_number: form.hbl_search,
                page: page,
                per_page: perPage.value,
                container_id: form.container_id,
            }
        });
        searchResults.value = response.data.data.map(pkg => ({
            ...pkg,
            selected: false,
            has_issue: pkg.has_unloading_issue || false,
            issue_type: pkg.existing_issue_type || null
        }));
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error("Error searching HBL:", error);
        searchResults.value = [];
        totalRecords.value = 0;
    } finally {
        loading.value = false;
    }
}, 500);

watch(() => form.hbl_search, () => {
    currentPage.value = 1;
    searchHBL(1);
});

const onPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = event.page + 1;
    searchHBL(currentPage.value);
};

const updateSelectedPackages = () => {
    form.selected_packages = searchResults.value
        .filter(pkg => pkg.selected && !pkg.has_issue)
        .map(pkg => pkg.id);
};

const handleFileSelect = (event) => {
    const files = Array.from(event.files || []);
    selectedFiles.value = files;
    form.photos = files;
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.photos = selectedFiles.value;
};

const togglePackageSelection = (pkg) => {
    if (pkg.has_issue && pkg.selected) {
        pkg.selected = false;
        push.warning(`This package already has an ${pkg.issue_type} issue.`);
    }
    updateSelectedPackages();
};

const submitAndCreateNew = () => {
    // Validate before submitting
    if (!form.container_id || !form.issue_type || form.selected_packages.length === 0) {
        push.error('Please fill all required fields and select at least one package.');
        return;
    }

    // Check if remarks is required
    if (requiresRemarksAndPhotos.value && !form.remarks) {
        push.error('Remarks are required for Damage and Other issue types.');
        return;
    }

    // Use FormData for file uploads
    const formData = new FormData();
    formData.append('container_id', form.container_id);
    formData.append('issue_type', form.issue_type);
    formData.append('create_another', 'true');

    form.selected_packages.forEach((packageId, index) => {
        formData.append(`selected_packages[${index}]`, packageId);
    });

    if (form.remarks) {
        formData.append('remarks', form.remarks);
    }

    if (form.photos && form.photos.length > 0) {
        form.photos.forEach((photo, index) => {
            formData.append(`photos[${index}]`, photo);
        });
    }

    form.processing = true;
    form.clearErrors();

    axios.post(route('arrival.unloading-issues.store'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': usePage().props.csrf
        }
    })
        .then(response => {
            push.success('Unloading issues created successfully!');
            // Reset form for new entry
            form.hbl_search = '';
            form.selected_packages = [];
            form.remarks = '';
            form.photos = [];
            selectedFiles.value = [];
            searchResults.value = [];
            totalRecords.value = 0;
            currentPage.value = 1;
        })
        .catch(error => {
            console.error('Full error:', error);
            console.error('Error response:', error.response);

            if (error.response?.status === 422 && error.response?.data?.errors) {
                // Validation errors
                const errors = error.response.data.errors;
                Object.keys(errors).forEach(key => {
                    form.errors[key] = errors[key][0];
                });

                // Show specific error message for photo validation
                const photoErrors = Object.keys(errors).filter(key => key.startsWith('photos'));
                if (photoErrors.length > 0) {
                    push.error('Some photos have invalid format or size. Please check and try again.');
                } else {
                    push.error('Validation failed. Please check the form.');
                }
            } else if (error.response?.status === 403) {
                push.error('You do not have permission to perform this action.');
            } else if (error.response?.data?.message) {
                push.error(error.response.data.message);
            } else {
                push.error('An error occurred. Please try again.');
            }
        })
        .finally(() => {
            form.processing = false;
        });
};

const submitAndRedirect = () => {
    // Validate before submitting
    if (!form.container_id || !form.issue_type || form.selected_packages.length === 0) {
        push.error('Please fill all required fields and select at least one package.');
        return;
    }

    // Check if remarks is required
    if (requiresRemarksAndPhotos.value && !form.remarks) {
        push.error('Remarks are required for Damage and Other issue types.');
        return;
    }

    // Use FormData for file uploads via Inertia
    const formData = {
        container_id: form.container_id,
        issue_type: form.issue_type,
        selected_packages: form.selected_packages,
        remarks: form.remarks || null,
        _method: 'POST'
    };

    // Add photos if present
    if (form.photos && form.photos.length > 0) {
        form.photos.forEach((photo, index) => {
            formData[`photos[${index}]`] = photo;
        });
    }

    form.transform(() => formData).post(route('arrival.unloading-issues.store'), {
        forceFormData: true,
        onSuccess: () => {
            push.success('Unloading issues created successfully!');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);

            // Check for photo errors
            const photoErrors = Object.keys(errors).filter(key => key.startsWith('photos'));
            if (photoErrors.length > 0) {
                push.error('Some photos have invalid format or size. Please check and try again.');
            } else {
                push.error('Failed to create unloading issues. Please check the form.');
            }
        }
    });
};

const cancel = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Create Unloading Issue">
        <template #header>Create Unloading Issue</template>

        <Breadcrumb/>

        <div class="mt-5">
            <Card>
                <template #title>
                    <div class="text-xl font-semibold">New Unloading Issue</div>
                </template>

                <template #content>
                    <form class="space-y-6" @submit.prevent="submitAndRedirect">
                        <!-- Select Container -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FloatLabel variant="on">
                                <Select
                                    v-model="form.container_id"
                                    :class="{'p-invalid': form.errors.container_id}"
                                    :options="containers"
                                    class="w-full"
                                    input-id="container"
                                    optionLabel="container_number"
                                    optionValue="id"
                                    placeholder="Select Container"
                                />
                            </FloatLabel>
                            <small v-if="form.errors.container_id" class="text-red-500">{{ form.errors.container_id }}</small>

                            <!-- Issue Type -->
                            <FloatLabel variant="on">
                                <Select
                                    v-model="form.issue_type"
                                    :class="{'p-invalid': form.errors.issue_type}"
                                    :options="issueTypes"
                                    class="w-full"
                                    input-id="issue-type"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Issue Type"
                                />
                            </FloatLabel>
                            <small v-if="form.errors.issue_type" class="text-red-500">{{ form.errors.issue_type }}</small>
                        </div>

                        <!-- Search HBL -->
                        <div>
                            <FloatLabel variant="on">
                                <InputText
                                    v-model="form.hbl_search"
                                    class="w-full"
                                    input-id="hbl-search"
                                    placeholder="Search by HBL Number"
                                />
                            </FloatLabel>
                            <small class="text-gray-500">Enter at least 3 characters to search all HBLs</small>
                        </div>

                        <!-- Remarks Field (Required for Damage/Other) -->
                        <div v-if="requiresRemarksAndPhotos">
                            <FloatLabel variant="on">
                                <Textarea
                                    v-model="form.remarks"
                                    :class="{'p-invalid': form.errors.remarks}"
                                    class="w-full"
                                    input-id="remarks"
                                    placeholder="Enter remarks (required for Damage/Other)"
                                    rows="4"
                                />
                            </FloatLabel>
                            <small v-if="form.errors.remarks" class="text-red-500">{{ form.errors.remarks }}</small>
                        </div>

                        <!-- Photo Upload (Optional for Damage/Other) -->
                        <div v-if="requiresRemarksAndPhotos">
                            <label class="block text-sm font-medium mb-2">Upload Photos (Optional)</label>
                            <FileUpload
                                :multiple="true"
                                accept="image/*,application/pdf"
                                mode="basic"
                                @select="handleFileSelect"
                            >
                                <template #empty>
                                    <p>Drag and drop files here or click to upload.</p>
                                </template>
                            </FileUpload>

                            <!-- Display photo validation errors -->
                            <div v-if="Object.keys(form.errors).some(key => key.startsWith('photos'))" class="mt-2">
                                <small
                                    v-for="(error, key) in form.errors"
                                    v-show="key.startsWith('photos')"
                                    :key="key"
                                    class="block text-red-500 mb-1"
                                >
                                    {{ error }}
                                </small>
                            </div>

                            <!-- Display selected files -->
                            <div v-if="selectedFiles.length > 0" class="mt-3">
                                <p class="text-sm font-medium mb-2">Selected Files:</p>
                                <div class="space-y-2">
                                    <div
                                        v-for="(file, index) in selectedFiles"
                                        :key="index"
                                        class="flex items-center justify-between p-2 bg-gray-50 rounded"
                                    >
                                        <span class="text-sm">{{ file.name }}</span>
                                        <Button
                                            icon="pi pi-times"
                                            severity="danger"
                                            size="small"
                                            text
                                            @click="removeFile(index)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- HBL Packages Results -->
                        <div v-if="searchResults.length > 0">
                            <Card>
                                <template #title>
                                    <div class="flex justify-between items-center">
                                        <span>HBL Packages</span>
                                    </div>
                                </template>

                                <template #content>
                                    <DataTable
                                        :loading="loading"
                                        :value="searchResults"
                                        :rows="perPage"
                                        :rowsPerPageOptions="[5, 10, 20, 50]"
                                        :totalRecords="totalRecords"
                                        lazy
                                        paginator
                                        tableStyle="min-width: 50rem"
                                        @page="onPageChange"
                                    >
                                        <Column header="Select" style="width: 5rem">
                                            <template #body="slotProps">
                                                <Checkbox
                                                    v-model="slotProps.data.selected"
                                                    :binary="true"
                                                    :disabled="slotProps.data.has_issue"
                                                    @update:modelValue="togglePackageSelection(slotProps.data)"
                                                />
                                            </template>
                                        </Column>

                                        <Column header="Status" style="width: 8rem">
                                            <template #body="slotProps">
                                                <span v-if="slotProps.data.has_issue" class="text-red-600 text-sm font-semibold">
                                                    {{ slotProps.data.issue_type }} Issue
                                                </span>
                                                <span v-else class="text-green-600 text-sm">Available</span>
                                            </template>
                                        </Column>

                                        <Column field="hbl_number" header="HBL Number"></Column>
                                        <Column field="hbl_name" header="Name"></Column>
                                        <Column field="weight" header="Weight">
                                            <template #body="slotProps">
                                                {{ slotProps.data.weight ? slotProps.data.weight.toFixed(2) : '-' }}
                                            </template>
                                        </Column>
                                        <Column field="volume" header="Volume">
                                            <template #body="slotProps">
                                                {{ slotProps.data.volume ? slotProps.data.volume.toFixed(3) : '-' }}
                                            </template>
                                        </Column>

                                        <template #footer>
                                            In total there are {{ totalRecords }} packages found.
                                        </template>
                                    </DataTable>
                                </template>
                            </Card>
                        </div>

                        <div v-if="form.errors.selected_packages" class="text-red-500">
                            {{ form.errors.selected_packages }}
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-4">
                            <Button
                                :disabled="form.processing"
                                label="Cancel"
                                outlined
                                severity="secondary"
                                type="button"
                                @click="cancel"
                            />
                            <Button
                                :disabled="form.processing || form.selected_packages.length === 0 || !form.issue_type"
                                :loading="form.processing"
                                icon="pi pi-plus-circle"
                                label="Create Issue & Create New"
                                severity="info"
                                type="button"
                                @click="submitAndCreateNew"
                            />
                            <Button
                                :disabled="form.processing || form.selected_packages.length === 0 || !form.issue_type"
                                :loading="form.processing"
                                icon="pi pi-check"
                                label="Create Issue"
                                severity="success"
                                type="submit"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
