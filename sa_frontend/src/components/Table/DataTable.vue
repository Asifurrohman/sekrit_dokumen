<template>
    <div class="w-full overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-500 rounded-lg">
                    <th @click="header.sortable && sortBy(header.key)" v-for="(header, index) in props.headers" v-bind:key="index" :class="['p-3 cursor-pointer select-none text-wrap', index === 0 ? 'rounded-l-lg min-w-[72px] w-12 text-center' : 'md:min-w-32', index === headers.length - 1 ? 'rounded-r-lg' : '']">
                        {{ header.label }}
                        <span v-if="sortKey === header.key">
                            <Icon v-if="sortKey === header.key" :icon="sortOrder === 'asc' ? 'material-symbols:arrow-upward-alt-rounded' : 'material-symbols:arrow-downward-alt-rounded'" class="inline-block w-4 h-4 ml-1 text-slate-500" />
                        </span>
                    </th>
                    <th v-if="showDelete" class="p-3 text-center rounded-r-lg">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="text-sm text-slate-700">
                <tr v-for="(item, index) in sortedItems" :key="index" class="odd:bg-white even:bg-slate-50">
                    <td v-for="(header, i) in headers" :key="i" :class="['p-4', i === 0 ? 'min-w-[72px] w-12 text-center' : '']">
                        <div v-if="header.key === 'classification' && item.classification === ''">
                            Belum Diklasifikasikan
                        </div>
                        <div  v-else-if="header.key === 'classification'" :class="['inline-block px-2 py-0.5 rounded-full text-white text-sm font-medium', item.classification === 'positive' ? 'bg-green-500' : '', item.classification === 'negative' ? 'bg-red-500' : '', item.classification === 'neutral' ? 'bg-slate-500' : '']">
                            {{ item.classification }}
                        </div>
                        <div v-else>
                            {{ item[header.key] }}
                        </div>
                    </td>
                    <td v-if="showDelete" class="p-4 text-center">
                        <button @click="confirmDelete(item)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">
                            Hapus
                        </button>
                    </td>
                </tr>
                
                <tr v-if="isLoading && !sortedItems.length" v-for="n in 5" :key="'skeleton-' + n" class="odd:bg-white even:bg-slate-50 animate-pulse">
                    <td v-for="i in headers.length" :key="i" class="p-4">
                        <div class="h-4 bg-slate-200 rounded w-full"></div>
                    </td>
                </tr>
                
                
                <tr v-if="!sortedItems.length && !isLoading">
                    <td :colspan="headers.length + (showDelete ? 1 : 0)" class="text-center p-4 text-lg text-slate-500">
                        Tidak ada dataset.
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-80">
                <h2 class="text-lg font-semibold mb-4">Konfirmasi Hapus</h2>
                <p class="text-sm text-slate-600 mb-6">
                    Apakah kamu yakin ingin menghapus data ini?
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showConfirm = false" class="px-4 py-2 rounded border text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button @click="deleteItem" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    headers: {
        type: Array,
        required: true
    },
    items: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    showDelete: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['delete-item'])


const sortKey = ref(null)
const sortOrder = ref('asc')
const showConfirm = ref(false)
const itemToDelete = ref(null)

const sortBy = (key) => {
    if(sortKey.value === key){
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

const confirmDelete = (item) => {
    itemToDelete.value = item
    showConfirm.value = true
}

const deleteItem = () => {
    if (itemToDelete.value) {
        emit('delete-item', itemToDelete.value)
    }
    showConfirm.value = false
    itemToDelete.value = null
}

const sortedItems = computed(() => {
    if(!sortKey.value) return props.items
    
    return [...props.items].sort((a, b) => {
        const aVal = a[sortKey.value]
        const bVal = b[sortKey.value]
        
        if(sortKey.value.toLowerCase().includes('date')){
            return sortOrder.value === 'asc' ? new Date(aVal) - new Date(bVal) : new Date(bVal) - new Date(aVal)
        }
        
        if(typeof aVal === 'number'){
            return sortOrder.value === 'asc' ? aVal - bVal : bVal - aVal
        }
        
        return sortOrder.value === 'asc' ? String(aVal).localeCompare(bVal) : String(bVal).localeCompare(aVal)
    })
})
</script>

<style scoped>

</style>