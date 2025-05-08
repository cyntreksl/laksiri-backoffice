<script setup>
import {router, useForm, usePage} from "@inertiajs/vue3";
import {computed, onBeforeMount, reactive, ref, watch, onMounted} from "vue";
import InputError from "@/Components/InputError.vue";
import {push} from "notivue";
import InputLabel from "@/Components/InputLabel.vue";
import hblImage from "../../../../images/illustrations/hblimage.png";
import {useConfirm} from "primevue/useconfirm";
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import SelectButton from 'primevue/selectbutton';
import Button from 'primevue/button';
import Fieldset from 'primevue/fieldset';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import IftaLabel from 'primevue/iftalabel';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import ToggleButton from 'primevue/togglebutton';

const props = defineProps({
    hbl: {
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
    warehouses: {
        type: Object,
        default: () => {
        },
    },
    priceRules: {
        type: Object,
        default: () => {
        },
    },
    packageTypes: {
        type: Array,
        default: () => [],
    },
    countryCodes: {
        type: Array,
        default: () => [],
    },
});

const errors = ref([]);
const packageItemVolume = ref(0);
const measureTypes = ref(['cm', 'm', 'in', 'ft']);
const confirm = useConfirm();
const volumeUnit = computed(() => {
    const units = {
        cm: 'CM.CU',
        m: 'M.CU',
        in: 'IN.CU',
        ft: 'FT.CU',
    };
    return units[packageItem.measure_type] || 'M.CM';
});


const splitCountryCode = (fullNumber) => {
    if (fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return code;
            }
        }
    }
    return '+94';
};

const splitContactNumber = (fullNumber) => {
    if(fullNumber) {
        for (let code of props.countryCodes) {
            if (fullNumber.startsWith(code)) {
                return fullNumber.slice(code.length);
            }
        }
    } else return "";
}

const isSameContactNumber = ref(props.hbl.contact_number === props.hbl.whatsapp_number);
const isSameConsigneeContactNumber = ref(props.hbl.consignee_contact === props.hbl.consignee_whatsapp_number);

const countryCode = ref(splitCountryCode(props.hbl.contact_number));
const contactNumber = ref(splitContactNumber(props.hbl.contact_number));
const consigneeCountryCode = ref(splitCountryCode(props.hbl.consignee_contact));
const consigneeContactNumber = ref(splitContactNumber(props.hbl.consignee_contact));

const additionalMobileCountryCode = ref(splitCountryCode(props.hbl.additional_mobile_number));
const additionalMobileNumber = ref(splitContactNumber(props.hbl.additional_mobile_number));

const whatsappNumberCountryCode = ref(splitCountryCode(props.hbl.whatsapp_number));
const whatsappNumber = ref(splitContactNumber(props.hbl.whatsapp_number));

const consigneeAdditionalMobileCountryCode = ref(splitCountryCode(props.hbl.consignee_additional_mobile_number));
const consigneeAdditionalMobileNumber = ref(splitContactNumber(props.hbl.consignee_additional_mobile_number));

const consigneeWhatsappNumberCountryCode = ref(splitCountryCode(props.hbl.consignee_whatsapp_number));
const consigneeWhatsappNumber = ref(splitContactNumber(props.hbl.consignee_whatsapp_number));

const form = useForm({
    hbl_name: props.hbl.hbl_name,
    email: props.hbl.email,
    contact_number: computed(() => countryCode.value + contactNumber.value),
    additional_mobile_number: props.hbl.additional_mobile_number || "",
    whatsapp_number: props.hbl.whatsapp_number || "",
    nic: props.hbl.nic,
    iq_number: props.hbl.iq_number,
    address: props.hbl.address,
    consignee_name: props.hbl.consignee_name,
    consignee_nic: props.hbl.consignee_nic,
    consignee_contact: computed(
        () => consigneeCountryCode.value + consigneeContactNumber.value
    ),
    consignee_additional_mobile_number: props.hbl.consignee_additional_mobile_number || "",
    consignee_whatsapp_number: props.hbl.consignee_whatsapp_number || "",
    consignee_address: props.hbl.consignee_address,
    consignee_note: props.hbl.consignee_note,
    cargo_type: props.hbl.cargo_type,
    hbl_type: props.hbl.hbl_type,
    warehouse: props.hbl.warehouse
        ? props.hbl.warehouse.charAt(0).toUpperCase() + props.hbl.warehouse.slice(1).toLowerCase()
        : '',
    warehouse_id: props.hbl.warehouse_id,
    freight_charge: props.hbl.freight_charge.toFixed(2),
    bill_charge: props.hbl.bill_charge.toFixed(2),
    other_charge: props.hbl.other_charge.toFixed(2),
    destination_charge: props.hbl.destination_charge.toFixed(2),
    discount: props.hbl.discount.toFixed(2),
    paid_amount: props.hbl.paid_amount.toFixed(2),
    grand_total: props.hbl.grand_total.toFixed(2),
    packages: props.hbl.packages,
    is_active_package: !!props. hbl. packages?.[0]?.package_rule,
    additional_charge: props.hbl.additional_charge.toFixed(2),
    vat: 0,
    is_departure_charges_paid: props.hbl.is_departure_charges_paid === 1,
    is_destination_charges_paid: props.hbl.is_destination_charges_paid === 1,
});

