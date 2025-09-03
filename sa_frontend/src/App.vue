<template>
    <div class="flex min-h-screen font-figtree">
        <div v-if="isSidebarOpen" class="fixed inset-0 bg-black/40 z-10 md:hidden" @click="toggleSidebar"></div>
        <Sidebar :class="[isSidebarOpen ? 'translate-x-0 z-20' : '-translate-x-full', 'md:translate-x-0']" />
        <div class="flex-1 flex flex-col md:ml-64 h-screen overflow-x-hidden">
            <Navbar @toggleSidebar="toggleSidebar" />
            <main class="p-4">
                <RouterView></RouterView>
            </main>
        </div>
    </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'

import Navbar from './components/Navbar/Navbar.vue'
import Sidebar from './components/Sidebar/Sidebar.vue'
import { ref } from 'vue'

const isSidebarOpen = ref(false)

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value
}
</script>

<style scoped>

</style>