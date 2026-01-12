<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { computed, reactive, ref, watch } from "vue";
import InputError from "@/Components/InputError.vue";
import { useConfirm } from "primevue/useconfirm";
import { push } from "notivue";
import moment from "moment";
import hblImage from "../../../../resources/images/illustrations/hblimage.png";
import HBLDetailModal from "@/Pages/Common/Dialog/HBL/Index.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Select from "primevue/select";
import Checkbox from "primevue/checkbox";
import Textarea from "primevue/textarea";
import SelectButton from "primevue/selectbutton";
import Button from "primevue/button";
import Fieldset from "primevue/fieldset";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputNumber from "primevue/inputnumber";
import Dialog from "primevue/dialog";
import Message from "primevue/message";
import DatePicker from "primevue/datepicker";
import Stepper from "primevue/stepper";
import StepList from "primevue/steplist";
import StepPanels from "primevue/steppanels";
import StepItem from "primevue/stepitem";
import Step from "primevue/step";
import StepPanel from "primevue/steppanel";
import Tag from "primevue/tag";

const props = defineProps({
    hblTypes: {
        type: Object,
        default: () => {},
    },
    cargoTypes: {
        type: Object,
        default: () => {},
    },
    packageTypes: {
        type: Array,
        default: () => [],
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },
    agents: {
        type: Object,
        default: () => {},
    },
    shipments: {
        type: Object,
        default: () => {},
    },
    airLines: {
        type: Object,
        default: () => {},
    },
    containerTypes: {
        type: Array,
        default: () => [],
    },
    seaContainerOptions: {
        type: Array,
        default: () => [],
    },
    airContainerOptions: {
        type: Array,
        default: () => [],
    },
    warehouses: {
        type: Object,
        default: () => {},
    },
    airLinesList: {
        type: Array,
        default: () => [],
    },
});

const measureTypes = ref(["cm", "m", "in", "ft"]);
const confirm = useConfirm();

// Stepper state for two-step bulk creation workflow
const activeStep = ref(0);
const hblsCreatedCount = ref(0);
const stepActivateCallback = ref(null); // Store the activate callback
const commonFields = reactive({
    agent: null,
    cargo_type: "",
    hbl_type: "",
    shipment: null,
});
const isCommonFieldsLocked = ref(false);

// HBL Number management for bulk creation
const baseHBLNumber = ref(""); // Starting number set by user
const currentHBLNumber = ref(""); // Current number (auto-incremented)
const isHBLNumberManuallyOverridden = ref(false);

// Container creation modal
const showCreateContainerDialog = ref(false);
const containerForm = useForm({
    cargo_type: "Sea Cargo",
    container_type: "",
    reference: "",
    bl_number: "",
    awb_number: "",
    estimated_time_of_departure: "",
    estimated_time_of_arrival: "",
    container_number: "",
    seal_number: "",
    vessel_name: "",
    voyage_number: "",
    shipping_line: "",
    port_of_loading: "",
    port_of_discharge: "",
    flight_number: "",
    air_line_id: "",
    airline_name: "",
    airport_of_departure: "",
    airport_of_arrival: "",
    target_warehouse: "",
});

const containerTypesForModal = ref(props.seaContainerOptions);

watch(
    () => containerForm.cargo_type,
    (newVal) => {
        if (newVal === "Sea Cargo") {
            containerTypesForModal.value = props.seaContainerOptions;
        } else {
            containerTypesForModal.value = props.airContainerOptions;
            containerForm.container_type = "Custom";
        }
    }
);

const openCreateContainerDialog = () => {
    // Set cargo type from main form if available
    if (form.cargo_type) {
        containerForm.cargo_type = form.cargo_type;
    }
    // Generate reference number
    generateContainerReference();
    showCreateContainerDialog.value = true;
};

const closeCreateContainerDialog = () => {
    showCreateContainerDialog.value = false;
    containerForm.reset();
};

const generateContainerReference = async () => {
    try {
        const response = await fetch(
            route("third-party-shipments.generate-container-reference"),
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": usePage().props.csrf,
                },
            }
        );

        if (response.ok) {
            const data = await response.json();
            containerForm.reference = data.reference;
        }
    } catch (error) {
        console.log("Failed to generate reference:", error);
        // Fallback: generate a simple reference
        containerForm.reference = `CNT-${Date.now()}`;
    }
};

const handleCreateContainer = async () => {
    // Format dates
    if (
        containerForm.estimated_time_of_departure &&
        containerForm.estimated_time_of_departure !== "Invalid date"
    ) {
        containerForm.estimated_time_of_departure = moment(
            containerForm.estimated_time_of_departure
        ).format("YYYY-MM-DD");
    } else {
        containerForm.estimated_time_of_departure = null;
    }

    if (
        containerForm.estimated_time_of_arrival &&
        containerForm.estimated_time_of_arrival !== "Invalid date"
    ) {
        containerForm.estimated_time_of_arrival = moment(
            containerForm.estimated_time_of_arrival
        ).format("YYYY-MM-DD");
    } else {
        containerForm.estimated_time_of_arrival = null;
    }

    // Set airline ID if airline name is selected
    const selectedAirline = props.airLinesList.find(
        (airline) => airline.name === containerForm.airline_name
    );
    if (selectedAirline) {
        containerForm.air_line_id = selectedAirline.id;
    }

    try {
        const response = await fetch(
            route("third-party-shipments.create-container"),
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": usePage().props.csrf,
                },
                body: JSON.stringify(containerForm.data()),
            }
        );

        const result = await response.json();

        if (response.ok && result.success) {
            // Refresh the page to get updated shipments list
            router.reload({
                only: ["shipments"],
                onSuccess: () => {
                    // Auto-select the newly created container
                    if (result.container) {
                        form.shipment = result.container.id;
                    }
                    push.success("Container created successfully!");
                    closeCreateContainerDialog();
                },
            });
        } else {
            push.error(result.message || "Failed to create container");
        }
    } catch (error) {
        console.log("Container creation error:", error);
        push.error("Failed to create container. Please try again.");
    }
};

//branch set
const currentBranch = usePage().props?.auth.user.active_branch_name;

const findCountryCodeByBranch = (country) => {
    return usePage().props.currentBranch.country_code;
};

const countryCode = ref(findCountryCodeByBranch(currentBranch));
const consignee_countryCode = ref("+94");
const contactNumber = ref("");
const consignee_contact = ref("");

const splitNumber = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            countryCode.value = code;
            contactNumber.value = fullNumber.slice(code.length);
            break;
        }
    }
};

const splitNumberConsignee = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            consignee_countryCode.value = code;
            consignee_contact.value = fullNumber.slice(code.length);
            break;
        }
    }
};

const isSameContactNumber = ref(false);
const isSameConsigneeContactNumber = ref(false);

const additionalMobileCountryCode = ref(findCountryCodeByBranch());
const additionalMobileNumber = ref("");

const whatsappNumberCountryCode = ref(findCountryCodeByBranch());
const whatsappNumber = ref("");

const consigneeAdditionalMobileCountryCode = ref("+94");
const consigneeAdditionalMobileNumber = ref("");

const consigneeWhatsappNumberCountryCode = ref("+94");
const consigneeWhatsappNumber = ref("");

const form = useForm({
    hbl: "",
    hbl_name: "",
    agent: null,
    shipment: null,
    email: "",
    contact_number: computed(() => countryCode.value + contactNumber.value),
    additional_mobile_number: "",
    whatsapp_number: "",
    nic: "",
    iq_number: "",
    address: "",
    consignee_name: "",
    consignee_nic: "",
    consignee_contact: computed(
        () => consignee_countryCode.value + consignee_contact.value
    ),
    consignee_additional_mobile_number: "",
    consignee_whatsapp_number: "",
    consignee_address: "",
    consignee_note: "",
    cargo_type: "",
    hbl_type: "",
    warehouse: "COLOMBO",
    warehouse_id: 2,
    freight_charge: 0,
    bill_charge: 0,
    other_charge: 0,
    destination_charge: 0,
    package_charges: 0,
    discount: 0,
    paid_amount: 0,
    additional_charge: 0,
    grand_total: 0,
    packages: {},
    is_active_package: false,
    is_departure_charges_paid: false,
    is_destination_charges_paid: false,
});