onMounted(() => {
    calculatePayment();
    hblRules();
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
    whatsappNumberCountryCode.value = splitCountryCode(props.hbl.whatsapp_number)
    whatsappNumber.value = splitContactNumber(props.hbl.whatsapp_number)
};

const addConsigneeContactToWhatsapp = () => {
    if (isSameContactNumber.value) {
        consigneeWhatsappNumberCountryCode.value = consigneeCountryCode.value;
        consigneeWhatsappNumber.value = consigneeContactNumber.value;
    } else {
        resetConsigneeWhatsappNumber();
    }
};

const resetConsigneeWhatsappNumber = () => {
    consigneeWhatsappNumberCountryCode.value = splitCountryCode(props.hbl.consignee_whatsapp_number)
    consigneeWhatsappNumber.value = splitContactNumber(props.hbl.consignee_whatsapp_number);
};

const handleHBLUpdate = () => {
    form.additional_mobile_number = additionalMobileCountryCode.value + additionalMobileNumber.value;
    form.whatsapp_number = whatsappNumberCountryCode.value + whatsappNumber.value;
    form.consignee_additional_mobile_number = consigneeAdditionalMobileCountryCode.value + consigneeAdditionalMobileNumber.value;
    form.consignee_whatsapp_number = consigneeWhatsappNumberCountryCode.value + consigneeWhatsappNumber.value;
    if(form.additional_mobile_number === additionalMobileCountryCode.value){
        form.additional_mobile_number = "";
    }
    if(form.whatsapp_number === whatsappNumberCountryCode.value){
        form.whatsapp_number = "";
    }

    if(form.consignee_additional_mobile_number === additionalMobileCountryCode.value){
        form.consignee_additional_mobile_number = "";
    }
    if(form.consignee_whatsapp_number === consigneeWhatsappNumberCountryCode.value){
        form.consignee_whatsapp_number = "";
    }
    form.put(route("hbls.update", props.hbl.id), {
        onSuccess: () => {
            push.success("HBL Updated Successfully!");
            router.visit(route("hbls.index"));
        },
        onError: () => {
            console.log("error");
            errors.value = usePage().props.errors
        },
        onFinish: () => console.log("finish"),
        preserveScroll: true,
        preserveState: true,
    });
};

const resetModal = () => {
    packageItem.package_type = props.packageTypes.find(
        type => type.name.toLowerCase() === 'carton'.toLowerCase()
    )?.name || "";
    packageItem.length = 0;
    packageItem.width = 0;
    packageItem.height = 0;
    packageItem.quantity = 0;
    packageItem.volume = 0;
    packageItem.weight = 0;
    packageItem.remarks = "";
};

const showAddNewPackageDialog = ref(false);

const showPackageDialog = () => {
    showAddNewPackageDialog.value = true;
};

const packageList = ref(form.packages ?? []);

const packageItem = reactive({
    package_rule: 0,
    type: props.packageTypes.find(
        type => type.name.toLowerCase() === 'carton'.toLowerCase()
    )?.name || "",
    length: 0,
    width: 0,
    height: 0,
    quantity: 0,
    volume: 0,
    weight: 0,
    remarks: "",
    measure_type: "cm"
});

const packageItemLength = ref(0);
const packageItemWidth = ref(0);
const packageItemHeight = ref(0);

function convertMeasurementstocm(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return value * factor;
}

watch(
    () => packageItem.measure_type,
    (newMeasureType) => {
        packageItemLength.value = convertMeasurementstocm(newMeasureType, packageItem.length);
        packageItemWidth.value = convertMeasurementstocm(newMeasureType, packageItem.width);
        packageItemHeight.value = convertMeasurementstocm(newMeasureType, packageItem.height);
    }
);

watch(
    [() => packageItem.length],
    ([newLength]) => {
        packageItemLength.value = convertMeasurementstocm(packageItem.measure_type, newLength);
    }
);

watch(
    [() => packageItem.width],
    ([newWidth]) => {
        packageItemWidth.value = convertMeasurementstocm(packageItem.measure_type, newWidth);
    }
);

watch(
    [() => packageItem.height],
    ([newHeight]) => {
        packageItemHeight.value = convertMeasurementstocm(packageItem.measure_type, newHeight);
    }
);

watch(
    [
        () => packageItem.length,
        () => packageItem.width,
        () => packageItem.height,
        () => packageItem.quantity,
        () => packageItem.measure_type,
    ],
    ([newLength, newWidth, newHeight, newQuantity, newMeasureType]) => {
        // Convert dimensions from cm to meters
        const lengthMeters = newLength / 100; // 1 cm = 0.01 meters
        const widthMeters = newWidth / 100;
        const heightMeters = newHeight / 100;

        // Calculate volume in cubic meters (m³)
        const volumeCubicMeters =
            lengthMeters * widthMeters * heightMeters * newQuantity;


        // Update reactive properties
        packageItem.volume = (newLength*newWidth*newHeight*newQuantity).toFixed(3);
        if (packageItem.measure_type === 'cm') {
            // Convert cm³ to m³ by dividing by 1,000,000
            packageItemVolume.value = (packageItem.volume / 1000000).toFixed(3);
        } else if (packageItem.measure_type === 'in') {
            // Convert from inches to cubic centimeters (1 inch = 16.387 cm³)
            packageItemVolume.value = (packageItem.volume * 16.387 / 1000000).toFixed(3);  // Convert to m³
        } else if (packageItem.measure_type === 'ft') {
            // Convert from cubic feet to cubic meters (1 ft³ = 0.0283 m³)
            packageItemVolume.value = (packageItem.volume * 0.0283).toFixed(3);
        } else {
            // Assume volume is already in cubic meters if no unit conversion is needed
            packageItemVolume.value = packageItem.volume;
        }

        // packageItem.totalWeight = totalWeightKg;
    }
);

const grandTotalVolume = computed(() => {
    return form.packages.reduce((acc, pack) => {
        return parseFloat(acc) + parseFloat(pack.volume);
    }, 0);
});

const grandTotalWeight = computed(() => {
    return form.packages.reduce((acc, pack) => {
        return acc + pack.weight;
    }, 0);
});

const addPackageData = () => {
    if (
        !packageItem.type ||
        packageItem.length <= 0 ||
        packageItem.width <= 0 ||
        packageItem.height <= 0 ||
        packageItem.quantity <= 0 ||
        packageItem.volume <= 0 ||
        (form.is_active_package && !packageItem.package_rule)
    ) {
        push.error("Please fill all required data");
        return;
    }

    if (form.cargo_type === 'Air Cargo') {
        if (packageItem.totalWeight <= 0) {
            push.error("Please fill the total weight");
            return;
        }
    }

    if (editMode.value) {
        packageList.value.splice(editIndex.value, 1, {...packageItem});
        grandTotalWeight.value = packageList.value.reduce(
            (accumulator, currentValue) => accumulator + parseFloat(currentValue.totalWeight),
            0
        );
        grandTotalVolume.value = packageList.value.reduce(
            (accumulator, currentValue) => accumulator + parseFloat(currentValue.volume),
            0
        );

        calculatePayment();
    } else {
        const newItem = {...packageItem};
        newItem['package_type']=newItem.type;
        packageList.value.push(newItem);
        form.packages = packageList.value;

        const volume = parseFloat(newItem.volume) || 0;
        grandTotalWeight.value += parseFloat(newItem.totalWeight);
        grandTotalVolume.value = grandTotalVolume.value += parseFloat(volume.toFixed(3));
        calculatePayment();
    }
    closeAddPackageModal();
};

// Watch for changes in length, width, height, or quantity to update volume and totalWeight
watch(
    [
        () => packageItem.length,
        () => packageItem.width,
        () => packageItem.height,
        () => packageItem.quantity,
    ],
    ([newLength, newWidth, newHeight, newQuantity]) => {
        // Convert dimensions from cm to meters
        const lengthMeters = newLength / 100; // 1 cm = 0.01 meters
        const widthMeters = newWidth / 100;
        const heightMeters = newHeight / 100;

        // Calculate volume in cubic meters (m³)
        const volumeCubicMeters =
            lengthMeters * widthMeters * heightMeters * newQuantity;

        // Assuming weight is directly proportional to volume
        // Convert weight from grams to kilograms
        const totalWeightKg = (volumeCubicMeters * newQuantity) / 1000; // 1 gram = 0.001 kilograms

        // Update reactive properties
        packageItem.volume = volumeCubicMeters.toFixed(3);
        // packageItem.totalWeight = totalWeightKg;
    }
);

const vat = ref(0);

watch(
    [
        () => form.other_charge,
        () => form.discount,
        () => form.freight_charge,
        () => form.vat,
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
            parseFloat(form.vat) -
            form.discount +
            parseFloat(form.additional_charge);
        hblTotal.value = Number(hblTotal.value.toFixed(2))
        form.grand_total = hblTotal.value;
    }
);

watch([() => form.cargo_type], ([newCargoType]) => {
    calculatePayment();
});

const selectedType = ref("");

const updateTypeDescription = () => {
    packageItem.type = (packageItem.type ? " " : "") + selectedType.value;
};
const hblTotal = ref(0);
const currency = ref(usePage().props.currentBranch.currency_symbol || "SAR");
const isEditable = ref(false);

onBeforeMount(() => {
    const cargoType = props.hbl.cargo_type;

    if (cargoType === "Sea Cargo") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Sea Cargo"
            );
        });

        isEditable.value = Boolean(priceRule.value.is_editable);
    } else if (cargoType === "Air Cargo") {
        const priceRule = computed(() => {
            return props.priceRules.find(
                (priceRule) => priceRule.cargo_mode === "Air Cargo"
            );
        });

        isEditable.value = Boolean(priceRule.value.is_editable);
    }
});

