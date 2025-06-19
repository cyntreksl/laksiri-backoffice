<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import hblImage from "../../../images/illustrations/hblimage.png";
import Fieldset from "primevue/fieldset";
import SelectButton from "primevue/selectbutton";
import Card from "primevue/card";
import InputError from "@/Components/InputError.vue";
import {useForm, router} from "@inertiajs/vue3";
import Select from "primevue/select";
import InputLabel from "@/Components/InputLabel.vue";
import ContainerCreateDialog from "@/Pages/Container/ContainerCreateDialog.vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import FileUpload from "primevue/fileupload";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Badge from "primevue/badge";
import {ref, reactive, watch} from "vue";
import axios from "axios";

const props = defineProps({
  agents: {
    type: Object,
    default: () => {
    },
  },
  hblTypes: {
    type: Object,
    default: () => {
    },
  },
  cargoTypes: {
    type: Object,
    default: () => {
    },
  },
  shipments: {
    type: Object,
    default: () => {
    },
  },
  airLines: {
    type: Object,
    default: () => {
    },
  },
});

const form = useForm({
  cargo_type: null,
  hbl_type: null,
  warehouse: null,
  agent: null,
  shipment: null,
  session_id: null,
  errors: {}
});

const showCreateShipmentDialog = ref(false);
const showCsvImportDialog = ref(false);
const showHblPreviewDialog = ref(false);
const selectedHblForPreview = ref(null);
const importedHbls = ref([]);
const uploadProgress = ref(0);
const isUploading = ref(false);
const isSaving = ref(false);

const importState = reactive({
  sessionId: null,
  hbls: [],
  isImported: false
});

// Only reset import state if cargo type changes (since it affects import context)
watch(() => form.cargo_type, (newCargoType, oldCargoType) => {
  // Only reset if cargo type actually changed and we had imported data
  if (oldCargoType !== null && newCargoType !== oldCargoType && importState.isImported) {
    importState.sessionId = null;
    importState.hbls = [];
    importState.isImported = false;
  }
});

const handleCsvUpload = async (event) => {
  const file = event.files[0];
  if (!file) return;

  isUploading.value = true;
  uploadProgress.value = 0;

  const formData = new FormData();
  formData.append('csv_file', file);

  try {
    const response = await axios.post(route('third-party-shipments.import-csv'), formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
      }
    });

    if (response.data.success) {
      importState.sessionId = response.data.session_id;
      form.session_id = response.data.session_id;
      await loadImportedHbls();
      showCsvImportDialog.value = false;
      importState.isImported = true;
    } else {
      alert('Import failed: ' + (response.data.message || 'Unknown error'));
    }
  } catch (error) {
    console.error('Upload error:', error);
    alert('Upload failed: ' + (error.response?.data?.message || error.message));
  } finally {
    isUploading.value = false;
    uploadProgress.value = 0;
  }
};

const loadImportedHbls = async () => {
  if (!importState.sessionId) return;

  try {
    const response = await axios.get(route('third-party-shipments.get-tmp-hbls'), {
      params: { session_id: importState.sessionId }
    });

    if (response.data.success) {
      importState.hbls = response.data.hbls;
    }
  } catch (error) {
    console.error('Error loading HBLs:', error);
  }
};

const viewHblDetails = (hbl) => {
  selectedHblForPreview.value = hbl;
  showHblPreviewDialog.value = true;
};

const downloadSample = () => {
  window.open(route('third-party-shipments.download-sample'), '_blank');
};

const handleHBLCreate = async () => {
  if (!importState.isImported || !importState.sessionId) {
    alert('Please import CSV data first');
    return;
  }

  if (!form.cargo_type || !form.hbl_type || !form.agent || !form.shipment) {
    alert('Please fill in all required fields');
    return;
  }

  // Prevent multiple clicks
  if (isSaving.value) {
    return;
  }

  isSaving.value = true;

  try {
    const response = await axios.post(route('third-party-shipments.save-shipment'), {
      session_id: importState.sessionId,
      cargo_type: form.cargo_type,
      hbl_type: form.hbl_type,
      agent: form.agent,
      shipment: form.shipment,
    });

    if (response.data.success) {
      alert(`Successfully created ${response.data.hbls_created} HBLs`);
      // Refresh the page to clear all data
      window.location.reload();
    } else {
      alert('Save failed: ' + response.data.message);
      isSaving.value = false; // Re-enable if failed
    }
  } catch (error) {
    console.error('Save error:', error);
    alert('Save failed: ' + (error.response?.data?.message || error.message));
    isSaving.value = false; // Re-enable if failed
  }
};
</script>

