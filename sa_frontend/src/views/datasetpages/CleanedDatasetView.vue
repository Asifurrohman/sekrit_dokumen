<template>
    <div class="flex justify-between mb-4">
        <h2 class="text-slate-700 font-semibold text-2xl">Tabel Dataset Bersih</h2>
        <div class="relative group inline-block">
            <!-- <button @click="exportCleanedDataset" :class="['flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition', !cleanedTweets.length ? 'bg-gray-300 text-gray-400 cursor-not-allowed pointer-events-none' : 'bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 cursor-pointer']" @click.prevent="!cleanedTweets.length && $event.preventDefault()">
                <span class="text-xl">
                    <Icon icon="material-symbols:download-rounded" />
                </span>
                Ekspor Dataset Bersih
            </button> -->
            <button class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 cursor-pointer">
                <span class="text-xl">
                    <Icon icon="material-symbols:data-usage" />
                </span>
                Analisis Dataset
            </button>
            <div v-if="!cleanedTweets.length" class="absolute top-12 right-0 -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-50">
                Tidak ada dataset yang bersih
            </div>
        </div>
    </div>
    
    <div>
        <input type="text" v-model="search" @keyup.enter="fetchCleanedDataset(1)" placeholder="Cari datset..." class="mb-4 px-4 py-2 border border-gray-300 rounded-lg w-full">
        <p class="text-sm text-slate-500 text-left">
            Total dataset: <span class="font-semibold">{{ pagination.meta?.total || 0 }}</span>
        </p>
        <DataTable :headers="headers" :items="cleanedTweets" :isLoading="isLoading"></DataTable>
        <Pagination v-if="cleanedTweets.length > 0" :currentPage="pagination.meta?.current_page || 1" :lastPage="pagination.meta?.last_page || 1" @change="(page) => fetchCleanedDataset(page, search)"></Pagination>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

import DataTable from '@/components/Table/DataTable.vue'
import Pagination from '@/components/Pagination/Pagination.vue'

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Cleaned Tweet', key: 'cleanedTweet', sortable: true }
]

const cleanedTweets = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const pagination = ref({})
const search = ref('')
const toast = useToast()

const fetchCleanedDataset = async (page = 1) => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/cleaned-datasets', { params: { page, search: search.value } })
        console.log(data)
        
        cleanedTweets.value = data.data.map((item, index) => ({
            no: (data.meta.from || 1) + index,
            cleanedTweet: item.cleanedTweet
        }))
        
        pagination.value = data
        
    } catch(error){
        console.error('Gagal mengambil dataset:', error)
        errorMessage.value = 'Gagal mengambil dataset'
        isLoading.value = false
        
    } finally {
        isLoading.value = false
    }
}

const exportCleanedDataset = async() => {
    try{
        const response = await axios.get('http://127.0.0.1:8000/api/cleaned-datasets/export', {
            responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'cleaned_dataset.csv'); // nama file saat diunduh
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch(error){
        console.error('Gagal mengunduh file', error)
        toast.error('Gagal mengunduh file!')
    }
}

onMounted(async () => {
    await fetchCleanedDataset()
})

</script>

<style scoped>

</style>