const calculatePayment = async () => {
    try {
        const response = await fetch(`/hbls/calculate-payment`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({
                cargo_type: form.cargo_type,
                hbl_type: form.hbl_type,
                warehouse: form.warehouse,
                grand_total_volume: grandTotalVolume.value,
                grand_total_weight: grandTotalWeight.value,
                package_list_length: packageList.value.length,
                package_list: packageList.value,
                is_active_package: form.is_active_package,
            })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();

            form.freight_charge = data.freight_charge;
            form.bill_charge = data.bill_charge;
            form.other_charge = data.other_charge;
            form.package_charges = data.package_charges;
            form.destination_charge = data.destination_charges;
            isEditable.value = data.is_editable;
            vat.value = data.vat;
        }

    } catch (error) {
        console.log(error);
    }
}

const showConfirmRemovePackageModal = ref(false);
const packageIndex = ref(null);

// remove package
const confirmRemovePackage = (index) => {
    confirm.require({
        message: 'Would you like to remove this hbl package record?',
        header: 'Remove Package?',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Remove Package',
            severity: 'danger'
        },
        accept: () => {
            handleRemovePackage(index)
        },
        reject: () => {
        }
    });
};

const closeModal = () => {
    showConfirmRemovePackageModal.value = false;
};

const handleRemovePackage = (index) => {
    if (index !== null) {
        grandTotalVolume.value -= packageList.value[index].volume;
        grandTotalWeight.value -= packageList.value[index].totalWeight;
        packageList.value.splice(index, 1);
        calculatePayment();
    }
};

