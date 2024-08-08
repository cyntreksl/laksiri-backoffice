<script setup>
import {Head} from "@inertiajs/vue3";
defineProps({
    title: "",
})
</script>
<template xmlns="http://www.w3.org/1999/html">
    <Head :title="title"/>
    <div class="h-screen m-0 p-0">
        <slot/>
    </div>
</template>
