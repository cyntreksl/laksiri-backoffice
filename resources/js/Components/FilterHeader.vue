<script setup>
defineProps({
    value: {
        type: String,
        required: true,
    }
})
</script>
<template>
    <div>
        <span class="font-medium">{{ value }}</span>
    </div>
</template>
