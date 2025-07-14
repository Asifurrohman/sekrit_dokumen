<template>
    <RouterLink :to="to" v-slot="{ isActive}">
        <div :class="['px-4 py-2 rounded-lg transition', isActive ? 'bg-indigo-500 text-white' : 'bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 cursor-pointer']">
            {{ label }}
        </div>
    </RouterLink>
</template>

<script setup>
defineProps({
    to: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    }
})
</script>

<style scoped>

</style>