// edit package
const closeAddPackageModal = () => {
    resetModal();
    showAddNewPackageDialog.value = false;
    editIndex.value = null;
    editMode.value = false;
};

const editMode = ref(false);
const editIndex = ref(null);

const conversionFactors = {
    cm: 1,
    m: 100,
    in: 2.54,
    ft: 30.48,
};

const openEditModal = (index) => {
    editMode.value = true;
    editIndex.value = index;
    showAddNewPackageDialog.value = true;

    selectedType.value = packageList.value[index].package_type;
    // populate packageItem with existing data for editing
    Object.assign(packageItem, packageList.value[index]);
    packageItem.type = packageList.value[index].package_type;
    packageItem.length = convertMeasurements(packageItem.measure_type,packageItem.length).toFixed(2);
    packageItem.width = convertMeasurements(packageItem.measure_type,packageItem.width).toFixed(2);
    packageItem.height = convertMeasurements(packageItem.measure_type,packageItem.height).toFixed(2);
};
const isPackageRuleSelected = ref(form.is_active_package);
const packageRulesData = ref([]);
const selectedPackage = ref("");
const isExistsRules = ref(false);

const volumeMeasurements = {
    cm: 'cm.cu',
    m: 'm.cu',
    in: 'in.cu',
    ft: 'ft.cu',
};

function convertMeasurements(measureType, value) {
    const factor = conversionFactors[measureType] || 1;
    return value / factor;
}

function getPackageRuleTitle(title, length, width , height, measureType) {
    const volumeMeasurement = volumeMeasurements[measureType] || 'cm.cu';

    return title + ' (' + convertMeasurements(measureType,length).toFixed(2) + '*' + convertMeasurements(measureType,width).toFixed(2) + '*' + convertMeasurements(measureType,height).toFixed(2) + ')'+volumeMeasurement;
}

const hblRules = async () => {
    try {
        const response = await fetch(`/get-hbl-rules`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
                // "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({
                cargo_type: form.cargo_type,
                hbl_type: form.hbl_type,
                warehouse: form.warehouse,
            })
        });
        const data = await response.json();
        if ((!data.package_rules || data.package_rules.length === 0) &&
            (!data.price_rules || data.price_rules.length === 0)) {
            push.error('Please add price rules');
            isExistsRules.value = false;
        }else{
            isExistsRules.value = true;
        }
        if (data.package_rules) {
            packageRulesData.value = data.package_rules;
        }

    } catch (error) {
        console.log(error);
    }
};

watch([() => form.cargo_type], ([newCargoType]) => {
    calculatePayment();
    hblRules();
});

watch([() => form.hbl_type], ([newHBLType]) => {
    calculatePayment();
    hblRules();
});

watch([() => form.warehouse], ([newHBLType]) => {
    calculatePayment();
    hblRules();
});
const getSelectedPackage = () => {
    // Find the selected package from the packages array based on the selected ID
    const selectedRule = packageRulesData.value.find(pkg => pkg.id === packageItem.package_rule);
    // console.log(packageItem, selectedRule);
    if (selectedRule) {
        isPackageRuleSelected.value = true;
        packageItem.length = convertMeasurements(selectedRule.measure_type, selectedRule.length).toFixed(2);
        packageItem.width = convertMeasurements(selectedRule.measure_type, selectedRule.width).toFixed(2);
        packageItem.height = convertMeasurements(selectedRule.measure_type, selectedRule.height).toFixed(2);
        packageItem.measure_type = selectedRule.measure_type;
    }else {
        isPackageRuleSelected.value = false;
        packageItem.length = 0;
        packageItem.width = 0;
        packageItem.height = 0;
    }
};

const isChecked = ref(false);

const addToConsigneeDetails = () => {
    if (isChecked.value) {
        form.consignee_name = form.hbl_name;
        consigneeCountryCode.value = countryCode.value;
        consigneeContactNumber.value = contactNumber.value;
        form.consignee_nic = form.nic;
        form.consignee_address = form.address;
        isSameConsigneeContactNumber.value = isSameContactNumber.value;
        consigneeWhatsappNumberCountryCode.value = whatsappNumberCountryCode.value;
        consigneeWhatsappNumber.value = whatsappNumber.value;
        consigneeAdditionalMobileCountryCode.value = additionalMobileCountryCode.value;
        consigneeAdditionalMobileNumber.value = additionalMobileNumber.value;
    } else {
        resetConsigneeDetails();
    }
};

const resetConsigneeDetails = () => {
    form.consignee_name = props.hbl.hbl_name;
    consigneeCountryCode.value = splitCountryCode(props.hbl.consignee_contact);
    consigneeContactNumber.value = splitContactNumber(props.hbl.consignee_contact);
    form.consignee_nic = props.hbl.nic;
    form.consignee_address = props.hbl.address;
    isSameConsigneeContactNumber.value = (props.hbl.consignee_contact === props.hbl.consignee_whatsapp_number);
    consigneeWhatsappNumberCountryCode.value = splitCountryCode(props.hbl.consignee_whatsapp_number);
    consigneeWhatsappNumber.value = splitContactNumber(props.hbl.consignee_whatsapp_number);
    consigneeAdditionalMobileCountryCode.value = splitCountryCode(props.hbl.consignee_additional_mobile_number);
    consigneeAdditionalMobileNumber.value = splitContactNumber(props.hbl.consignee_additional_mobile_number);
};

