<template>
    <div v-if="isLoading" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
        <!-- Loader -->
        <div class="flex flex-col items-center gap-6">
            <PulseLoader class="scale-150" color="#3b82f6" />
            
            <!-- Judul -->
            <span class="text-2xl md:text-3xl font-semibold text-slate-700 animate-pulse">
                {{ title }}
            </span>
            
            <!-- Sub-teks -->
            <p class="text-slate-500 text-sm md:text-base text-center max-w-md">
                {{ subtitle }}
            </p>
            
            <!-- Timer -->
            <p class="text-slate-400 text-sm">
                Waktu tunggu: <span class="font-semibold">{{ formattedTime }}</span>
            </p>
        </div>
    </div>
</template>

<script setup>
import { defineProps, ref, watch, computed, onUnmounted } from 'vue'
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

const props = defineProps({
    isLoading: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Sedang proses pembersihan dataset'
    },
    subtitle: {
        type: String,
        default: 'Mohon tunggu sebentar, sistem sedang memproses data Anda...'
    }
})

const elapsedTime = ref(0)
let timer = null

// Hitung format menit:detik
const formattedTime = computed(() => {
    const minutes = Math.floor(elapsedTime.value / 60)
    const seconds = elapsedTime.value % 60
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})

// Pantau perubahan isLoading
watch(
() => props.isLoading,
(newVal) => {
    if (newVal) {
        // Reset & mulai timer ketika loader muncul
        elapsedTime.value = 0
        timer = setInterval(() => {
            elapsedTime.value++
        }, 1000)
    } else {
        // Hentikan timer ketika loader ditutup
        clearInterval(timer)
        timer = null
    }
}
)

// Bersihkan interval kalau komponen di-unmount
onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>
