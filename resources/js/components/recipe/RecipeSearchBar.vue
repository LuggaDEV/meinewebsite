<script setup lang="ts">
import { ref, watch } from 'vue'
import { watchDebounced } from '@vueuse/core'
import { Search, X } from 'lucide-vue-next'
import Input from '@/components/ui/input/Input.vue'

const props = defineProps<{
    modelValue: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const localValue = ref(props.modelValue)

// Debounced update to parent
watchDebounced(
    localValue,
    (newValue) => {
        emit('update:modelValue', newValue)
    },
    { debounce: 300, maxWait: 1000 }
)

// Sync external changes
watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = newValue
    }
)

function clearSearch() {
    localValue.value = ''
    emit('update:modelValue', '')
}
</script>

<template>
    <div class="relative">
        <Search class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500 dark:text-slate-500" />
        <Input
            v-model="localValue"
            type="text"
            placeholder="Suche nach Titel oder Beschreibung..."
            class="border-slate-200 bg-white pl-10 pr-10 text-slate-900 shadow-sm placeholder:text-slate-400 dark:border-slate-200 dark:bg-white dark:text-slate-900 dark:placeholder:text-slate-400"
            aria-label="Rezeptsuche"
        />
        <button
            v-if="localValue"
            type="button"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1 transition-colors hover:bg-slate-100 dark:hover:bg-slate-100"
            aria-label="Suche löschen"
        >
            <X class="h-4 w-4 text-slate-500 dark:text-slate-500" />
        </button>
    </div>
</template>