const copyFromHBLToShipperModalShow = ref(false);
const reference = ref(null);

const confirmShowingCopyFromHBLToShipperModal = () => {
    copyFromHBLToShipperModalShow.value = true;
}

const closeCopyFromHBLToShipperModal = () => {
    reference.value = null;
    copyFromHBLToShipperModalShow.value = false;
}

const splitNumber = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            countryCode.value = code;
            contactNumber.value = fullNumber.slice(code.length);
            break;
        }
    }
}

const handleCopyFromHBLToShipper = async () => {
    try {
        const response = await fetch(`/get-hbl-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            closeCopyFromHBLToShipperModal()
            push.error('HBL Missing or Invalid Reference Number');
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            closeCopyFromHBLToShipperModal()

            form.hbl_name = data.hbl_name;
            form.email = data.email;
            form.nic = data.nic;
            form.iq_number = data.iq_number;
            form.address = data.address;

            splitNumber(data.contact_number);

            push.success('Copied!');
        }

    } catch (error) {
        console.log(error);
    }
}
const handleCopyShipper = () => {
    form.consignee_name = form.hbl_name;
    form.consignee_nic = form.nic;
}

const copyFromHBLToConsigneeModalShow = ref(false);

const confirmShowingCopyFromHBLToConsigneeModal = () => {
    copyFromHBLToConsigneeModalShow.value = true;
}

const closeCopyFromHBLToConsigneeModal = () => {
    reference.value = null;
    copyFromHBLToConsigneeModalShow.value = false;
}

const splitNumberConsignee = (fullNumber) => {
    for (let code of props.countryCodes) {
        if (fullNumber.startsWith(code)) {
            consigneeCountryCode.value = code;
            consigneeContactNumber.value = fullNumber.slice(code.length);
            break;
        }
    }
}

const handleCopyFromHBLToConsignee = async () => {
    try {
        const response = await fetch(`/get-hbl-by-reference/${reference.value}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
        });

        if (!response.ok) {
            closeCopyFromHBLToConsigneeModal()
            push.error('HBL Missing or Invalid Reference Number');
            throw new Error('Network response was not ok.');
        } else {
            const data = await response.json();
            closeCopyFromHBLToConsigneeModal()

            form.consignee_name = data.consignee_name;
            form.consignee_nic = data.consignee_nic;
            form.consignee_address = data.consignee_address;
            form.consignee_note = data.consignee_note;

            splitNumberConsignee(data.consignee_contact);

            push.success('Copied!');
        }

    } catch (error) {
        console.log(error);
    }
}
</script>

