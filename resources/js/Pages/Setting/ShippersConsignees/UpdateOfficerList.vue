<script setup>
import {router, useForm} from "@inertiajs/vue3";
import {push} from "notivue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    officer:{
        type:object,default:() => {

        },
    },
});

const form = useForm({
    id:  props.officer.id,
    name: props.officer.name,
    email: props.officer.mobile_number,
    pp_or_nic_no: props.officer.pp_or_nic_no,
    residency_no : props.officer.residency_no,
});
const updateOfficerList =() =>{
    form.put(route("setings."))
}

</script>

<template #header>Edit Officer Details</template>
<AppLayout title="edit Officer Details"
<template> </template>

<style scoped>

</style>