const addContactToWhatsapp = () => {
    if (isSameContactNumber.value) {
        whatsappNumberCountryCode.value = countryCode.value;
        whatsappNumber.value = contactNumber.value;
    } else {
        resetWhatsappNumber();
    }
};

const resetWhatsappNumber = () => {
    whatsappNumberCountryCode.value = findCountryCodeByBranch();
    whatsappNumber.value = "";
};

const addConsigneeContactToWhatsapp = () => {
    if (isSameContactNumber.value) {
        consigneeWhatsappNumberCountryCode.value = consignee_countryCode.value;
        consigneeWhatsappNumber.value = consignee_contact.value;
    } else {
        resetConsigneeWhatsappNumber();
    }
};

const resetConsigneeWhatsappNumber = () => {
    consigneeWhatsappNumberCountryCode.value = findCountryCodeByBranch();
    consigneeWhatsappNumber.value = "";
};

const handleHBLCreate = () => {
    // Check if there are any packages
    if (
        packageList.value.length === 0 &&
        Object.values(copiedPackages.value).length === 0
    ) {
        push.error("Please add at least one package before creating an HBL");
        return;
    }

    form.additional_mobile_number =
        additionalMobileCountryCode.value + additionalMobileNumber.value;
    form.whatsapp_number =
        whatsappNumberCountryCode.value + whatsappNumber.value;
    form.consignee_additional_mobile_number =
        consigneeAdditionalMobileCountryCode.value +
        consigneeAdditionalMobileNumber.value;
    form.consignee_whatsapp_number =
        consigneeWhatsappNumberCountryCode.value +
        consigneeWhatsappNumber.value;
    if (form.additional_mobile_number === additionalMobileCountryCode.value) {
        form.additional_mobile_number = "";
    }
    if (form.whatsapp_number === whatsappNumberCountryCode.value) {
        form.whatsapp_number = "";
    }

    if (
        form.consignee_additional_mobile_number ===
        consigneeAdditionalMobileCountryCode.value
    ) {
        form.consignee_additional_mobile_number = "";
    }

    if (
        form.consignee_whatsapp_number ===
        consigneeWhatsappNumberCountryCode.value
    ) {
        form.consignee_whatsapp_number = "";
    }

    form.post(route("third-party-shipments.save-shipment.v2"), {
        onSuccess: () => {
            form.reset();
            push.success("HBL Created Successfully!");
            router.visit(route("third-party-shipments.create.v2"));
        },
        onError: () => console.log("error"),
        preserveScroll: true,
        preserveState: true,
    });
};

// Step navigation functions for bulk creation workflow
const proceedToStep2 = () => {
    // Validate that all common fields are filled
    if (
        !commonFields.agent ||
        !commonFields.cargo_type ||
        !commonFields.hbl_type ||
        !commonFields.shipment
    ) {
        push.error("Please fill all required fields before proceeding");
        return;
    }
    
    // Validate HBL Number is provided
    if (!baseHBLNumber.value) {
        push.error("Please enter the starting HBL Number before proceeding");
        return;
    }

    // Lock common fields and copy to main form
    isCommonFieldsLocked.value = true;
    form.agent = commonFields.agent;
    form.cargo_type = commonFields.cargo_type;
    form.hbl_type = commonFields.hbl_type;
    form.shipment = commonFields.shipment;
    
    // Initialize current HBL Number from base
    currentHBLNumber.value = baseHBLNumber.value;
    form.hbl = currentHBLNumber.value;

    // Move to step 2
    activeStep.value = 1;
};

const backToStep1 = () => {
    if (hblsCreatedCount.value > 0) {
        confirm.require({
            message: `You have created ${hblsCreatedCount.value} HBL(s) in this session. Going back will reset the session. Do you want to continue?`,
            header: "Confirm Navigation",
            icon: "pi pi-exclamation-triangle",
            rejectLabel: "Cancel",
            acceptLabel: "Yes, Go Back",
            accept: () => {
                resetBulkSession();
            },
        });
    } else {
        resetBulkSession();
    }
};

const resetBulkSession = () => {
    isCommonFieldsLocked.value = false;
    activeStep.value = 0;
    hblsCreatedCount.value = 0;
    resetHBLSpecificFields();
};

const resetHBLSpecificFields = () => {
    // Reset only HBL-specific fields, keep common fields
    // HBL Number: Auto-increment unless manually overridden
    if (!isHBLNumberManuallyOverridden.value && currentHBLNumber.value) {
        // Auto-increment: try to parse and increment number
        const match = currentHBLNumber.value.match(/(.*?)(\d+)$/);
        if (match) {
            const prefix = match[1];
            const number = parseInt(match[2]);
            currentHBLNumber.value = prefix + (number + 1);
        } else {
            // If no number found, just append "1"
            currentHBLNumber.value = currentHBLNumber.value + "1";
        }
    }
    
    // Update form with new HBL number
    form.hbl = currentHBLNumber.value;
    isHBLNumberManuallyOverridden.value = false; // Reset override flag
    
    form.hbl_name = "";
    form.email = "";
    contactNumber.value = "";
    additionalMobileNumber.value = "";
    whatsappNumber.value = "";
    form.nic = "";
    form.iq_number = "";
    form.address = "";
    form.consignee_name = "";
    form.consignee_nic = "";
    consignee_contact.value = "";
    consigneeAdditionalMobileNumber.value = "";
    consigneeWhatsappNumber.value = "";
    form.consignee_address = "";
    form.consignee_note = "";
    form.warehouse = "COLOMBO";
    form.warehouse_id = 2;
    form.freight_charge = 0;
    form.bill_charge = 0;
    form.other_charge = 0;
    form.destination_charge = 0;
    form.package_charges = 0;
    form.discount = 0;
    form.paid_amount = 0;
    form.additional_charge = 0;
    form.grand_total = 0;
    packageList.value = [];
    copiedPackages.value = {};
    grandTotalWeight.value = 0;
    grandTotalVolume.value = 0;
    isSameContactNumber.value = false;
    isSameConsigneeContactNumber.value = false;
    isChecked.value = false;
};

const saveAndAddAnother = () => {
    handleHBLCreateBulk(true);
};

const saveAndFinish = () => {
    handleHBLCreateBulk(false);
};

const handleHBLCreateBulk = (continueAdding) => {
    // Check if there are any packages
    if (
        packageList.value.length === 0 &&
        Object.values(copiedPackages.value).length === 0
    ) {
        push.error("Please add at least one package before creating an HBL");
        return;
    }

    form.additional_mobile_number =
        additionalMobileCountryCode.value + additionalMobileNumber.value;
    form.whatsapp_number =
        whatsappNumberCountryCode.value + whatsappNumber.value;
    form.consignee_additional_mobile_number =
        consigneeAdditionalMobileCountryCode.value +
        consigneeAdditionalMobileNumber.value;
    form.consignee_whatsapp_number =
        consigneeWhatsappNumberCountryCode.value +
        consigneeWhatsappNumber.value;

    if (form.additional_mobile_number === additionalMobileCountryCode.value) {
        form.additional_mobile_number = "";
    }
    if (form.whatsapp_number === whatsappNumberCountryCode.value) {
        form.whatsapp_number = "";
    }
    if (
        form.consignee_additional_mobile_number ===
        consigneeAdditionalMobileCountryCode.value
    ) {
        form.consignee_additional_mobile_number = "";
    }
    if (
        form.consignee_whatsapp_number ===
        consigneeWhatsappNumberCountryCode.value
    ) {
        form.consignee_whatsapp_number = "";
    }

    form.post(route("third-party-shipments.save-shipment.v2"), {
        onSuccess: (response) => {
            hblsCreatedCount.value++;
            const hblRef =
                response?.props?.flash?.hbl_reference ||
                `HBL #${hblsCreatedCount.value}`;
            push.success(`HBL Created Successfully! Reference: ${hblRef}`);

            if (continueAdding) {
                // Reset only HBL-specific fields, keep common fields locked
                resetHBLSpecificFields();
            } else {
                // Finish and redirect
                router.visit(route("third-party-shipments.index"));
            }
        },
        onError: () => {
            push.error(
                "Failed to create HBL. Please check the form and try again."
            );
            console.log("error");
        },
        preserveScroll: true,
        preserveState: continueAdding, // Preserve state only when continuing
    });
};

