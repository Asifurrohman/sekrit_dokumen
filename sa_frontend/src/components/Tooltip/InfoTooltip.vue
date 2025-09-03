<template>
    <div class="relative inline-block">
        <div class="w-6 h-6 text-slate-400 flex items-center justify-center rounded-full" @mouseenter="show = true" @mouseleave="show = false">
            <Icon icon="material-symbols:info-outline" class="w-8 h-8" />
        </div>
        
        <!-- Tooltip -->
        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
            <div v-if="show" class="absolute left-1/2 -translate-x-1/2 mt-2 w-max max-w-xs px-3 py-2 text-sm rounded-lg shadow-lg bg-gray-800 text-white z-10">
                <slot></slot>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref } from "vue"

const show = ref(false);
</script>

<style scoped>

</style>