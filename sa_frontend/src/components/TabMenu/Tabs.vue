<template>
    <div class="bg-white px-4 py-2 rounded-lg flex items-center gap-2 mb-4 text-sm">
        <TabLink v-for="tab in tabs" v-bind:key="tab.to" :to="tab.to" :label="tab.label"></TabLink>
    </div>
</template>

<script setup>
import TabLink from './TabLink.vue'

defineProps({
    tabs: {
        type: Array,
        required: true
    }
})
</script>

<style scoped>

</style>