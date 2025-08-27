<script setup>
import { onMounted, ref, watch } from "vue";
import axios from "axios";
import { usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Input from 'primevue/inputtext';

const props = defineProps({
    hblId: {
        type: Number,
        required: true,
    },
});

const remarks = ref([]);
const newRemark = ref('');
const loading = ref(false);
const fetching = ref(false); // Separate loading state for fetching
const page = usePage();

const fetchRemarks = async () => {
    if (!props.hblId) return;
    fetching.value = true;

    try {
        const { data } = await axios.get(`/remarks/hbl/${props.hblId}`);
        remarks.value = data.data || data;
    } catch (error) {
        console.error('Error fetching remarks:', error);
    } finally {
        fetching.value = false;
    }
};

watch(() => props.hblId, () => {
    fetchRemarks();
});

const addRemark = async () => {
    if (!newRemark.value.trim()) return;
    loading.value = true;

    try {
        // Send the remark to the server
        await axios.post(`/hbls/${props.hblId}/remarks`, {
            body: newRemark.value
        });

        // Clear input
        newRemark.value = '';

        // Refetch remarks from database to get the latest data with proper IDs and timestamps
        await fetchRemarks();

    } catch (error) {
        console.error('Error adding remark:', error);
        alert('Failed to add remark. Please try again.');
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';

    try {
        // Handle both ISO format and Laravel's datetime format
        const date = new Date(dateString.includes(' ') ? dateString.replace(' ', 'T') : dateString);
        return date.toLocaleString();
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
};

onMounted(() => {
    fetchRemarks();
});
</script>

<template>
    <div class="flex flex-col h-[400px] border rounded-lg p-4 bg-gray-50">
        <!-- Loading overlay for fetching -->
        <div
            v-if="fetching"
            class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-10 rounded-lg"
        >
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-success"></div>
                <p class="mt-2 text-gray-600">Loading remarks...</p>
            </div>
        </div>

        <!-- Chat messages -->
        <div class="flex-1 overflow-y-auto space-y-4 px-4 relative">
            <!-- Empty state -->
            <div
                v-if="!fetching && remarks.length === 0"
                class="flex items-center justify-center h-full text-gray-400"
            >
                <div class="text-center text-2xl">
                    <i class="pi pi-comments !size-4 mb-2"></i>
                    <p>No remarks yet</p>
                    <p class="text-sm">Be the first to add a remark</p>
                </div>
            </div>

            <!-- Remarks list -->
            <div
                v-for="(item, index) in remarks"
                :key="item.id || index"
                :class="item?.user?.id === page.props.auth.user.id ? 'justify-end' : 'justify-start'"
                class="flex"
            >
                <div
                    :class="item?.user?.id === page.props.auth.user.id ? 'bg-success text-white' : 'bg-white text-gray-700'"
                    class="max-w-xs rounded-lg p-3 shadow-md"
                >
                    <p class="text-sm font-semibold">{{ item?.user?.name }}</p>
                    <p class="break-words">{{ item.body }}</p>
                    <small class="block text-xs mt-1 opacity-70">
                        {{ formatDate(item.created_at) }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Input box -->
        <div class="flex items-center gap-2 mt-4 relative">
            <Input
                v-model="newRemark"
                :disabled="loading || fetching"
                class="flex-1 rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-success/90 disabled:opacity-50"
                placeholder="Type a remark..."
                @keyup.enter="addRemark"
            />

            <!-- Loading indicator for sending -->
            <div
                v-if="loading"
                class="absolute right-12"
            >
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-success"></div>
            </div>

            <Button
                :disabled="loading || fetching || !newRemark.trim()"
                @click="addRemark"
                class="bg-success text-white px-4 py-2 rounded-lg hover:bg-success/80 disabled:opacity-50 flex items-center gap-2"
            >
                <i class="pi pi-send text-sm"></i>
                <span>{{ loading ? 'Sending...' : 'Send' }}</span>
            </Button>
        </div>
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