const showAddNewPackageDialog = ref(false);
const editMode = ref(false);
const showPackageDialog = () => {
    showAddNewPackageDialog.value = true;
    if (!editMode.value) {
        selectedType.value = "";
    }
};

const packageList = ref([]);

const packageItem = reactive({
    type:
        props.packageTypes.find(
            (type) => type.name.toLowerCase() === "carton".toLowerCase()
        )?.name || "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 1,
    volume: 0,
    volumetricWeight: 0,
    chargeableWeight: 0,
    totalWeight: 0,
    remarks: "",
    packageRule: 0,
    measure_type: "cm",
});

const grandTotalWeight = ref(0);
const grandTotalVolume = ref(0);

const addPackageData = () => {
    if (
        !packageItem.type ||
        packageItem.length <= 0 ||
        packageItem.width <= 0 ||
        packageItem.height <= 0 ||
        packageItem.quantity <= 0 ||
        packageItem.volume <= 0 ||
        form.is_active_package
    ) {
        push.error("Please fill all required data");
        return;
    }
    packageItem.length = packageItemLength.value;
    packageItem.width = packageItemWidth.value;
    packageItem.height = packageItemHeight.value;
    packageItem.volume = packageItemVolume.value;

    if (form.cargo_type === "Air Cargo") {
        if (packageItem.totalWeight <= 0 && packageItem.volumetricWeight <= 0) {
            push.error("Please fill the total weight");
            return;
        }
    }

    // Calculate chargeableWeight before adding to the list
    packageItem.chargeableWeight = Math.max(
        packageItem.volumetricWeight,
        packageItem.totalWeight
    );

    if (editMode.value) {
        packageList.value.splice(editIndex.value, 1, { ...packageItem });
        grandTotalWeight.value = packageList.value.reduce(
            (accumulator, currentValue) =>
                accumulator + parseFloat(currentValue.totalWeight),
            0
        );
        grandTotalVolume.value = packageList.value.reduce(
            (accumulator, currentValue) =>
                accumulator + parseFloat(currentValue.volume),
            0
        );
    } else {
        const newItem = { ...packageItem }; // Create a copy of packageItem
        packageList.value.push(newItem); // Add the new item to packageList
        form.packages = packageList.value;

        const volume = parseFloat(newItem.volume) || 0;
        grandTotalWeight.value += parseFloat(newItem.totalWeight);
        grandTotalVolume.value += parseFloat(volume.toFixed(3));
    }
    closeAddPackageModal();
};

const packageItemVolume = ref(0);

// Watch for changes in length, width, height, or quantity to update volume and totalWeight
watch(
    [
        () => packageItem.length,
        () => packageItem.width,
        () => packageItem.height,
        () => packageItem.quantity,
        () => packageItem.measure_type,
        () => form.cargo_type,
    ],
    ([
        newLength,
        newWidth,
        newHeight,
        newQuantity,
        newMeasureType,
        newCargoType,
    ]) => {
        // Convert dimensions from cm to meters
        const lengthMeters = newLength / 100; // 1 cm = 0.01 meters
        const widthMeters = newWidth / 100;
        const heightMeters = newHeight / 100;

        // Calculate volume in cubic meters (m³)
        const volumeCubicMeters =
            lengthMeters * widthMeters * heightMeters * newQuantity;

        // Calculate volumetric weight (L × W × H in cm) / 6000 for air cargo only
        if (newCargoType === "Air Cargo") {
            const lengthCM = convertMeasurementstocm(newMeasureType, newLength);
            const widthCM = convertMeasurementstocm(newMeasureType, newWidth);
            const heightCM = convertMeasurementstocm(newMeasureType, newHeight);
            packageItem.volumetricWeight =
                (lengthCM * widthCM * heightCM * newQuantity) / 6000;
        } else {
            packageItem.volumetricWeight = 0;
        }

        // Assuming weight is directly proportional to volume
        // Convert weight from grams to kilograms
        const totalWeightKg = (volumeCubicMeters * newQuantity) / 1000; // 1 gram = 0.001 kilograms

        // Update reactive properties
        packageItem.volume = (
            newLength *
            newWidth *
            newHeight *
            newQuantity
        ).toFixed(3);
        if (packageItem.measure_type === "cm") {
            // Convert cm³ to m³ by dividing by 1,000,000
            packageItemVolume.value = (packageItem.volume / 1000000).toFixed(3);
        } else if (packageItem.measure_type === "in") {
            // Convert from inches to cubic centimeters (1 inch = 16.387 cm³)
            packageItemVolume.value = (
                (packageItem.volume * 16.387) /
                1000000
            ).toFixed(3); // Convert to m³
        } else if (packageItem.measure_type === "ft") {
            // Convert from cubic feet to cubic meters (1 ft³ = 0.0283 m³)
            packageItemVolume.value = (packageItem.volume * 0.0283).toFixed(3);
        } else {
            // Assume volume is already in cubic meters if no unit conversion is needed
            packageItemVolume.value = packageItem.volume;
        }

        // packageItem.totalWeight = totalWeightKg;
    }
);

const vat = ref(0);

watch(
    [
        () => form.other_charge,
        () => form.discount,
        () => form.freight_charge,
        () => vat,
        () => form.additional_charge,
        () => form.bill_charge,
        () => form.destination_charge,
        () => form.package_charges,
    ],
    ([newOtherCharge, newDiscount, newFreightCharge]) => {
        // Convert dimensions from cm to meters
        hblTotal.value =
            parseFloat(form.freight_charge) +
            parseFloat(form.bill_charge) +
            parseFloat(form.package_charges) +
            parseFloat(form.destination_charge) +
            // parseFloat(form.other_charge) +
            parseFloat(vat.value) -
            form.discount +
            parseFloat(form.additional_charge);
        hblTotal.value = Number(hblTotal.value.toFixed(2));
        form.grand_total = hblTotal.value;
    }
);

const selectedType = ref("");

const isChecked = ref(false);

const addToConsigneeDetails = () => {
    if (isChecked.value) {
        form.consignee_name = form.hbl_name;
        consignee_countryCode.value = countryCode.value;
        consignee_contact.value = contactNumber.value;
        form.consignee_nic = form.nic;
        form.consignee_address = form.address;
        isSameConsigneeContactNumber.value = isSameContactNumber.value;
        consigneeWhatsappNumberCountryCode.value =
            whatsappNumberCountryCode.value;
        consigneeWhatsappNumber.value = whatsappNumber.value;
        consigneeAdditionalMobileCountryCode.value =
            additionalMobileCountryCode.value;
        consigneeAdditionalMobileNumber.value = additionalMobileNumber.value;
    } else {
        resetConsigneeDetails();
    }
};

const resetConsigneeDetails = () => {
    form.consignee_name = "";
    consignee_contact.value = "";
    form.consignee_nic = "";
    form.consignee_address = "";
    consigneeAdditionalMobileNumber.value = "";
};

const updateTypeDescription = () => {
    packageItem.type = (packageItem.type ? " " : "") + selectedType.value;
};

const hblTotal = ref(0);

const closeViewModal = () => {
    showConfirmViewHBLModal.value = false;
    hblId.value = null;
    router.visit(route("hbls.create"));
};

const handleRemovePackage = (index) => {
    if (index !== null) {
        grandTotalVolume.value -= packageList.value[index].volume;
        grandTotalWeight.value -= packageList.value[index].totalWeight;
        packageList.value.splice(index, 1);
    }
};

// edit package
const closeAddPackageModal = () => {
    showAddNewPackageDialog.value = false;
    editIndex.value = null;
    editMode.value = false;
    restModalFields();
};

const restModalFields = () => {
    packageItem.type =
        props.packageTypes.find(
            (type) => type.name.toLowerCase() === "carton".toLowerCase()
        )?.name || "";
    packageItem.length = 0;
    packageItem.width = 0;
    packageItem.height = 0;
    packageItem.quantity = 1;
    packageItem.volume = 0;
    packageItem.volumetricWeight = 0;
    packageItem.totalWeight = 0;
    packageItem.remarks = "";
    packageItem.packageRule = 0;
};

const editIndex = ref(null);

