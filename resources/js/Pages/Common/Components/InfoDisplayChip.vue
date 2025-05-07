<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    value: {
        type: [String, Array],
        default: '-'
    }
})

const chipItems = computed(() => {
    if (Array.isArray(props.value)) {
        return props.value
    } else if (typeof props.value === 'string') {
        // Remove brackets
        const cleaned = props.value.replace(/^\[|\]$/g, '')

        // Match items between quotes OR items between commas, allowing spaces in values
        const matches = cleaned.match(/"([^"]+)"|[^,]+/g)

        return matches
            ?.map(item =>
                item.replace(/"/g, '').replace(/,/g, '').trim()
            )
            .filter(Boolean) || []
    } else {
        return []
    }
})

</script>

<template>
    <div>
        <p class="text-xs uppercase text-slate-400 dark:text-navy-300">
            {{ label }}
        </p>
        <div class="mt-1 flex flex-wrap gap-2">
            <template v-if="chipItems.length">
                <span
                    v-for="(item, index) in chipItems"
                    :key="index"
                    class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-200 dark:bg-navy-500 text-slate-700 dark:text-navy-100"
                >
                    {{ item }}
                </span>
            </template>
            <template v-else>
                <span class="font-medium text-slate-700 dark:text-navy-100">
                    -
                </span>
            </template>
        </div>
    </div>
</template>
