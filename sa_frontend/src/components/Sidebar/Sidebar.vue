<template>
    <aside class="bg-white w-64 fixed h-screen px-4 transition">
        <div class="px-4 mt-4 hidden md:block">
            <a href="#" class="flex items-center gap-2">
                
                    <Icon icon="material-symbols:analytics-outline" class="text-blue-500 text-2xl" />
                    <span class="text-slate-700 font-semibold">Sentimen <span class="text-blue-600">App</span></span>
                
            </a>
        </div>
        <ul class="mt-4 md:mt-6">
            <SideLink to="/statistics" icon="material-symbols:pie-chart-outline" label="Statistik" />
            <SideLink to="/dataset" icon="material-symbols:database-outline" label="Dataset" />
            <SideLink to="/preprocessing" icon="material-symbols:subtitles-gear-rounded" label="Detail Pra-pemrosesan" />
            <SideLink to="/classification" icon="material-symbols:book-6-rounded" label="Klasifikasi" />
            <SideLink to="/about" icon="material-symbols:info-outline-rounded" label="Informasi Aplikasi" />
        </ul>
    </aside>
</template>

<script setup>
import SideLink from './SideLink.vue'


</script>

<style scoped>

</style>