const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPackageDialog.value = true;
    // populate packageItem with existing data for editing
    Object.assign(packageItem, packageList.value[index]);
    const factor = conversionFactors[packageItem.measure_type] || 1;
    packageItem.length = packageItem.length / factor;
    packageItem.width = packageItem.width / factor;
    packageItem.height = packageItem.height / factor;
};

const copyFromHBLToShipperModalShow = ref(false);

const reference = ref(null);

const closeCopyFromHBLToShipperModal = () => {
    reference.value = null;
    copyFromHBLToShipperModalShow.value = false;
};

const handleCopyFromHBLToShipper = async () => {
    try {
        const response = await fetch(
            `/get-hbl-by-reference/${reference.value}`,
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": usePage().props.csrf,
                },
            }
        );

        if (!response.ok) {
            closeCopyFromHBLToShipperModal();
            push.error("HBL Missing or Invalid Reference Number");
            throw new Error("Network response was not ok.");
        } else {
            const data = await response.json();
            closeCopyFromHBLToShipperModal();

            form.hbl_name = data.hbl_name;
            form.email = data.email;
            form.nic = data.nic;
            form.iq_number = data.iq_number;
            form.address = data.address;

            splitNumber(data.contact_number);

            push.success("Copied!");
        }
    } catch (error) {
        console.log(error);
    }
};

const copyFromHBLToConsigneeModalShow = ref(false);

const closeCopyFromHBLToConsigneeModal = () => {
    reference.value = null;
    copyFromHBLToConsigneeModalShow.value = false;
};

const handleCopyFromHBLToConsignee = async () => {
    try {
        const response = await fetch(
            `/get-hbl-by-reference/${reference.value}`,
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": usePage().props.csrf,
                },
            }
        );

        if (!response.ok) {
            closeCopyFromHBLToConsigneeModal();
            push.error("HBL Missing or Invalid Reference Number");
            throw new Error("Network response was not ok.");
        } else {
            const data = await response.json();
            closeCopyFromHBLToConsigneeModal();

            form.consignee_name = data.consignee_name;
            form.consignee_nic = data.consignee_nic;
            form.consignee_address = data.consignee_address;
            form.consignee_note = data.consignee_note;

            splitNumberConsignee(data.consignee_contact);

            push.success("Copied!");
        }
    } catch (error) {
        console.log(error);
    }
};

const copiedPackages = ref({});

const copyFromHBLToPackageModalShow = ref(false);

const closeCopyFromHBLToPackageModal = () => {
    reference.value = null;
    copyFromHBLToPackageModalShow.value = false;
};

const handleCopyFromHBLToPackage = async () => {
    try {
        const response = await fetch(
            `/get-hbl-packages-by-reference/${reference.value}`,
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": usePage().props.csrf,
                },
            }
        );

        if (!response.ok) {
            closeCopyFromHBLToPackageModal();
            push.error("HBL Packages Missing or Invalid Reference Number");
            throw new Error("Network response was not ok.");
        } else {
            const data = await response.json();
            closeCopyFromHBLToPackageModal();
            copiedPackages.value = data;

            const copiedTotalWeight = copiedPackages.value.reduce(
                (acc, curr) => acc + curr.weight,
                0
            );
            const copiedTotalVolume = copiedPackages.value.reduce(
                (acc, curr) => acc + curr.volume,
                0
            );

            grandTotalWeight.value += copiedTotalWeight;
            grandTotalVolume.value += copiedTotalVolume;

            push.success("Copied!");
        }
    } catch (error) {
        console.log(error);
    }
};

const handleCopyShipper = () => {
    form.consignee_name = form.hbl_name;
    form.consignee_nic = form.nic;
};

const packageItemLength = ref(0);
const packageItemWidth = ref(0);
const packageItemHeight = ref(0);

const conversionFactors = {
    cm: 1,
    m: 100,
    in: 2.54,
    ft: 30.48,
};

function convertMeasurementstocm(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return value * factor;
}

watch(
    () => packageItem.measure_type,
    (newMeasureType) => {
        packageItemLength.value = convertMeasurementstocm(
            newMeasureType,
            packageItem.length
        );
        packageItemWidth.value = convertMeasurementstocm(
            newMeasureType,
            packageItem.width
        );
        packageItemHeight.value = convertMeasurementstocm(
            newMeasureType,
            packageItem.height
        );
    }
);

watch([() => packageItem.length], ([newLength]) => {
    packageItemLength.value = convertMeasurementstocm(
        packageItem.measure_type,
        newLength
    );
});

watch([() => packageItem.width], ([newWidth]) => {
    packageItemWidth.value = convertMeasurementstocm(
        packageItem.measure_type,
        newWidth
    );
});

watch([() => packageItem.height], ([newHeight]) => {
    packageItemHeight.value = convertMeasurementstocm(
        packageItem.measure_type,
        newHeight
    );
});

const volumeUnit = computed(() => {
    const units = {
        cm: "CM.CU",
        m: "M.CU",
        in: "IN.CU",
        ft: "FT.CU",
    };
    return units[packageItem.measure_type] || "M.CM";
});

const hblId = ref(null);
const showConfirmViewHBLModal = ref(false);

const confirmRemovePackage = (index) => {
    confirm.require({
        message: "Would you like to remove this hbl package record?",
        header: "Remove Package?",
        icon: "pi pi-info-circle",
        rejectLabel: "Cancel",
        rejectProps: {
            label: "Cancel",
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: "Remove Package",
            severity: "danger",
        },
        accept: () => {
            handleRemovePackage(index);
        },
        reject: () => {},
    });
};

const onDialogShow = () => {
    document.body.classList.add("p-overflow-hidden");
};

const onDialogHide = () => {
    document.body.classList.remove("p-overflow-hidden");
};

const filteredShipments = computed(() => {
    if (!form.cargo_type || !props.shipments) return [];

    return Object.values(props.shipments).filter((shipment) => {
        return shipment.cargo_type === form.cargo_type;
    });
});

watch(
    () => form.cargo_type,
    (newVal) => {
        form.shipment = null;
    }
);
</script>

