<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

defineEmits(['close']);
</script>

<template>
    <div class="modal-content bg-white p-4 rounded shadow-lg">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Create New Shipper</h2>
            <button @click="$emit('close')" class="text-gray-500 hover:text-gray-800">
                &times;
            </button>
        </div>


        <form>
            <label class="block mb-2">
                Name:
                <input type="text" class="w-full p-2 border rounded" placeholder="Enter Name" />
            </label>

            <label class="block mb-2">
                Email:
                <input type="email" class="w-full p-2 border rounded" placeholder="Enter Email" />
            </label>

            <button type="submit" class="mt-4 bg-blue-500 text-white p-2 rounded">
                Submit
            </button>
        </form>
    </div>
</template>

<style scoped>

</style>
