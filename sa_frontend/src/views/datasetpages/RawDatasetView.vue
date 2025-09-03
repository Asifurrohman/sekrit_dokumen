<template>
    <div class="flex justify-between mb-4">
        <h2 class="text-slate-700 font-semibold text-2xl">Tabel Dataset Mentah</h2>
        
        <div class="flex items-center gap-4 text-slate-700">
            <InfoTooltip>
                Tombol Generate Dataset berguna untuk melakukan pra-pemrosesan (preprocessing) pada dataset. Metode pra-pemrosesan ini membersihkan dataset mentah menjadi dataset semi bersih dan dataset bersih. 
            </InfoTooltip>
            <button @click="submitToCleanDataset" class="flex gap-2 text-sm cursor-pointer bg-indigo-50 hover:bg-indigo-100 transition text-blue-600 px-4 py-2 rounded-lg">
                <span class="text-xl">
                    <Icon icon="material-symbols:subtitles-gear-outline-rounded" />
                </span>
                Generate Dataset
            </button>
            
            <div class="relative group inline-block">
                <RouterLink to="/dataset/import-dataset" :class="['flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition', tweets.length > 0 ? 'bg-gray-300 text-gray-400 cursor-not-allowed pointer-events-none' : 'bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 cursor-pointer']" @click.prevent="tweets.length > 0 && $event.preventDefault()">
                    <span class="text-xl">
                        <Icon icon="material-symbols:add-notes-outline-rounded" />
                    </span>
                    Import Dataset
                </RouterLink>
                
                <div v-if="tweets.length > 0" class="absolute top-12 right-0 -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-50">
                    Dataset sudah ada
                </div>
            </div>
        </div>
        
    </div>
    
    <div>
        <input type="text" v-model="search" @keyup.enter="fetchDataset(1)" placeholder="Cari tweet atau username..." class="mb-4 px-4 py-2 border border-gray-300 rounded-lg w-full">
        <p class="text-sm text-slate-500 text-left">
            Total dataset: <span class="font-semibold">{{ pagination.meta?.total || 0 }}</span>
        </p>
        <DataTable :headers="headers" :items="tweets" :isLoading="isLoading"></DataTable>
        <Pagination v-if="tweets.length > 0" :currentPage="pagination.meta?.current_page || 1" :lastPage="pagination.meta?.last_page || 1" @change="(page) => fetchDataset(page, search)"></Pagination>
    </div>
    
    <MyLoader :isLoading="isUploading" title="Pra-pemrosesan Dataset" subtitle="Proses pembersihan dataset"></MyLoader>
    
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'

import DataTable from '@/components/Table/DataTable.vue'
import Pagination from '@/components/Pagination/Pagination.vue'
import MyLoader from '@/components/LoaderAnimation/MyLoader.vue'
import InfoTooltip from '@/components/Tooltip/InfoTooltip.vue'

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Tweet ID', key: 'tweetId', sortable: true },
{ label: 'Tanggal', key: 'date', sortable: true },
{ label: 'Username', key: 'username', sortable: true },
{ label: 'Tweet', key: 'tweet', sortable: true },
{ label: 'Bahasa', key: 'language', sortable: true },
]

const tweets = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const pagination = ref({})
const search = ref('')
const toast = useToast()
const isUploading = ref(false)
const router = useRouter()

const fetchDataset = async (page = 1) => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/harvest-datasets', { params: { page, search: search.value } })
        
        tweets.value = data.data.map((item, index) => ({
            no: (data.meta.from || 1) + index,
            tweetId: item.tweetId,
            date: new Date(item.datetime).toLocaleString(),
            username: item.username,
            tweet: item.tweet,
            language: item.language || 'unknown'
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

const submitToCleanDataset = async () => {
    if (!tweets.value.length) {
        toast.error('Tidak ada dataset untuk diproses!')
        return
        
    }
    
    try {
        isUploading.value = true
        await axios.delete('http://127.0.0.1:8000/api/cleaned-datasets')
        
        const response = await axios.post('http://127.0.0.1:8000/api/cleaned-datasets')
        
        toast.success(response.data.message || 'Dataset berhasil dibersihkan!')
        router.push('/dataset/cleaned')
    } catch (error) {
        console.error(error)
        toast.error('Gagal memproses dataset.')
    } finally {
        isUploading.value = false
    }
}

onMounted(async () => {
    await fetchDataset()
})
</script>

<style scoped>

</style>