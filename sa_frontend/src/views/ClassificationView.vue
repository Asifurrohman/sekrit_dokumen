<template>
    <div class="bg-white p-4 w-full">
        <div class="flex justify-between mb-4">
            <h2 class="text-slate-700 font-semibold text-2xl">Tabel Klassifikasi Data</h2>
            <button class="flex gap-2 text-sm cursor-pointer bg-blue-500 hover:bg-blue-600 transition text-white px-4 py-2 rounded-lg">
                <span class="text-xl">
                    <Icon icon="material-symbols:filter-alt" />
                </span>
                Filter Klasifikasi
            </button>
        </div>
        <div>
            <DataTable :headers="headers" :items="clasifiedTweets" :isLoading="isLoading"></DataTable>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

import DataTable from '@/components/Table/DataTable.vue'

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Tweet', key: 'tweet' },
{ label: 'Tweet Bersih', key: 'cleanedTweet' },
{ label: 'Klasifikasi', key: 'classification' },
]

const clasifiedTweets = ref([
    { no: 1, tweet: 'Contoh tweet 1', cleanedTweet: 'Contoh tweet bersih 1', classification: 'Positif' },
    { no: 2, tweet: 'Contoh tweet 2', cleanedTweet: 'Contoh tweet bersih 2', classification: 'Negatif' },
    { no: 3, tweet: 'Contoh tweet 3', cleanedTweet: 'Contoh tweet bersih 3', classification: 'Netral' },
])
const isLoading = ref(false)
</script>

<style scoped>

</style>