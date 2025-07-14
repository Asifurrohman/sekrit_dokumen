<template>
    <div class="flex justify-center mt-4 gap-2 flex-wrap">
        <button @click="$emit('change', currentPage - 1)" class="px-4 py-1 rounded bg-gray-200 hover:bg-gray-300 cursor-pointer disabled:cursor-auto disabled:bg-gray-100 disabled:text-slate-400 disabled:hover:bg-gray-100" :disabled="currentPage === 1">
            &laquo; Prev
        </button>
        
        <button v-if="pages[0] > 1" @click="$emit('change', 1)" class="px-4 py-1 rounded bg-gray-200 cursor-pointer">
            1
        </button>
        <span v-if="pages[0] > 2" class="px-2">...</span>
        
        <button v-for="page in pages" :key="page" @click="$emit('change', page)" class="px-4 py-1 rounded" :class="{ 'bg-blue-500 text-white': page === currentPage, 'bg-gray-200 hover:bg-gray-300 cursor-pointer': page !== currentPage }">
            {{ page }}
        </button>
        
        <span v-if="pages.at(-1) < lastPage - 1" class="px-2">...</span>
        <button v-if="pages.at(-1) < lastPage" class="px-4 py-1 rounded bg-gray-200 cursor-pointer" @click="$emit('change', lastPage)">
            {{ lastPage }}
        </button>
        
        <button @click="$emit('change', currentPage + 1)" class="px-4 py-1 rounded bg-gray-200 hover:bg-gray-300 cursor-pointer disabled:cursor-auto disabled:bg-gray-100 disabled:text-slate-400 disabled:hover:bg-gray-100" :disabled="currentPage === lastPage">
            Next &raquo;
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    currentPage: Number,
    lastPage: Number,
    maxVisible: {
        type: Number,
        default: 5
    }
})

const pages = computed(() => {
    const start = Math.max(props.currentPage - Math.floor(props.maxVisible / 2), 1)
    const end = Math.min(start + props.maxVisible - 1, props.lastPage)
    const visible = []
    
    for (let i = start; i <= end; i++) {
        visible.push(i)
    }
    
    return visible
})
</script>

<style scoped>

</style>