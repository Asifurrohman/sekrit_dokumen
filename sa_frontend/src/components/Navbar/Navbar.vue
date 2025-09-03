<template>
    <header class="md:hidden w-full bg-white h-14 sticky top-0 flex items-center justify-between px-4">
        <!-- Branding / Logo -->
        <div class="flex items-center gap-2 md:hidden">
            <Icon icon="material-symbols:analytics-outline" class="text-blue-500 text-2xl" />
            <span class="text-slate-700 font-semibold">Sentimen <span class="text-blue-600">App</span></span>
        </div>
        
        <!-- Hamburger Menu -->
        <button @click="$emit('toggleSidebar')" class="text-3xl text-blue-500 hover:text-blue-600 active:text-blue-700 cursor-pointer md:hidden">
            <Icon icon="material-symbols:menu-rounded" />
        </button>
    </header>
</template>

<script setup>
defineEmits(['toggleSidebar'])
</script>

<style scoped>

</style>
