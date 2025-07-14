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
        to: '/dataset/raw',
        label: 'Raw Dataset'
    },
    {
        to: '/dataset/cleaned',
        label: 'Cleaned Dataset'
    }
]

</script>

<style scoped>

</style>