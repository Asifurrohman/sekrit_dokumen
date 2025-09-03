<template>
    <div class="flex justify-between mb-4">
        <h2 class="text-slate-700 font-semibold text-2xl">Tabel Dataset Bersih</h2>
        <div class="flex gap-4 items-center">
            <InfoTooltip>
                <div class="space-y-2">
                    <p>
                        Penjelasan Tweet Semi Bersih dan Tweet Bersih:
                        <ul class="list-disc pl-2">
                            <li><strong>Tweet Semi Bersih</strong>: Pengolahan dataset yang hanya sebatas Case Folding dan memberi spasi pada tanda baca.</li>
                            <li><strong>Tweet Bersih</strong>: Pengolahan dataset secara menyeluruh meliputi Data Cleaning, Case Folding, Stopword Removal, dan  Stemming/Lemmatization.</li>
                        </ul>
                    </p>
                    <p>
                        Tombol <strong>Klasifikasikan Dataset</strong> berguna untuk mengklasifikasikan dataset menjadi bernilai positif atau negatif secara otomatis. <strong>Hati-hati dalam penggunaannya</strong>, karena prosesnya lama.
                    </p>
                </div>
            </InfoTooltip>
            <button @click="submitDataLabeling" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 cursor-pointer">
                <span class="text-xl">
                    <Icon icon="material-symbols:bubble-chart" />
                </span>
                Klasifikasikan Dataset
            </button>
            <button @click="recleanDataset()" class="flex gap-2 text-sm cursor-pointer bg-indigo-50 hover:bg-indigo-100 transition text-blue-600 px-4 py-2 rounded-lg">
                <span class="text-xl">
                    <Icon icon="material-symbols:subtitles-gear-outline-rounded" />
                </span>
                Generate Ulang Dataset
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
    
    <MyLoader :isLoading="isRecleaning" title="Pra-pemrosesan Ulang Dataset" subtitle="Proses pembersihan ulang dataset"></MyLoader>
    <MyLoader :isLoading="isClassifying" title="Pengklasifikasian Dataset Otomatis" subtitle="Proses pengklasifikasian atau pelabelan dataset bernilai positif atau negatif"></MyLoader>
    
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useRouter } from 'vue-router'

import DataTable from '@/components/Table/DataTable.vue'
import Pagination from '@/components/Pagination/Pagination.vue'
import MyLoader from '@/components/LoaderAnimation/MyLoader.vue'
import InfoTooltip from '@/components/Tooltip/InfoTooltip.vue'

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Tweet Mentah', key: 'rawTweet', sortable: true },
{ label: 'Tweet Semi Bersih', key: 'semiCleanedTweet', sortable: true },
{ label: 'Tweet Bersih', key: 'fullyCleanedTweet', sortable: true }
]

const cleanedTweets = ref([])
const isLoading = ref(false)
const isRecleaning = ref(false)
const isClassifying = ref(false)
const errorMessage = ref('')
const pagination = ref({})
const search = ref('')
const toast = useToast()
const router = useRouter()

const fetchCleanedDataset = async (page = 1) => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/cleaned-datasets', { params: { page, search: search.value } })
        // console.log(data)
        
        cleanedTweets.value = data.data.map((item, index) => ({
            no: (data.meta.from || 1) + index,
            rawTweet: item.rawTweet,
            semiCleanedTweet: item.semiCleanedTweet,
            fullyCleanedTweet: item.fullyCleanedTweet
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

const recleanDataset = async () => {
    isRecleaning.value = true
    errorMessage.value = ''
    
    try {
        const { data } = await axios.post('http://127.0.0.1:8000/api/cleaned-datasets/reclean')
        toast.success(data.message || 'Dataset berhasil dibersihkan ulang!')
        // isLoading.value = false
    } catch(error) {
        console.error('Gagal membersihkan ulang dataset:', error)
        errorMessage.value = 'Gagal membersihkan ulang dataset'
        toast.error('Gagal membersihkan ulang dataset!')
    } finally {
        isRecleaning.value = false
    }
}

const submitDataLabeling = async () => {
    isClassifying.value = true
    
    try {
        const { data } = await axios.post('http://127.0.0.1:8000/api/cleaned-datasets/classify')
        // console.log("Response dari backend:", data)
        toast.success(data.message || 'Dataset berhasil diklasifikasikan!')
        router.push('/classification')
        
    } catch(error) {
        toast.error('Gagal mengklasifikasikan dataset!')
        console.error('Gagal mengklasifikasikan dataset:', error.response?.data || error.message)
    } finally {
        isClassifying.value = false
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