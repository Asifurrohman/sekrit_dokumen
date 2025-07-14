<template>
  <div class="">
    <h2 class="text-2xl font-semibold text-slate-700 mb-4">Import Dataset</h2>
    <div @dragover.prevent @dragenter.prevent @drop.prevent="handleDrop" @click="triggerFileSelect" class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-blue-400 transition cursor-pointer">
      <input @change="handleFileSelect" ref="fileInput" type="file" accept=".csv" class="hidden"/>
      <p class="text-slate-500">
        <Icon icon="material-symbols:upload" class="text-4xl mx-auto mb-2 text-blue-500" />
        <span v-if="!fileName">Drag and drop CSV file here or click to select</span>
        <span v-else>Selected file: <strong>{{ fileName }}</strong></span>
      </p>
    </div>
    
    <!-- Preview Table -->
    <div v-if="preview.length" class="mt-6 overflow-x-auto rounded-lg">
      <div class="flex justify-between px-2 py-4">
        <p class="mt-2 text-sm text-slate-500 text-center">
          Menampilkan <span class="font-semibold">50 baris dataset</span> pertama dari total <span class="font-semibold">{{ preview.length }} baris dataset</span>.
        </p>
        <button @click="submitToBackend" class="bg-emerald-500 text-white px-6 py-2 rounded-lg hover:bg-emerald-600 active:bg-emerald-700 cursor-pointer">
          Simpan ke Database
        </button>
      </div>
      <table class="min-w-full text-sm text-left">
        <thead class="bg-slate-100 text-slate-500">
          <tr>
            <th v-for="(header, index) in previewHeaders" :key="index" class="p-3">
              {{ header }}
            </th>
          </tr>
        </thead>
        <tbody class="text-slate-700">
          <tr v-for="(row, rowIndex) in preview.slice(0, 50)" :key="rowIndex" class="odd:bg-white even:bg-slate-50">
            <td v-for="(header, cellIndex) in previewHeaders" :key="cellIndex" class="p-3">
              {{ row[header] }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
  <div v-if="isUploading" class="fixed z-50 top-0 left-0 right-0 bg-white/75 w-screen h-screen flex flex-col items-center">
    <div class="mt-60 flex flex-col items-center gap-10">
      <PulseLoader class="scale-300" color="#3b82f6" />
      <div class="text-4xl font-semibold capitalize text-slate-700">Sedang mengunggah dataset ke database</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Papa from 'papaparse'
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import { useToast } from 'vue-toastification'

const fileInput = ref(null)
const fileName = ref('')
const preview = ref([])
const previewHeaders = ref([])
const isUploading = ref(false)
const toast = useToast()

const router = useRouter()

// Trigger file input
const triggerFileSelect = () => {
  fileInput.value.click()
}

// Handle manual file select
const handleFileSelect = (e) => {
  const file = e.target.files[0]
  processFile(file)
}

// Handle drop file
const handleDrop = (e) => {
  const file = e.dataTransfer.files[0]
  processFile(file)
}

// Process CSV using PapaParse
const processFile = (file) => {
  if (!file || file.type !== 'text/csv') {
    alert('Harap pilih file CSV.')
    return
  }
  
  fileName.value = file.name
  
  Papa.parse(file, {
    header: true,
    skipEmptyLines: true,
    complete: (results) => {
      const cleaned = results.data.filter(row =>
      row['Datetime'] && row['Tweet Id'] && row['Text'] && row['Username']
      ).map(row => {
        const datetime = row['Datetime'].split('+')[0].trim()
        return {
          'Tweet Id': row['Tweet Id'],
          'Datetime': datetime,
          'Text': row['Text'],
          'Username': row['Username']
        }
      })
      
      if (cleaned.length === 0) {
        alert('Tidak ada data yang valid.')
        return
      }
      
      preview.value = cleaned
      previewHeaders.value = Object.keys(cleaned[0])
    },
    error: (err) => {
      console.error('Parsing error:', err)
      alert('Gagal membaca file CSV.')
    }
  })
}

const submitToBackend = async () => {
  if (!preview.value.length) {
    alert('Tidak ada data yang diproses.')
    return
  }
  
  const transformed = preview.value.map(row => ({
    tweet_id: row['Tweet Id'],
    datetime: row['Datetime'],
    username: row['Username'],
    tweet: row['Text']
  }))
  
  try {
    isUploading.value = true
    await axios.post('http://127.0.0.1:8000/api/datasets', {
      data: transformed
    })
    // alert('Data berhasil dikirim!')
    toast.success('Dataset berhasil ditambahkan')
    router.push('/dataset/raw')
  } catch (error) {
    console.error(error)
    toast.error('Dataset gagal diupload')
  }
}
</script>

<style scoped>

</style>