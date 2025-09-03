<template>
    <div class="bg-white p-4 w-full">
        <div class="flex justify-between mb-4">
            <h2 class="text-slate-700 font-semibold text-2xl">Tabel Klassifikasi Data</h2>
            <div class="flex items-center gap-4">
                <div class="relative group inline-block">
                    <button @click="submitToTrain()" :class="['flex gap-2 text-sm cursor-pointer px-4 py-2 rounded-lg', statisticData.length > 0 ?  'bg-gray-300 text-gray-400 cursor-not-allowed pointer-events-none' : 'bg-indigo-50 hover:bg-indigo-100 transition text-blue-600']" @click.prevent="statisticData.length > 0 && $event.preventDefault()">
                        <span class="text-xl">
                            <Icon icon="material-symbols:calculate-rounded" />
                        </span>
                        Latih Dataset
                    </button>

                    <div v-if="statisticData.length > 0" class="absolute top-12 right-0 -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-50">
                        Data cukup dilatih sekali saja
                    </div>
                </div>
                <button @click="submitToEvaluate()" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 cursor-pointer">
                    <span class="text-xl">
                        <Icon icon="material-symbols:checklist-rtl-rounded" />
                    </span>
                    Evaluasi Dataset
                </button>
                <select v-model="filterLabel" @change="fetchClassifiedDataset(1)" class="border border-gray-300 px-3 py-2 rounded-lg">
                    <option value="">Filter Label</option>
                    <option value="positive">Positif</option>
                    <option value="negative">Negatif</option>
                </select>
            </div>
        </div>
        <div>
            <input type="text" v-model="search" @keyup.enter="fetchClassifiedDataset(1)" placeholder="Cari data..." class="mb-4 px-4 py-2 border border-gray-300 rounded-lg w-full">
            <p class="text-sm text-slate-500 text-left">
                Total data: <span class="font-semibold">{{ pagination.meta?.total || 0 }}</span>
            </p>
            <DataTable :headers="headers" :items="classifiedTweets" :isLoading="isLoading" ></DataTable>
            <Pagination v-if="classifiedTweets.length > 0" :currentPage="pagination.meta?.current_page || 1" :lastPage="pagination.meta?.last_page || 1" @change="(page) => fetchClassifiedDataset(page, search)"></Pagination>
        </div>
        <MyLoader :isLoading="isTraining" title="Proses Pelatihan Model" subtitle="Proses melatih model Support Vector Machine"></MyLoader>
        <MyLoader :isLoading="isEvaluating" title="Proses Evaluasi Model" subtitle="Proses evaluasi model Support Vector Machine"></MyLoader>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import MyLoader from '@/components/LoaderAnimation/MyLoader.vue'
import { useRouter } from 'vue-router'

import DataTable from '@/components/Table/DataTable.vue'
import Pagination from '@/components/Pagination/Pagination.vue'

const isLoading = ref(false)
const isTraining = ref(false)
const isEvaluating = ref(false)
const errorMessage = ref('')
const pagination = ref({})
const search = ref('')
const classifiedTweets = ref([])
const filterLabel = ref('')
const toast = useToast()
const router = useRouter()
const statisticData = ref([])

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Tweet Mentah', key: 'rawTweet' },
{ label: 'Tweet Bersih', key: 'cleanedTweet' },
{ label: 'Klasifikasi', key: 'classification' },
]


const fetchClassifiedDataset = async (page = 1) => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/cleaned-datasets', { params: { page, search: search.value, classification: filterLabel.value } })
        // console.log(data)
        
        classifiedTweets.value = data.data.map((item, index) => ({
            id: item.id,
            no: (data.meta.from || 1) + index,
            rawTweet: item.rawTweet,
            cleanedTweet: item.cleanedTweet,
            classification: item.classification || '',
            // editClassification: item.classification || ''
        }))
        
        pagination.value = data
        
        const { data: evalData } = await axios.get('http://127.0.0.1:8000/api/machine-learning') // contoh endpoint
        statisticData.value = evalData.data.map(item => ({
            modelName: item.modelName,
            totalData: item.totalData,
            accuracy: item.accuracy,
            precision: item.precision,
            recall: item.recall,
            f1Score: item.f1Score,
            confusionMatrix: item.confussionMatrix,
            topWords: item.topWords
        }))
        
    } catch(error){
        console.error('Gagal mengambil dataset:', error)
        errorMessage.value = 'Gagal mengambil dataset'
        
    } finally {
        isLoading.value = false
    }
}

const submitToTrain = async () => {
    isTraining.value = true
    
    try {
        const { data } = await axios.post('http://127.0.0.1:8000/api/cleaned-datasets/train')
        // console.log("Response dari Laravel:", data)
    } catch(error) {
        toast.error('Gagal mengklasifikasikan dataset!')
        // console.error('Gagal mengklasifikasikan dataset:', error.response?.data || error.message)
    } finally {
        isTraining.value = false
    }
}

const submitToEvaluate = async () => {
    isEvaluating.value = true
    
    try {
        const { data } = await axios.post('http://127.0.0.1:8000/api/cleaned-datasets/evaluate')
        // console.log("Response dari Laravel:", data)
        toast.success('Evaluasi model selesai!')
        router.push('/statistics')
    } catch(error) {
        // console.error('Gagal mengklasifikasikan dataset:', error.response?.data || error.message)
        toast.error('Evaluasi model gagal!')
    } finally {
        isEvaluating.value = false
    }
}


onMounted(async () => {
    await fetchClassifiedDataset()
})
</script>

<style scoped>

</style>