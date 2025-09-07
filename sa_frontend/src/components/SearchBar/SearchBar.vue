<template>
    <div class="relative w-full">
        <input type="text" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" @keyup.enter="$emit('enter')" :placeholder="placeholder" class="mb-4 px-4 py-2 border border-gray-300 rounded-lg w-full pr-10">
        <button v-if="modelValue" @click="clear" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
            <Icon icon="material-symbols:close" class="w-5 h-5" />
        </button>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Cari dataset...'
    }
})

const emit = defineEmits(['update:modelValue', 'enter'])

const clear = () => {
    emit('update:modelValue', '')  // reset input
    emit('enter')                  // trigger ulang fetch data
}
</script>

<style scoped>

</style>