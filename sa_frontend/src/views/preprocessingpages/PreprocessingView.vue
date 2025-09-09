<template>
    <Tabs :tabs="TabLinks"></Tabs>
    <div class="bg-white p-4 w-full">
        <RouterView></RouterView>
    </div>
</template>

<script setup>
import Tabs from '@/components/TabMenu/Tabs.vue'

const TabLinks = [
    {
        to: '/preprocessing/casefolding',
        label: 'Case Folding'
    },
    {
        to: '/preprocessing/fixed-words',
        label: 'Fixed Words'
    },
    {
        to: '/preprocessing/cleaning',
        label: 'Cleaning'
    },
    {
        to: '/preprocessing/stopword-removal',
        label: 'Stopword Removal'
    },
    {
        to: '/preprocessing/stemming',
        label: 'Stemming'
    }
]
</script>

<style scoped>

</style>