<template>
    <AppLayout title="HBL Create">
        <template #header>HBL - Create</template>

        <!-- Breadcrumb -->
        <Breadcrumb />

        <!-- Bulk Creation Progress Banner -->
        <Message v-if="hblsCreatedCount > 0" class="my-3" severity="success">
            <div class="flex justify-between items-center w-full">
                <span
                    ><i class="pi pi-check-circle mr-2"></i
                    >{{ hblsCreatedCount }} HBL(s) created in this session</span
                >
                <Button
                    label="Finish & View All"
                    severity="success"
                    size="small"
                    @click="router.visit(route('third-party-shipments.index'))"
                />
            </div>
        </Message>

        <Stepper v-model:value="activeStep" class="my-4">
            <StepList>
                <Step value="0">Common Details</Step>
                <Step value="1">HBL Details</Step>
            </StepList>

            <StepPanels>
                <!-- Step 1: Common Fields -->
                <StepPanel value="0">
                    <form @submit.prevent="proceedToStep2">
                        <div class="grid grid-cols-1 sm:grid-cols-3 my-4 gap-4">
                            <div class="sm:col-span-3">
                                <Card>
                                    <template #title>
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-cog"></i>
                                            <span>Primary Details</span>
                                        </div>
                                    </template>
                                    <template #subtitle
                                        >Select the common details that will be
                                        used for all HBLs in this bulk
                                        creation.</template
                                    >
                                    <template #content>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-3"
                                        >
                                            <div>
                                                <InputLabel value="Agent" />
                                                <Select
                                                    v-model="commonFields.agent"
                                                    :option-label="
                                                        (option) => option.name
                                                    "
                                                    :options="agents"
                                                    :required="true"
                                                    class="w-full mt-1"
                                                    filter
                                                    option-value="id"
                                                    placeholder="Select Agent"
                                                />
                                            </div>

                                            <div>
                                                <InputLabel
                                                    value="Cargo Type"
                                                />
                                                <SelectButton
                                                    v-model="
                                                        commonFields.cargo_type
                                                    "
                                                    :options="cargoTypes"
                                                    class="mt-1 w-full"
                                                    name="Cargo Type"
                                                >
                                                    <template
                                                        #option="slotProps"
                                                    >
                                                        <div
                                                            class="flex items-center"
                                                        >
                                                            <i
                                                                v-if="
                                                                    slotProps.option ===
                                                                    'Sea Cargo'
                                                                "
                                                                class="ti ti-ship mr-2"
                                                            ></i>
                                                            <i
                                                                v-else
                                                                class="ti ti-plane mr-2"
                                                            ></i>
                                                            <span>{{
                                                                slotProps.option
                                                            }}</span>
                                                        </div>
                                                    </template>
                                                </SelectButton>
                                            </div>

                                            <div>
                                                <InputLabel value="Type" />
                                                <SelectButton
                                                    v-model="
                                                        commonFields.hbl_type
                                                    "
                                                    :options="hblTypes"
                                                    class="mt-1 w-full"
                                                    name="HBL Type"
                                                />
                                            </div>

                                            <div>
                                                <div
                                                    class="flex justify-between items-center mb-1"
                                                >
                                                    <InputLabel
                                                        value="Select Container"
                                                    />
                                                    <Button
                                                        icon="pi pi-plus"
                                                        label="New"
                                                        severity="help"
                                                        size="small"
                                                        type="button"
                                                        variant="text"
                                                        @click.prevent="
                                                            openCreateContainerDialog
                                                        "
                                                    />
                                                </div>
                                                <Select
                                                    v-model="
                                                        commonFields.shipment
                                                    "
                                                    :option-label="
                                                        (option) =>
                                                            option.reference
                                                    "
                                                    :options="
                                                        commonFields.cargo_type
                                                            ? Object.values(
                                                                  shipments
                                                              ).filter(
                                                                  (s) =>
                                                                      s.cargo_type ===
                                                                      commonFields.cargo_type
                                                              )
                                                            : []
                                                    "
                                                    :required="true"
                                                    class="w-full"
                                                    filter
                                                    option-value="id"
                                                    placeholder="Select Container"
                                                />
                                            </div>
                                            
                                            <div>
                                                <InputLabel value="Starting HBL Number" />
                                                <InputText
                                                    v-model="baseHBLNumber"
                                                    class="w-full mt-1"
                                                    placeholder="e.g., HBL001 or 100"
                                                    required
                                                />
                                                <small class="text-gray-500">Enter the starting HBL number. It will auto-increment for each HBL.</small>
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <Button
                                icon="pi pi-arrow-right"
                                iconPos="right"
                                label="Next: Add HBLs"
                                severity="info"
                                type="submit"
                            />
                        </div>
                    </form>
                </StepPanel>

                <!-- Step 2: HBL Details Form -->
                <StepPanel value="1">
                    <!-- Locked Common Fields Display -->
                    <Card class="mb-4">
                        <template #content>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="font-semibold text-sm"
                                    >Locked Fields:</span
                                >
                                <Tag severity="info" v-if="commonFields.agent">
                                    <i class="pi pi-lock mr-2"></i>
                                    Agent:
                                    {{
                                        agents.find(
                                            (a) => a.id === commonFields.agent
                                        )?.name
                                    }}
                                </Tag>
                                <Tag
                                    severity="info"
                                    v-if="commonFields.cargo_type"
                                >
                                    <i class="pi pi-lock mr-2"></i>
                                    {{ commonFields.cargo_type }}
                                </Tag>
                                <Tag
                                    severity="info"
                                    v-if="commonFields.hbl_type"
                                >
                                    <i class="pi pi-lock mr-2"></i>
                                    {{ commonFields.hbl_type }}
                                </Tag>
                                <Tag
                                    severity="info"
                                    v-if="commonFields.shipment"
                                >
                                    <i class="pi pi-lock mr-2"></i>
                                    Container:
                                    {{
                                        shipments.find(
                                            (s) =>
                                                s.id === commonFields.shipment
                                        )?.reference
                                    }}
                                </Tag>
                                <Button
                                    icon="pi pi-arrow-left"
                                    label="Change"
                                    severity="secondary"
                                    size="small"
                                    text
                                    @click="backToStep1"
                                />
                            </div>
                        </template>
                    </Card>

                    <form @submit.prevent="saveAndAddAnother">
                        <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">

                            <div class="sm:col-span-2">
                                <Card>
                                    <template #title>
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <span>Shipper Details</span>
                                        </div>
                                    </template>
                                    <template #content>
                                        <div
                                            class="grid grid-cols-3 gap-5 mt-3"
                                        >
                                            <div class="col-span-3">
                                                <InputLabel value="HBL Number" />
                                                <InputText
                                                    v-model="currentHBLNumber"
                                                    class="w-full"
                                                    required
                                                    @input="isHBLNumberManuallyOverridden = true"
                                                />
                                                <small class="text-gray-500">Auto-incremented. You can override manually if needed.</small>
                                            </div>
                                            <div class="col-span-3">
                                                <InputLabel value="Name" />
                                                <IconField>
                                                    <InputIcon
                                                        class="pi pi-user"
                                                    />
                                                    <InputText
                                                        v-model="form.hbl_name"
                                                        class="w-full"
                                                        placeholder="Name"
                                                    />
                                                </IconField>
                                                <InputError
                                                    :message="
                                                        form.errors.hbl_name
                                                    "
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel value="Email" />
                                                <IconField>
                                                    <InputIcon
                                                        class="pi pi-envelope"
                                                    />
                                                    <InputText
                                                        v-model="form.email"
                                                        class="w-full"
                                                        placeholder="Email"
                                                        type="email"
                                                    />
                                                </IconField>
                                                <InputError
                                                    :message="form.errors.email"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="Mobile Number"
                                                />
                                                <div class="flex flex-row">
                                                    <Select
                                                        v-model="countryCode"
                                                        :options="countryCodes"
                                                        class="w-25 !rounded-r-none !border-r-0"
                                                        filter
                                                        placeholder="Select a Country Code"
                                                    />
                                                    <InputText
                                                        v-model="contactNumber"
                                                        class="!rounded-l-none w-full"
                                                        placeholder="123 4567 890"
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .contact_number
                                                    "
                                                    class="col-span-1"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Checkbox
                                                        v-model="
                                                            isSameContactNumber
                                                        "
                                                        binary
                                                        inputId="whatsapp"
                                                        @change="
                                                            addContactToWhatsapp
                                                        "
                                                    />
                                                    <label for="whatsapp">
                                                        Use mobile number as
                                                        whatsapp number</label
                                                    >
                                                </div>
                                            </div>

                                            <div
                                                v-if="!isSameContactNumber"
                                                class="col-span-3"
                                            >
                                                <InputLabel
                                                    value="Whatsapp Number"
                                                />
                                                <div class="flex flex-row">
                                                    <Select
                                                        v-model="
                                                            whatsappNumberCountryCode
                                                        "
                                                        :options="countryCodes"
                                                        class="w-25 !rounded-r-none !border-r-0"
                                                        filter
                                                        placeholder="Select a Country Code"
                                                    />
                                                    <InputText
                                                        v-model="whatsappNumber"
                                                        class="!rounded-l-none w-full"
                                                        placeholder="123 4567 890"
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .whatsapp_number
                                                    "
                                                    class="col-span-1"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="Additional Mobile Number"
                                                />
                                                <div class="flex flex-row">
                                                    <Select
                                                        v-model="
                                                            additionalMobileCountryCode
                                                        "
                                                        :options="countryCodes"
                                                        class="w-25 !rounded-r-none !border-r-0"
                                                        filter
                                                        placeholder="Select a Country Code"
                                                    />
                                                    <InputText
                                                        v-model="
                                                            additionalMobileNumber
                                                        "
                                                        class="!rounded-l-none w-full"
                                                        placeholder="123 4567 890"
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .additional_mobile_number
                                                    "
                                                    class="col-span-1"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="PP or NIC No"
                                                />
                                                <IconField>
                                                    <InputIcon
                                                        class="pi pi-tag"
                                                    />
                                                    <InputText
                                                        v-model="form.nic"
                                                        class="w-full"
                                                        placeholder="PP or NIC No"
                                                    />
                                                </IconField>
                                                <InputError
                                                    :message="form.errors.nic"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="Residency No"
                                                />
                                                <IconField>
                                                    <InputIcon
                                                        class="pi pi-home"
                                                    />
                                                    <InputText
                                                        v-model="form.iq_number"
                                                        class="w-full"
                                                        placeholder="Residency No"
                                                    />
                                                </IconField>
                                                <InputError
                                                    :message="
                                                        form.errors.iq_number
                                                    "
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel value="Address" />
                                                <Textarea
                                                    v-model="form.address"
                                                    class="w-full"
                                                    cols="30"
                                                    placeholder="Type address here..."
                                                    rows="5"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors.address
                                                    "
                                                />
                                            </div>

                                            <div
                                                v-if="
                                                    form.hbl_type ===
                                                    'Door to Door'
                                                "
                                                class="col-span-3"
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Checkbox
                                                        v-model="isChecked"
                                                        binary
                                                        inputId="consignee-same"
                                                        @change="
                                                            addToConsigneeDetails
                                                        "
                                                    />
                                                    <label for="consignee-same"
                                                        >Same as Consignee
                                                        Details</label
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>

                            <div class="sm:col-span-2 grid grid-rows">
                                <Card>
                                    <template #title>
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <span>Consignee Details</span>
                                            <div class="flex space-x-1">
                                                <Button
                                                    v-if="form.hbl_name"
                                                    aria-label="Copy Shipper"
                                                    icon="pi pi-clone"
                                                    rounded
                                                    size="large"
                                                    variant="text"
                                                    x-tooltip.placement.bottom="'Copy Shipper'"
                                                    @click.prevent="
                                                        handleCopyShipper
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </template>
                                    <template #content>
                                        <div
                                            class="grid grid-cols-3 gap-5 mt-3"
                                        >
                                            <div class="col-span-3">
                                                <InputLabel value="Name" />
                                                <IconField>
                                                    <InputIcon
                                                        class="pi pi-user"
                                                    />
                                                    <InputText
                                                        v-model="
                                                            form.consignee_name
                                                        "
                                                        class="w-full"
                                                        placeholder="Name"
                                                    />
                                                </IconField>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_name
                                                    "
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="PP or NIC No"
                                                />
                                                <IconField>
                                                    <InputIcon
                                                        class="pi pi-tag"
                                                    />
                                                    <InputText
                                                        v-model="
                                                            form.consignee_nic
                                                        "
                                                        class="w-full"
                                                        placeholder="PP or NIC No"
                                                    />
                                                </IconField>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_nic
                                                    "
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="Mobile Number"
                                                />
                                                <div class="flex flex-row">
                                                    <Select
                                                        v-model="
                                                            consignee_countryCode
                                                        "
                                                        :options="countryCodes"
                                                        class="w-25 !rounded-r-none !border-r-0"
                                                        filter
                                                        placeholder="Select a Country Code"
                                                    />
                                                    <InputText
                                                        v-model="
                                                            consignee_contact
                                                        "
                                                        class="!rounded-l-none w-full"
                                                        placeholder="123 4567 890"
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_contact
                                                    "
                                                    class="col-span-1"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Checkbox
                                                        v-model="
                                                            isSameConsigneeContactNumber
                                                        "
                                                        binary
                                                        inputId="consignee-whatsapp"
                                                        @change="
                                                            addConsigneeContactToWhatsapp
                                                        "
                                                    />
                                                    <label
                                                        for="consignee-whatsapp"
                                                        >Use mobile number as
                                                        whatsapp number</label
                                                    >
                                                </div>
                                            </div>

                                            <div
                                                v-if="
                                                    !isSameConsigneeContactNumber
                                                "
                                                class="col-span-3"
                                            >
                                                <InputLabel
                                                    value="Whatsapp Number"
                                                />
                                                <div class="flex flex-row">
                                                    <Select
                                                        v-model="
                                                            consigneeWhatsappNumberCountryCode
                                                        "
                                                        :options="countryCodes"
                                                        class="w-25 !rounded-r-none !border-r-0"
                                                        filter
                                                        placeholder="Select a Country Code"
                                                    />
                                                    <InputText
                                                        v-model="
                                                            consigneeWhatsappNumber
                                                        "
                                                        class="!rounded-l-none w-full"
                                                        placeholder="123 4567 890"
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_whatsapp_number
                                                    "
                                                    class="col-span-1"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel
                                                    value="Additional Mobile Number"
                                                />
                                                <div class="flex flex-row">
                                                    <Select
                                                        v-model="
                                                            consigneeAdditionalMobileCountryCode
                                                        "
                                                        :options="countryCodes"
                                                        class="w-25 !rounded-r-none !border-r-0"
                                                        filter
                                                        placeholder="Select a Country Code"
                                                    />
                                                    <InputText
                                                        v-model="
                                                            consigneeAdditionalMobileNumber
                                                        "
                                                        class="!rounded-l-none w-full"
                                                        placeholder="123 4567 890"
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_additional_mobile_number
                                                    "
                                                    class="col-span-1"
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel value="Address" />
                                                <Textarea
                                                    v-model="
                                                        form.consignee_address
                                                    "
                                                    class="w-full"
                                                    cols="30"
                                                    placeholder="Type address here..."
                                                    rows="5"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_address
                                                    "
                                                />
                                            </div>

                                            <div class="col-span-3">
                                                <InputLabel value="Note" />
                                                <Textarea
                                                    v-model="
                                                        form.consignee_note
                                                    "
                                                    class="w-full"
                                                    cols="30"
                                                    placeholder="Type note here..."
                                                    rows="3"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .consignee_note
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
                            <div class="sm:col-span-12">
                                <Card>
                                    <template #title>
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <span>Package Details</span>
                                            </div>
                                            <Button
                                                v-if="
                                                    Object.values(
                                                        copiedPackages
                                                    ).length === 0
                                                "
                                                icon="pi pi-plus"
                                                label="New Package"
                                                severity="help"
                                                type="button"
                                                variant="outlined"
                                                @click="showPackageDialog"
                                            />
                                        </div>
                                    </template>
                                    <template #content>
                                        <DataTable
                                            v-if="packageList.length > 0"
                                            :value="packageList"
                                            tableStyle="min-width: 50rem"
                                        >
                                            <Column header="Actions">
                                                <template #body="slotProps">
                                                    <Button
                                                        icon="pi pi-times"
                                                        rounded
                                                        severity="danger"
                                                        size="small"
                                                        variant="text"
                                                        @click.prevent="
                                                            confirmRemovePackage(
                                                                slotProps.index
                                                            )
                                                        "
                                                    />

                                                    <Button
                                                        icon="pi pi-pencil"
                                                        rounded
                                                        size="small"
                                                        variant="text"
                                                        @click.prevent="
                                                            openEditModal(
                                                                slotProps.index
                                                            )
                                                        "
                                                    />
                                                </template>
                                            </Column>
                                            <Column
                                                field="type"
                                                header="Type"
                                            ></Column>
                                            <Column
                                                field="length"
                                                header="Length (CM)"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.length.toFixed(
                                                            3
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="width"
                                                header="Width"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.width.toFixed(
                                                            3
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="height"
                                                header="Height"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.height.toFixed(
                                                            3
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="quantity"
                                                header="Quantity"
                                            ></Column>
                                            <Column
                                                field="totalWeight"
                                                header="Actual Weight"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.totalWeight.toFixed(
                                                            3
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                v-if="
                                                    form.cargo_type ===
                                                    'Air Cargo'
                                                "
                                                field="volumetricWeight"
                                                header="Volumetric Weight"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.volumetricWeight.toFixed(
                                                            3
                                                        )
                                                    }}
                                                    kg
                                                </template>
                                            </Column>
                                            <Column
                                                field="volume"
                                                header="Volume (M.CU)"
                                            ></Column>
                                            <Column
                                                field="remarks"
                                                header="Remark"
                                            ></Column>
                                        </DataTable>

                                        <DataTable
                                            v-if="
                                                Object.keys(copiedPackages)
                                                    .length > 0
                                            "
                                            :value="copiedPackages"
                                            tableStyle="min-width: 50rem"
                                        >
                                            <Column
                                                field="package_type"
                                                header="Type"
                                            ></Column>
                                            <Column
                                                field="length"
                                                header="Length (CM)"
                                            ></Column>
                                            <Column
                                                field="width"
                                                header="Width"
                                            ></Column>
                                            <Column
                                                field="height"
                                                header="Height"
                                            ></Column>
                                            <Column
                                                field="quantity"
                                                header="Quantity"
                                            ></Column>
                                            <Column
                                                field="weight"
                                                header="Weight"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.weight.toFixed(
                                                            3
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="volume"
                                                header="Volume (M.CU)"
                                            >
                                                <template #body="slotProps">
                                                    {{
                                                        slotProps.data.volume.toFixed(
                                                            3
                                                        )
                                                    }}
                                                </template>
                                            </Column>
                                            <Column
                                                field="remarks"
                                                header="Remark"
                                            ></Column>
                                        </DataTable>

                                        <div
                                            v-if="
                                                packageList.length === 0 &&
                                                Object.values(copiedPackages)
                                                    .length === 0
                                            "
                                            class="text-center"
                                        >
                                            <div class="text-center mb-4">
                                                <i
                                                    class="pi pi-box text-purple-300 animate-slow-bounce"
                                                    style="font-size: 8rem"
                                                ></i>
                                                <p class="text-gray-600">
                                                    No packages. Please add
                                                    packages to view data.
                                                </p>
                                            </div>
                                            <Button
                                                v-if="
                                                    Object.values(
                                                        copiedPackages
                                                    ).length === 0
                                                "
                                                icon="pi pi-plus"
                                                label="New Package"
                                                severity="help"
                                                type="button"
                                                variant="outlined"
                                                @click="showPackageDialog"
                                            />
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-6 my-6 gap-4">
                            <!-- Empty grid columns for spacing -->
                            <div class="col-span-4"></div>

                            <!-- Action Buttons for Bulk Creation -->
                            <div
                                class="flex justify-between space-x-5 col-span-6 mt-6"
                            >
                                <Button
                                    icon="pi pi-arrow-left"
                                    label="Back to Common Fields"
                                    severity="secondary"
                                    variant="outlined"
                                    @click="backToStep1"
                                />

                                <div class="flex gap-3">
                                    <Button
                                        :class="{
                                            'opacity-50': form.processing,
                                        }"
                                        :disabled="form.processing"
                                        icon="pi pi-plus"
                                        iconPos="right"
                                        label="Save & Add Another"
                                        severity="success"
                                        variant="outlined"
                                        type="submit"
                                    />
                                    <Button
                                        :class="{
                                            'opacity-50': form.processing,
                                        }"
                                        :disabled="form.processing"
                                        icon="pi pi-check"
                                        iconPos="right"
                                        label="Save & Finish"
                                        severity="info"
                                        @click.prevent="saveAndFinish"
                                    />
                                </div>
                            </div>
                        </div>
                    </form>
                </StepPanel>
            </StepPanels>
        </Stepper>

        <HBLDetailModal
            :hbl-id="hblId"
            :show="showConfirmViewHBLModal"
            @close="closeViewModal"
            @update:show="showConfirmViewHBLModal = $event"
        />

        <Dialog
            v-model:visible="showAddNewPackageDialog"
            :header="editMode ? `Edit Package` : `Add New Package`"
            :style="{ width: '60rem' }"
            block-scroll
            maximizable
            modal
            position="bottom"
            @hide="onDialogHide"
            @show="onDialogShow"
        >
            <span class="text-surface-500 dark:text-surface-400 block mb-4">{{
                !editMode ? "Add new package to HBL" : ""
            }}</span>

            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-4 md:col-span-1">
                    <InputLabel value="Type" />
                    <Select
                        v-model="selectedType"
                        :options="packageTypes"
                        class="w-full"
                        filter
                        option-label="name"
                        option-value="name"
                        placeholder="Choose One"
                        @change="updateTypeDescription"
                    />
                </div>

                <div class="col-span-4 md:col-span-2">
                    <InputLabel>
                        Type Description
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputText
                        v-model="packageItem.type"
                        class="w-full"
                        placeholder="Sofa set"
                    />
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Measure Type
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <Select
                        v-model="packageItem.measure_type"
                        :options="measureTypes"
                        class="w-full"
                        placeholder="Choose One"
                    />
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Length
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber
                        v-model="packageItem.length"
                        :maxFractionDigits="5"
                        :minFractionDigits="2"
                        class="w-full"
                        min="0.00"
                        placeholder="1.00"
                        step="0.01"
                    />
                    <Message severity="secondary" size="small" variant="simple"
                        >{{ packageItemLength.toFixed(2) }} cm</Message
                    >
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Width
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber
                        v-model="packageItem.width"
                        :maxFractionDigits="5"
                        :minFractionDigits="2"
                        class="w-full"
                        min="0.00"
                        placeholder="1.00"
                        step="0.01"
                    />
                    <Message severity="secondary" size="small" variant="simple"
                        >{{ packageItemWidth.toFixed(2) }} cm</Message
                    >
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Height
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber
                        v-model="packageItem.height"
                        :maxFractionDigits="5"
                        :minFractionDigits="2"
                        class="w-full"
                        min="0.00"
                        placeholder="1.00"
                        step="0.01"
                    />
                    <Message severity="secondary" size="small" variant="simple"
                        >{{ packageItemHeight.toFixed(2) }} cm</Message
                    >
                </div>

                <div class="col-span-4 md:col-span-1">
                    <InputLabel>
                        Quantity
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber
                        v-model="packageItem.quantity"
                        class="w-full"
                        min="0"
                        placeholder="1"
                        step="1"
                    />
                </div>

                <div class="col-span-2">
                    <InputLabel>
                        Volume ({{ volumeUnit }})
                        <span class="text-red-500 text-sm">*</span>
                    </InputLabel>
                    <InputNumber
                        v-model="packageItem.volume"
                        :maxFractionDigits="5"
                        :minFractionDigits="2"
                        class="w-full"
                        placeholder="1.00"
                        step="0.001"
                    />
                    <Message severity="secondary" size="small" variant="simple"
                        >{{ packageItemVolume }} M.CU</Message
                    >
                </div>

                <div class="col-span-2">
                    <InputLabel value="Total Weight" />
                    <InputNumber
                        v-model="packageItem.totalWeight"
                        :maxFractionDigits="5"
                        :minFractionDigits="2"
                        class="w-full"
                        min="0"
                        placeholder="1"
                        step="1"
                    />
                    <Message
                        v-if="form.cargo_type === 'Air Cargo'"
                        severity="secondary"
                        size="small"
                        variant="simple"
                        >Volumetric Weight
                        {{ packageItem.volumetricWeight.toFixed(3) }}
                        kg</Message
                    >
                </div>

                <div class="col-span-4">
                    <InputLabel value="Remarks" />
                    <Textarea
                        v-model="packageItem.remarks"
                        class="w-full"
                        cols="30"
                        placeholder="Type Remarks..."
                        rows="4"
                    />
                </div>
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    severity="secondary"
                    text
                    @click="closeAddPackageModal"
                />
                <Button
                    :label="editMode ? `Edit Package` : `Add Package`"
                    severity="help"
                    @click="addPackageData"
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="copyFromHBLToShipperModalShow"
            :style="{ width: '25rem' }"
            header="Copy From HBL"
            modal
        >
            <div class="flex items-center gap-4 mb-4">
                <label class="font-semibold w-24" for="reference"
                    >HBL Reference</label
                >
                <InputText
                    id="reference"
                    v-model="reference"
                    autocomplete="off"
                    class="flex-auto"
                    placeholder="Enter HBL Reference"
                    required="true"
                />
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    type="button"
                    @click="closeCopyFromHBLToShipperModal"
                ></Button>
                <Button
                    label="Copy"
                    type="button"
                    @click.prevent="handleCopyFromHBLToShipper"
                ></Button>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="copyFromHBLToConsigneeModalShow"
            :style="{ width: '25rem' }"
            header="Copy From HBL"
            modal
        >
            <div class="flex items-center gap-4 mb-4">
                <label class="font-semibold w-24" for="reference"
                    >HBL Reference</label
                >
                <InputText
                    id="reference"
                    v-model="reference"
                    autocomplete="off"
                    class="flex-auto"
                    placeholder="Enter HBL Reference"
                    required="true"
                />
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    type="button"
                    @click="closeCopyFromHBLToConsigneeModal"
                ></Button>
                <Button
                    label="Copy"
                    type="button"
                    @click.prevent="handleCopyFromHBLToConsignee"
                ></Button>
            </div>
        </Dialog>

        <Dialog
            v-model:visible="copyFromHBLToPackageModalShow"
            :style="{ width: '25rem' }"
            header="Copy From HBL"
            modal
        >
            <div class="flex items-center gap-4 mb-4">
                <label class="font-semibold w-24" for="reference"
                    >HBL Reference</label
                >
                <InputText
                    id="reference"
                    v-model="reference"
                    autocomplete="off"
                    class="flex-auto"
                    placeholder="Enter HBL Reference"
                    required="true"
                />
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancel"
                    severity="secondary"
                    type="button"
                    @click="closeCopyFromHBLToPackageModal"
                ></Button>
                <Button
                    label="Copy"
                    severity="help"
                    type="button"
                    @click.prevent="handleCopyFromHBLToPackage"
                ></Button>
            </div>
        </Dialog>

        <!-- Container Creation Modal -->
        <Dialog
            v-model:visible="showCreateContainerDialog"
            :style="{ width: '70rem' }"
            block-scroll
            header="Create New Container"
            maximizable
            modal
            position="center"
            @hide="onDialogHide"
            @show="onDialogShow"
        >
            <div class="grid grid-cols-1 gap-4">
                <Card>
                    <template #title>Cargo Type</template>
                    <template #content>
                        <SelectButton
                            v-model="containerForm.cargo_type"
                            :options="cargoTypes"
                            name="Cargo Type"
                        >
                            <template #option="slotProps">
                                <div class="flex items-center">
                                    <i
                                        v-if="slotProps.option === 'Sea Cargo'"
                                        class="ti ti-ship mr-2"
                                    ></i>
                                    <i v-else class="ti ti-plane mr-2"></i>
                                    <span>{{ slotProps.option }}</span>
                                </div>
                            </template>
                        </SelectButton>
                        <InputError
                            :message="containerForm.errors.cargo_type"
                        />
                    </template>
                </Card>

                <Card>
                    <template #title>Target Warehouse</template>
                    <template #content>
                        <SelectButton
                            v-model="containerForm.target_warehouse"
                            :options="warehouses"
                            name="target_warehouse"
                            option-label="name"
                            option-value="id"
                        />
                        <InputError
                            :message="containerForm.errors.target_warehouse"
                        />
                    </template>
                </Card>

                <Card>
                    <template #title>Container Specs</template>
                    <template #content>
                        <SelectButton
                            v-model="containerForm.container_type"
                            :disabled="containerForm.cargo_type === 'Air Cargo'"
                            :options="containerTypesForModal"
                            name="container_type"
                        />
                        <InputError
                            :message="containerForm.errors.container_type"
                        />
                    </template>
                </Card>

                <Card>
                    <template #title>Container Details</template>
                    <template #content>
                        <div class="grid grid-cols-4 gap-5 mt-3">
                            <div class="col-span-2">
                                <InputLabel value="Reference" />
                                <InputText
                                    v-model="containerForm.reference"
                                    class="w-full"
                                    placeholder="Enter Container Reference"
                                />
                                <InputError
                                    :message="containerForm.errors.reference"
                                />
                            </div>

                            <div
                                v-if="containerForm.cargo_type === 'Sea Cargo'"
                                class="col-span-2"
                            >
                                <InputLabel value="Container Number" />
                                <InputText
                                    v-model="containerForm.container_number"
                                    class="w-full"
                                    placeholder="Enter Container Number"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.container_number
                                    "
                                />
                            </div>

                            <div
                                v-if="containerForm.cargo_type === 'Sea Cargo'"
                                class="col-span-2"
                            >
                                <InputLabel value="Seal Number" />
                                <InputText
                                    v-model="containerForm.seal_number"
                                    class="w-full"
                                    placeholder="Enter Seal Number"
                                />
                                <InputError
                                    :message="containerForm.errors.seal_number"
                                />
                            </div>

                            <div
                                v-if="containerForm.cargo_type === 'Sea Cargo'"
                                class="col-span-2"
                            >
                                <InputLabel value="BL Number" />
                                <InputText
                                    v-model="containerForm.bl_number"
                                    class="w-full"
                                    placeholder="Enter BL Number"
                                />
                                <InputError
                                    :message="containerForm.errors.bl_number"
                                />
                            </div>

                            <div v-else class="col-span-2">
                                <InputLabel value="AWB Number" />
                                <InputText
                                    v-model="containerForm.awb_number"
                                    class="w-full"
                                    placeholder="Enter AWB Number"
                                />
                                <InputError
                                    :message="containerForm.errors.awb_number"
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Estimated Departure Date" />
                                <DatePicker
                                    v-model="
                                        containerForm.estimated_time_of_departure
                                    "
                                    class="w-full mt-1"
                                    date-format="yy-mm-dd"
                                    icon-display="input"
                                    placeholder="Set Estimated Departure Date"
                                    show-icon
                                />
                                <InputError
                                    :message="
                                        containerForm.errors
                                            .estimated_time_of_departure
                                    "
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Estimated Arrival Date" />
                                <DatePicker
                                    v-model="
                                        containerForm.estimated_time_of_arrival
                                    "
                                    class="w-full mt-1"
                                    date-format="yy-mm-dd"
                                    icon-display="input"
                                    placeholder="Set Estimated Arrival Date"
                                    show-icon
                                />
                                <InputError
                                    :message="
                                        containerForm.errors
                                            .estimated_time_of_arrival
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card v-if="containerForm.cargo_type === 'Sea Cargo'">
                    <template #title>Vessel Details</template>
                    <template #content>
                        <div class="grid grid-cols-4 gap-5 mt-3">
                            <div class="col-span-2">
                                <InputLabel value="Vessel Name" />
                                <InputText
                                    v-model="containerForm.vessel_name"
                                    class="w-full"
                                    placeholder="Enter Vessel Name"
                                />
                                <InputError
                                    :message="containerForm.errors.vessel_name"
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Voyage Number" />
                                <InputText
                                    v-model="containerForm.voyage_number"
                                    class="w-full"
                                    placeholder="Enter Voyage Number"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.voyage_number
                                    "
                                />
                            </div>

                            <div class="col-span-4">
                                <InputLabel value="Shipping Line" />
                                <InputText
                                    v-model="containerForm.shipping_line"
                                    class="w-full"
                                    placeholder="Enter Shipping Line"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.shipping_line
                                    "
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Port of Loading" />
                                <InputText
                                    v-model="containerForm.port_of_loading"
                                    class="w-full"
                                    placeholder="Enter Port of Loading"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.port_of_loading
                                    "
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Port of Discharge" />
                                <InputText
                                    v-model="containerForm.port_of_discharge"
                                    class="w-full"
                                    placeholder="Enter Port of Discharge"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.port_of_discharge
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card v-else>
                    <template #title>Flight Details</template>
                    <template #content>
                        <div class="grid grid-cols-4 gap-5 mt-3">
                            <div class="col-span-1">
                                <InputLabel value="Flight Number" />
                                <InputText
                                    v-model="containerForm.flight_number"
                                    class="w-full"
                                    placeholder="Enter Flight Number"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.flight_number
                                    "
                                />
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Airline Name" />
                                <Select
                                    v-model="containerForm.airline_name"
                                    :options="airLinesList"
                                    class="w-full"
                                    filter
                                    option-label="name"
                                    option-value="name"
                                    placeholder="Select Air Line"
                                />
                                <InputError
                                    :message="containerForm.errors.airline_name"
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Airport of Departure" />
                                <InputText
                                    v-model="containerForm.airport_of_departure"
                                    class="w-full"
                                    placeholder="Enter Airport of Departure"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors
                                            .airport_of_departure
                                    "
                                />
                            </div>

                            <div class="col-span-2">
                                <InputLabel value="Airport of Arrival" />
                                <InputText
                                    v-model="containerForm.airport_of_arrival"
                                    class="w-full"
                                    placeholder="Enter Airport of Arrival"
                                />
                                <InputError
                                    :message="
                                        containerForm.errors.airport_of_arrival
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    severity="secondary"
                    text
                    @click="closeCreateContainerDialog"
                />
                <Button
                    :class="{ 'opacity-50': containerForm.processing }"
                    :disabled="containerForm.processing"
                    label="Create Container"
                    severity="help"
                    @click="handleCreateContainer"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
