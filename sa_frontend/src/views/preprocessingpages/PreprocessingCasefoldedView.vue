<template>
    <div class="flex justify-between items-end mb-4">
        <h2 class="text-slate-700 font-semibold text-2xl">Detail Tahap Casefolding</h2>
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
            <p class="text-sm text-slate-500 text-left">
                Total dataset: <span class="font-semibold">{{ pagination.meta?.total || 0 }}</span>
            </p>
        </div>
    </div>
    <div>
        <DataTable :headers="headers" :items="processedTweets" :isLoading="isLoading"></DataTable>
        <Pagination v-if="processedTweets.length > 0" :currentPage="pagination.meta?.current_page || 1" :lastPage="pagination.meta?.last_page || 1" @change="(page) => fetchProcessedDataset(page)"></Pagination>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

import DataTable from '@/components/Table/DataTable.vue'
import Pagination from '@/components/Pagination/Pagination.vue'
import MyLoader from '@/components/LoaderAnimation/MyLoader.vue'
import InfoTooltip from '@/components/Tooltip/InfoTooltip.vue'

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Tweet Mentah', key: 'rawTweet', sortable: true },
{ label: 'Pra-pemrosesan (Casefolding)', key: 'casefoldedTweet', sortable: true },
{ label: 'Tweet Bersih', key: 'fullyCleanedTweet', sortable: true }
]

const processedTweets = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const pagination = ref({})

const fetchProcessedDataset = async (page = 1) => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/cleaned-datasets', { params: { page}})
        // console.log(data)
        
        processedTweets.value = data.data.map((item, index) => ({
            no: (data.meta.from || 1) + index,
            rawTweet: item.rawTweet,
            casefoldedTweet: item.casefoldedTweet,
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

onMounted(async () => {
    await fetchProcessedDataset()
})
</script>

<style scoped>

</style>