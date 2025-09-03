<template>
    <li>
        <RouterLink :to="to" v-slot="{ isActive }">
            <div :class="['flex items-center gap-2 px-4 py-4 rounded-lg transition', isActive ?  'bg-indigo-50 text-blue-600 font-semibold hover:text-blue-600 cursor-default' : 'text-slate-400 hover:text-blue-400']">

                <Icon :icon="icon" class="text-xl" />
                {{ label }}
            </div>
        </RouterLink>
    </li>
</template>

<script setup>
defineProps({
    to: {
        type: String,
        required: true
    },
    icon: {
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