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
                </tr>
            </thead>
            <tbody class="text-sm text-slate-700">
                <tr v-for="(item, index) in sortedItems" :key="index" class="odd:bg-white even:bg-slate-50">
                    <td v-for="(header, i) in headers" :key="i" :class="['p-4', i === 0 ? 'min-w-[72px] w-12 text-center' : '']">
                        
                        <div v-if="header.key === 'editClassification'">
                            <select v-model="item.classification" @change="$emit('update-classification', { item, value: item.classification })" class="border rounded px-2 py-1 text-sm">
                                <option value="positif">Positif</option>
                                <option value="negatif">Negatif</option>
                            </select>
                        </div>
                        <div v-else-if="header.key === 'classification' && item.classification === ''">
                            Belum Diklasifikasikan
                        </div>
                        <div  v-else-if="header.key === 'classification'" :class="['inline-block px-2 py-0.5 rounded-full text-white text-sm font-medium', item.classification === 'positive' ? 'bg-green-500' : '', item.classification === 'negative' ? 'bg-red-500' : '']">
                            {{ item.classification }}
                        </div>
                        <div v-else>
                            {{ item[header.key] }}
                        </div>
                    </td>
                </tr>
                
                <tr v-if="isLoading && !sortedItems.length" v-for="n in 5" :key="'skeleton-' + n" class="odd:bg-white even:bg-slate-50 animate-pulse">
                    <td v-for="i in headers.length" :key="i" class="p-4">
                        <div class="h-4 bg-slate-200 rounded w-full"></div>
                    </td>
                </tr>
                
                
                <tr v-if="!sortedItems.length && !isLoading">
                    <td :colspan="headers.length" class="text-center p-4 text-lg text-slate-500">
                        Tidak ada dataset.
                    </td>
                </tr>
            </tbody>
        </table>
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
    }
})


const sortKey = ref(null)
const sortOrder = ref('asc')

const sortBy = (key) => {
    if(sortKey.value === key){
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
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