<template>
    <form @submit.prevent="handleHBLUpdate">
        <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
            <div class="sm:col-span-2 grid grid-rows gap-4">
                <Card>
                    <template #title>Primary Details</template>
                    <template #content>
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
                            <SelectButton v-model="form.hbl_type" :options="hblTypes" name="HBL Type" />
                            <InputError :message="form.errors.hbl_type"/>
                        </Fieldset>

                        <Fieldset legend="Warehouse">
                            <SelectButton v-model="form.warehouse" :options="warehouses" name="HBL Type" option-label="name" option-value="name" @change="updateWarehouseId"/>
                            <InputError :message="form.errors.warehouse" />
                        </Fieldset>

                        <div class="flex justify-center mt-36">
                            <img :src="hblImage" alt="hbl-image" class="w-3/4">
                        </div>
                    </template>
                </Card>
            </div>

            <div class="sm:col-span-2">
                <Card>
                    <template #title>
                        <div class="flex justify-between items-center">
                            <span>Shipper Details</span>
                            <Button aria-label="Copy from HBL" icon="pi pi-clipboard" rounded size="large" variant="text" x-tooltip.placement.bottom="'Copy from HBL'" @click.prevent="confirmShowingCopyFromHBLToShipperModal" />
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <InputLabel value="Name"/>
                                <IconField>
                                    <InputIcon class="pi pi-user" />
                                    <InputText v-model="form.hbl_name" class="w-full" placeholder="Name" />
                                </IconField>
                                <InputError :message="form.errors.hbl_name"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Email"/>
                                <IconField>
                                    <InputIcon class="pi pi-envelope" />
                                    <InputText v-model="form.email" class="w-full"
                                               placeholder="Email" type="email" />
                                </IconField>
                                <InputError :message="form.errors.email"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Mobile Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="countryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="contactNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.contact_number" class="col-span-1"/>
                            </div>

                            <div class="col-span-3">
                                <div class="flex items-center gap-2">
                                    <Checkbox v-model="isSameContactNumber"
                                              binary inputId="whatsapp" @change="addContactToWhatsapp" />
                                    <label for="whatsapp"> Use mobile number as whatsapp number</label>
                                </div>
                            </div>

                            <div v-if="!isSameContactNumber" class="col-span-3">
                                <InputLabel value="Whatsapp Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="whatsappNumberCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="whatsappNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.whatsapp_number" class="col-span-1"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Additional Mobile Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="additionalMobileCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="additionalMobileNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.additional_mobile_number" class="col-span-1"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="PP or NIC No"/>
                                <IconField>
                                    <InputIcon class="pi pi-tag" />
                                    <InputText v-model="form.nic" class="w-full"
                                               placeholder="PP or NIC No" />
                                </IconField>
                                <InputError :message="form.errors.nic"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Residency No"/>
                                <IconField>
                                    <InputIcon class="pi pi-home" />
                                    <InputText v-model="form.iq_number" class="w-full"
                                               placeholder="Residency No" />
                                </IconField>
                                <InputError :message="form.errors.iq_number"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Address"/>
                                <Textarea v-model="form.address" class="w-full" cols="30" placeholder="Type address here..." rows="5" />
                                <InputError :message="form.errors.address"/>
                            </div>

                            <div v-if="form.hbl_type === 'Door to Door'" class="col-span-3">
                                <div class="flex items-center gap-2">
                                    <Checkbox v-model="isChecked"
                                              binary inputId="consignee-same" @change="addToConsigneeDetails" />
                                    <label for="consignee-same">Same as Consignee Details</label>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="sm:col-span-2 grid grid-rows">
                <Card>
                    <template #title>
                        <div class="flex justify-between items-center">
                            <span>Consignee Details</span>
                            <div class="flex space-x-1">
                                <Button v-if="form.hbl_name" aria-label="Copy Shipper" icon="pi pi-clone" rounded size="large" variant="text"  x-tooltip.placement.bottom="'Copy Shipper'"
                                        @click.prevent="handleCopyShipper" />
                                <Button aria-label="Copy from HBL" icon="pi pi-clipboard" rounded size="large" variant="text"  x-tooltip.placement.bottom="'Copy from HBL'"
                                        @click.prevent="confirmShowingCopyFromHBLToConsigneeModal" />
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-3 gap-5 mt-3">
                            <div class="col-span-3">
                                <InputLabel value="Name"/>
                                <IconField>
                                    <InputIcon class="pi pi-user" />
                                    <InputText v-model="form.consignee_name" class="w-full" placeholder="Name" />
                                </IconField>
                                <InputError :message="form.errors.consignee_name"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="PP or NIC No"/>
                                <IconField>
                                    <InputIcon class="pi pi-tag" />
                                    <InputText v-model="form.consignee_nic" class="w-full"
                                               placeholder="PP or NIC No" />
                                </IconField>
                                <InputError :message="form.errors.consignee_nic"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Mobile Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="consigneeCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="consigneeContactNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.consignee_contact" class="col-span-1"/>
                            </div>

                            <div class="col-span-3">
                                <div class="flex items-center gap-2">
                                    <Checkbox v-model="isSameConsigneeContactNumber"
                                              binary inputId="consignee-whatsapp" @change="addConsigneeContactToWhatsapp" />
                                    <label for="consignee-whatsapp">Use mobile number as whatsapp number</label>
                                </div>
                            </div>

                            <div v-if="!isSameConsigneeContactNumber" class="col-span-3">
                                <InputLabel value="Whatsapp Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="consigneeWhatsappNumberCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="consigneeWhatsappNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.consignee_whatsapp_number" class="col-span-1"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Additional Mobile Number"/>
                                <div class="flex flex-row">
                                    <Select v-model="consigneeAdditionalMobileCountryCode" :options="countryCodes" class="w-25 !rounded-r-none !border-r-0" filter placeholder="Select a Country Code" />
                                    <InputText v-model="consigneeAdditionalMobileNumber" class="!rounded-l-none w-full" placeholder="123 4567 890"/>
                                </div>
                                <InputError :message="form.errors.consignee_additional_mobile_number" class="col-span-1"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Address"/>
                                <Textarea v-model="form.consignee_address" class="w-full" cols="30" placeholder="Type address here..." rows="5" />
                                <InputError :message="form.errors.consignee_address"/>
                            </div>

                            <div class="col-span-3">
                                <InputLabel value="Note"/>
                                <Textarea v-model="form.consignee_note" class="w-full" cols="30" placeholder="Type note here..." rows="3" />
                                <InputError :message="form.errors.consignee_note"/>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-6 my-4 gap-4">
            <div class="sm:col-span-4">
                <Card>
                    <template #title>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span>Package Details</span>
                                <InputError :message="errors.packages"/>
                            </div>
                            <Button :disabled="!isExistsRules" icon="pi pi-plus" label="New Package" severity="help" type="button" variant="outlined"
                                    @click="showPackageDialog" />
                        </div>
                    </template>
                    <template #content>
                        <DataTable v-if="form.packages.length > 0" :value="form.packages" tableStyle="min-width: 50rem">
                            <Column header="Actions">
                                <template #body="slotProps">
                                    <Button icon="pi pi-times" rounded severity="danger" size="small" variant="text" @click.prevent="confirmRemovePackage(slotProps.index)" />

                                    <Button icon="pi pi-pencil" rounded size="small" variant="text" @click.prevent="openEditModal(slotProps.index)"  />
                                </template>
                            </Column>
                            <Column field="type" header="Type">
                                <template #body="slotProps">
                                    {{ slotProps.data.package_type }}
                                </template>
                            </Column>
                            <Column field="length" header="Length (CM)">
                                <template #body="slotProps">
                                    {{ slotProps.data.length.toFixed(3) }}
                                </template>
                            </Column>
                            <Column field="width" header="Width">
                                <template #body="slotProps">
                                    {{ slotProps.data.width.toFixed(3) }}
                                </template>
                            </Column>
                            <Column field="height" header="Height">
                                <template #body="slotProps">
                                    {{ slotProps.data.height.toFixed(3) }}
                                </template>
                            </Column>
                            <Column field="quantity" header="Quantity"></Column>
                            <Column field="weight" header="Weight">
                                <template #body="slotProps">
                                    {{ slotProps.data.weight.toFixed(3) }}
                                </template>
                            </Column>
                            <Column field="volume" header="Volume (M.CU)"></Column>
                            <Column field="remarks" header="Remark"></Column>
                        </DataTable>
                        <div v-if="packageList.length === 0"
                             class="text-center">
                            <div class="text-center mb-4">
                                <i class="pi pi-box text-purple-300 animate-slow-bounce" style="font-size: 8rem"></i>
                                <p class="text-gray-600">
                                    No packages. Please add packages to view data.
                                </p>
                            </div>
                            <Button :disabled="!isExistsRules" icon="pi pi-plus" label="New Package" severity="help" type="button" variant="outlined"
                                    @click="showPackageDialog" />
                        </div>
                    </template>
                </Card>
            </div>

            <div class="sm:col-span-2">
                <Card>
                    <template #title>
                        <div class="flex justify-between items-center">
                            <span>Price and Payment</span>
                            <Button icon="pi pi-calculator" label="Calculate" severity="help" variant="outlined" @click="calculatePayment" />
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-2 gap-5 mt-5">
                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.freight_charge" :disabled="!isEditable" :minFractionDigits="2"  class="w-full" inputId="freight-charge" min="0" step="any" variant="filled" />
                                    <label for="freight-charge">Freight Charge</label>
                                </IftaLabel>
                                <InputError :message="form.errors.freight_charge"/>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.bill_charge" :disabled="!isEditable" :minFractionDigits="2" class="w-full" inputId="bill-charge" min="0" step="any" variant="filled" />
                                    <label for="bill-charge">Bill Charge</label>
                                </IftaLabel>
                                <InputError :message="form.errors.bill_charge"/>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.destination_charge" :disabled="!isEditable" :minFractionDigits="2"  class="w-full" inputId="bill-charge" min="0" step="any" variant="filled" />
                                    <label for="bill-charge">Destination Charges</label>
                                </IftaLabel>
                                <InputError :message="form.errors.destination_charge"/>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.package_charges" :disabled="!isEditable" :minFractionDigits="2"  class="w-full" inputId="bill-charge" min="0" step="any" variant="filled" />
                                    <label for="bill-charge">Package Charges</label>
                                </IftaLabel>
                                <InputError :message="form.errors.package_charges"/>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.discount" :disabled="!isEditable" :minFractionDigits="2"  class="w-full" inputId="bill-charge" min="0" step="any" variant="filled" />
                                    <label for="bill-charge">Discount</label>
                                </IftaLabel>
                                <InputError :message="form.errors.discount"/>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.additional_charge" :disabled="!isEditable" :minFractionDigits="2"  class="w-full" inputId="bill-charge" min="0" step="any" variant="filled" />
                                    <label for="bill-charge">Additional Charges</label>
                                </IftaLabel>
                                <InputError :message="form.errors.additional_charge"/>
                            </div>

                            <div class="col-span-2">
                                <IftaLabel>
                                    <InputNumber v-model="form.paid_amount" :disabled="!isEditable" :minFractionDigits="2"  class="w-full" inputId="bill-charge" min="0" step="any" variant="filled" />
                                    <label for="bill-charge">Paid Amount</label>
                                </IftaLabel>
                                <InputError :message="form.errors.paid_amount"/>
                            </div>

                            <div class="flow-root col-span-2 my-3">
                                <ul class="-my-6" role="list">
                                    <li class="flex py-3">
                                        <div class="flex items-center gap-3 text-base font-medium text-gray-900 dark:text-white">
                                            <ToggleButton
                                                v-model="form.is_departure_charges_paid"
                                                onIcon="pi pi-check"
                                                offIcon="pi pi-times"
                                                class="w-40"
                                                size="small"
                                                aria-label="Confirmation"
                                            />
                                            <InputLabel value="Departure(Agent) Charges Paid"/>
                                        </div>
                                    </li>
                                    <li class="flex py-3">
                                        <div class="flex items-center gap-3 text-base font-medium text-gray-900 dark:text-white">
                                            <ToggleButton
                                                v-model="form.is_destination_charges_paid"
                                                onIcon="pi pi-check"
                                                offIcon="pi pi-times"
                                                class="w-40"
                                                size="small"
                                                aria-label="Confirmation"
                                            />
                                            <InputLabel value="Destination Charges Paid"/>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="flow-root col-span-2 my-3">
                                <ul class="-my-6" role="list">
                                    <li class="flex py-3">
                                        <div class="flex flex-1 flex-col">
                                            <div>
                                                <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                    <h3>
                                                        Packages
                                                    </h3>
                                                    <p class="ml-4">{{ packageList.length }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="flex py-3">
                                        <div class="flex flex-1 flex-col">
                                            <div>
                                                <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                    <h3>
                                                        Weight
                                                    </h3>
                                                    <p class="ml-4">{{ grandTotalWeight.toFixed(2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="flex py-3">
                                        <div class="flex flex-1 flex-col">
                                            <div>
                                                <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                    <h3>
                                                        Volume
                                                    </h3>
                                                    <p class="ml-4">{{ grandTotalVolume.toFixed(3) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-span-2">
                                <div
                                    class="flex justify-between text-2xl text-success font-bold"
                                >
                                    <p class="line-clamp-1">Grand Total</p>
                                    <div class="flex items-center">
                                        <i v-if="packageList.length > 0" class="pi pi-info-circle text-info hover:cursor-pointer mr-2" style="font-size: 1.2rem" @click="isShowedPaymentSummery = !isShowedPaymentSummery"></i>
                                        <p>{{ hblTotal ? hblTotal.toFixed(2) : 0.00 }} {{ currency }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-6 my-6 gap-4">
            <!-- Empty grid columns for spacing -->
            <div class="col-span-4"></div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-5 col-span-2">
                <Button label="Cancel" severity="danger" variant="outlined"  @click="router.visit(route('hbls.index'))" />

                <Button :class="{ 'opacity-50': form.processing }" icon="pi pi-arrow-right" iconPos="right" label="Update HBL" type="submit" />
            </div>
        </div>

    </form>

    <Dialog v-model:visible="showAddNewPackageDialog" :header="editMode ? `Edit Package` : `Add New Package`" :style="{ width: '60rem' }" block-scroll maximizable modal position="bottom" @hide="onDialogHide" @show="onDialogShow">

        <span class="text-surface-500 dark:text-surface-400 block mb-4">{{ !editMode ? "Add new package to HBL" : "" }}</span>

        <div class="grid grid-cols-4 gap-4">
            <div v-if="packageRulesData.length > 0" class="col-span-4" >
                <InputLabel>
                    Package
                    <span v-if="form.is_active_package" class="text-red-500 text-sm">*</span>
                </InputLabel>
                <Select
                    v-model="packageItem.package_rule"
                    @change="getSelectedPackage"
                    :required="form.is_active_package"
                    :disabled="!form.is_active_package && packageList.length > 0"
                    class="w-full"
                    filter
                    option-value="id"
                    placeholder="Choose Package"
                />
            </div>

            <div class="col-span-4 md:col-span-1">
                <InputLabel value="Type"/>
                <Select v-model="selectedType" :options="packageTypes" class="w-full" filter option-label="name" option-value="name" placeholder="Choose One" @change="updateTypeDescription" />
            </div>

            <div class="col-span-4 md:col-span-2">
                <InputLabel>
                    Type Description
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputText v-model="packageItem.type" class="w-full" placeholder="Sofa set"/>
            </div>

            <div class="col-span-4 md:col-span-1">
                <InputLabel>
                    Measure Type
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <Select v-model="packageItem.measure_type" :options="measureTypes" class="w-full" placeholder="Choose One"/>
            </div>

            <div class="col-span-4 md:col-span-1">
                <InputLabel>
                    Length
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="packageItem.length" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                <Message severity="secondary" size="small" variant="simple">{{ packageItemLength.toFixed(2) }} cm</Message>
            </div>

            <div class="col-span-4 md:col-span-1">
                <InputLabel>
                    Width
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="packageItem.width" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                <Message severity="secondary" size="small" variant="simple">{{ packageItemWidth.toFixed(2) }} cm</Message>
            </div>

            <div class="col-span-4 md:col-span-1">
                <InputLabel>
                    Height
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="packageItem.height" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                <Message severity="secondary" size="small" variant="simple">{{ packageItemHeight.toFixed(2) }} cm</Message>
            </div>

            <div class="col-span-4 md:col-span-1">
                <InputLabel>
                    Quantity
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="packageItem.quantity" class="w-full" min="0" placeholder="1" step="1"/>
            </div>

            <div class="col-span-2">
                <InputLabel>
                    Volume ({{volumeUnit }})
                    <span class="text-red-500 text-sm">*</span>
                </InputLabel>
                <InputNumber v-model="packageItem.volume" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
                <Message severity="secondary" size="small" variant="simple">{{packageItemVolume}} M.CU</Message>
            </div>

            <div class="col-span-2">
                <InputLabel value="Total Weight" />
                <InputNumber v-model="packageItem.totalWeight" :maxFractionDigits="5" :minFractionDigits="2" class="w-full" min="0.00" placeholder="1.00" step="0.01"/>
            </div>

            <div class="col-span-4">
                <InputLabel value="Remarks" />
                <Textarea v-model="packageItem.remarks" class="w-full" cols="30" placeholder="Type Remarks..." rows="4" />
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" text @click="closeAddPackageModal" />
            <Button :label="editMode ? `Edit Package` : `Add Package`" severity="help" @click="addPackageData" />
        </template>

    </Dialog>

    <Dialog v-model:visible="copyFromHBLToShipperModalShow" :style="{ width: '25rem' }" header="Copy From HBL" modal>
        <div class="flex items-center gap-4 mb-4">
            <label class="font-semibold w-24" for="reference">HBL Reference</label>
            <InputText id="reference" v-model="reference" autocomplete="off" class="flex-auto" placeholder="Enter HBL Reference" required="true" />
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" type="button" @click="closeCopyFromHBLToShipperModal"></Button>
            <Button label="Copy" type="button" @click.prevent="handleCopyFromHBLToShipper"></Button>
        </div>
    </Dialog>

    <Dialog v-model:visible="copyFromHBLToConsigneeModalShow" :style="{ width: '25rem' }" header="Copy From HBL" modal>
        <div class="flex items-center gap-4 mb-4">
            <label class="font-semibold w-24" for="reference">HBL Reference</label>
            <InputText id="reference" v-model="reference" autocomplete="off" class="flex-auto" placeholder="Enter HBL Reference" required="true" />
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" type="button" @click="closeCopyFromHBLToConsigneeModal"></Button>
            <Button label="Copy" type="button" @click.prevent="handleCopyFromHBLToConsignee"></Button>
        </div>
    </Dialog>
</template>
