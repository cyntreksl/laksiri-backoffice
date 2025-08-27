<script setup>
import {onMounted, ref} from "vue";

const props = defineProps({
    hblId: {
        type: Number,
        required: true,
    },
});

const remarks = ref([]);
const newRemark = ref('');
const loading = ref(false);

const fetchRemarks = async () => {
    const { data } = await axios.get(`/remarks/hbl/${props.hblId}`);
    remarks.value = data;
};

const addRemark = async () => {
    if (!newRemark.value.trim()) return;
    loading.value = true;

    try {
        const { data } = await axios.post(`hbls/${props.hblId}/remarks`, {
            body: newRemark.value
        });

        remarks.value.push(data.body);
        newRemark.value = '';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRemarks());
</script>

<template>
    <div class="flex flex-col h-[400px] border rounded-lg p-4 bg-gray-50">
        <!-- Chat messages -->
        <div class="flex-1 overflow-y-auto space-y-4 px-4">
            <div
                v-for="(item, index) in remarks"
                :key="index"
                :class="item.user === 'You' ? 'justify-end' : 'justify-start'"
                class="flex"
            >
                <div
                    :class="item.user === 'You' ? 'bg-success text-white' : 'bg-white text-gray-700'"
                    class="max-w-xs rounded-lg p-3 shadow-md"
                >
                    <p class="text-sm font-semibold">{{ item.user.name }}</p>
                    <p>{{ item.remark }}</p>
                    <small class="block text-xs mt-1 opacity-70">{{ new Date(item.created_at).toLocaleString() }}</small>
                </div>
            </div>
        </div>

        <!-- Input box -->
        <div class="flex items-center gap-2 mt-4">
            <input
                v-model="newRemark"
                class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-success/90"
                placeholder="Type a remark..."
            />
            <button
                class="bg-success text-white px-4 py-3 rounded-lg hover:bg-success/80"
                @click="addRemark"
            >
                Send
            </button>
        </div>
    </div>
</template>
