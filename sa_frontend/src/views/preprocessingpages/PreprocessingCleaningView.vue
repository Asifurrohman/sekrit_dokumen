<template>
    <div>
        <DataTable :headers="headers" :items="tweets" :isLoading="isLoading"></DataTable>
    </div>
</template>

<script setup>
import { ref } from 'vue'

import DataTable from '@/components/Table/DataTable.vue'
import Pagination from '@/components/Pagination/Pagination.vue'
import MyLoader from '@/components/LoaderAnimation/MyLoader.vue'
import InfoTooltip from '@/components/Tooltip/InfoTooltip.vue'

const isLoading = ref(false)

const headers = [
{ label: 'No', key: 'no' },
{ label: 'Tweet Mentah', key: 'rawTweet', sortable: true },
{ label: 'Pra-pemrosesan (Cleaning)', key: 'preprocessing', sortable: true },
{ label: 'Tweet Semi Bersih', key: 'semiCleanedTweet', sortable: true }
]

const tweets = ref([
    { no: 1, rawTweet: 'Ini adalah tweet mentah', preprocessing: 'Cleaning', semiCleanedTweet: 'Ini adalah' }
])
</script>

<style scoped>

</style>