<template>
  <AppLayout title="Create Third Party Shipment">
    <template #header>Create Third Party Shipment</template>

    <main class="kanban-app w-full">
      <div
          class="flex items-center justify-between space-x-2 px-[var(--margin-x)] py-5 transition-all duration-[.25s]">
        <div class="flex items-center space-x-1">
          <h3 class="text-lg font-medium text-slate-700 line-clamp-1 dark:text-navy-50">
            Create Third Party Shipment
          </h3>
        </div>
      </div>

      <form @submit.prevent="handleHBLCreate">
        <div class="grid grid-cols-3 my-4 gap-4">
          <div class="grid grid-rows gap-4">
            <Card>
              <template #title>Primary Details</template>
              <template #content>
                <Fieldset legend="Agent">
                  <div class="col-span-4">
                    <Select
                        v-model="form.agent"
                        :options="agents"
                        :option-label="(option) => option.name"
                        :required="true"
                        class="w-full"
                        filter
                        option-value="id"
                        placeholder="Select Agent"
                    />
                  </div>
                </Fieldset>

                <Fieldset legend="Cargo Type">
                  <SelectButton v-model="form.cargo_type" :options="cargoTypes" name="Cargo Type">
                    <template #option="slotProps">
                      <div class="flex items-center">
                        <i v-if="slotProps.option === 'Sea Cargo'" class="ti ti-ship mr-2"></i>
                        <i v-else class="ti ti-plane mr-2"></i>
                        <span>{{ slotProps.option }}</span>
                      </div>
                    </template>
                  </SelectButton>
                  <InputError :message="form.errors.cargo_type"/>
                </Fieldset>

                <Fieldset legend="Type">
                  <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type"/>
                  <InputError :message="form.errors.hbl_type"/>
                </Fieldset>

                <div class="flex justify-center mt-36">
                  <img :src="hblImage" alt="hbl-image" class="w-3/4">
                </div>
              </template>
            </Card>
          </div>

          <div class="col-span-2 ">
            <Card class="mb-4">
              <template #title>
                <div class="flex items-center justify-between w-full">
                  <span>Container Details</span>
                  <Button
                    :disabled="!form.cargo_type"
                    label="Create Container"
                    icon="ti ti-plus"
                    class="p-button-success ml-auto"
                    @click="showCreateShipmentDialog = true"
                  />
                </div>
              </template>
              <template #content>
                <Fieldset legend="Select Container">
                  <div class="col-span-4">
                    <Select
                        v-model="form.shipment"
                        :options="props.shipments"
                        :option-label="(option) => option.container_number"
                        :required="true"
                        class="w-full"
                        filter
                        option-value="id"
                        placeholder="Select Container"
                    />
                  </div>
                </Fieldset>
              </template>
            </Card>

            <Card class="mb-4">
              <template #title>
                <div class="flex items-center justify-between w-full">
                  <span>Package Details</span>
                  <Button
                    label="Import by CSV"
                    icon="ti ti-upload"
                    class="p-button-outlined"
                    @click="showCsvImportDialog = true"
                  />
                </div>
              </template>
              <template #content>
                <div v-if="!importState.isImported" class="text-center text-gray-500 py-8">
                  <i class="ti ti-file-spreadsheet text-4xl mb-2"></i>
                  <p>Import CSV file to view package details</p>
                </div>

                <div v-else class="space-y-4">
                  <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <p class="text-green-800 font-medium">
                      <i class="ti ti-check-circle mr-2"></i>
                      Successfully imported {{ importState.hbls.length }} HBLs with
                      {{ importState.hbls.reduce((total, hbl) => total + hbl.packages_count, 0) }} packages
                    </p>
                  </div>

                  <DataTable :value="importState.hbls" class="mt-4">
                    <Column field="hbl_number" header="HBL Number">
                      <template #body="slotProps">
                        <div class="font-medium">{{ slotProps.data.hbl_number }}</div>
                      </template>
                    </Column>
                    <Column field="hbl_name" header="Shipper Name">
                      <template #body="slotProps">
                        <div>{{ slotProps.data.hbl_name }}</div>
                      </template>
                    </Column>
                    <Column field="consignee_name" header="Consignee Name">
                      <template #body="slotProps">
                        <div>{{ slotProps.data.consignee_name }}</div>
                      </template>
                    </Column>
                    <Column field="packages_count" header="Packages">
                      <template #body="slotProps">
                        <Badge :value="slotProps.data.packages_count" class="bg-blue-100 text-blue-800"></Badge>
                      </template>
                    </Column>
                    <Column field="total_weight" header="Total Weight">
                      <template #body="slotProps">
                        <div>{{ slotProps.data.total_weight || 0 }} kg</div>
                      </template>
                    </Column>
                    <Column field="total_volume" header="Total Volume">
                      <template #body="slotProps">
                        <div>{{ slotProps.data.total_volume || 0 }} m³</div>
                      </template>
                    </Column>
                    <Column header="Actions">
                      <template #body="slotProps">
                        <Button
                          icon="ti ti-eye"
                          class="p-button-text p-button-sm"
                          @click="viewHblDetails(slotProps.data)"
                          v-tooltip="'View Details'"
                        />
                      </template>
                    </Column>
                  </DataTable>
                </div>
              </template>
                        </Card>

            <!-- Save Button -->
            <div v-if="importState.isImported" class="mt-6">
              <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border border-green-200">
                <div class="text-center">
                  <div class="mb-4">
                    <p class="text-lg font-medium text-gray-700 mb-2">Ready to Process</p>
                    <p class="text-sm text-gray-600">
                      {{ importState.hbls.length }} HBLs with
                      {{ importState.hbls.reduce((total, hbl) => total + hbl.packages_count, 0) }} packages
                      will be created and loaded into the selected container
                    </p>
                  </div>
                  <Button
                    :label="isSaving ? 'Creating HBLs...' : 'Create HBLs & Load Container'"
                    :icon="isSaving ? 'ti ti-loader-2' : 'ti ti-rocket'"
                    :class="['p-button-lg px-8 py-3', isSaving ? 'p-button-secondary' : 'p-button-success']"
                    @click="handleHBLCreate"
                    :disabled="!form.cargo_type || !form.hbl_type || !form.agent || !form.shipment || !importState.isImported || isSaving"
                    :loading="isSaving"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

    </main>

    <Dialog v-model:visible="showCreateShipmentDialog" header="Create New Container" modal>
      <div class="flex items-center gap-4 mb-4">
        <ContainerCreateDialog
            :warehouse="2"
            :air-lines="props.airLines"
            :cargo-type="form.cargo_type"
            @containerCreated="(shipment) => {
              showCreateShipmentDialog = false;
            }"
        />
      </div>
    </Dialog>

    <!-- CSV Import Dialog -->
    <Dialog v-model:visible="showCsvImportDialog" header="Import HBL Data from CSV" modal class="w-[600px]">
      <div class="space-y-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
          <h4 class="font-medium text-blue-800 mb-2">
            <i class="ti ti-info-circle mr-2"></i>
            CSV Format Requirements
          </h4>
          <ul class="text-sm text-blue-700 space-y-1">
            <li>• HBL data will be grouped by HBL NUMBER column</li>
            <li>• Multiple rows with same HBL NUMBER will create multiple packages for that HBL</li>
            <li>• All dimensions will be converted to centimeters automatically</li>
            <li>• Volume will be auto-calculated (Length × Width × Height × Quantity)</li>
            <li>• Supported measure types: cm, inches, meters, feet, millimeters</li>
            <li>• All required fields must be filled for successful import</li>
          </ul>
        </div>

        <div class="flex justify-between items-center">
          <label class="font-medium">Upload CSV File</label>
          <Button
            label="Download Sample"
            icon="ti ti-download"
            class="p-button-text p-button-sm"
            @click="downloadSample"
          />
        </div>

        <FileUpload
          mode="basic"
          name="csv_file"
          accept=".csv"
          :maxFileSize="2048000"
          :auto="false"
          @select="handleCsvUpload"
          :disabled="isUploading"
          class="w-full"
        />

        <div v-if="isUploading" class="space-y-2">
          <div class="flex justify-between text-sm">
            <span>Uploading...</span>
            <span>{{ uploadProgress }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          class="p-button-text"
          @click="showCsvImportDialog = false"
          :disabled="isUploading"
        />
      </template>
    </Dialog>

    <!-- HBL Preview Dialog -->
    <Dialog v-model:visible="showHblPreviewDialog" header="HBL Details" modal class="w-[800px]">
      <div v-if="selectedHblForPreview" class="space-y-6">
        <!-- HBL Info -->
        <Card>
          <template #title>HBL Information</template>
          <template #content>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-600">HBL Number</label>
                <p class="font-medium">{{ selectedHblForPreview.hbl_number }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Shipper Name</label>
                <p>{{ selectedHblForPreview.hbl_name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Email</label>
                <p>{{ selectedHblForPreview.email }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Contact</label>
                <p>{{ selectedHblForPreview.contact_number }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Consignee Name</label>
                <p>{{ selectedHblForPreview.consignee_name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Total Packages</label>
                <p>{{ selectedHblForPreview.packages_count }}</p>
              </div>
            </div>
          </template>
        </Card>

        <!-- Packages Info -->
        <Card>
          <template #title>Package Details</template>
          <template #content>
            <DataTable :value="selectedHblForPreview.packages" class="mt-4">
              <Column field="package_type" header="Type"></Column>
              <Column field="dimensions" header="Dimensions (L×W×H)"></Column>
              <Column field="quantity" header="Qty"></Column>
              <Column field="volume" header="Volume (m³)"></Column>
              <Column field="weight" header="Weight (kg)"></Column>
              <Column field="measure_type" header="Measure"></Column>
              <Column field="remarks" header="Remarks"></Column>
            </DataTable>
          </template>
        </Card>
      </div>

      <template #footer>
        <Button
          label="Close"
          class="p-button-text"
          @click="showHblPreviewDialog = false"
        />
      </template>
    </Dialog>
  </AppLayout>
</template>
