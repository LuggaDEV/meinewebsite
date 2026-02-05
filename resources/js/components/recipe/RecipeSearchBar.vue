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
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-warm-gray)]" />
        <Input
            v-model="localValue"
            type="text"
            placeholder="Suche nach Titel oder Beschreibung..."
            class="pl-10 pr-10"
            aria-label="Rezeptsuche"
        />
        <button
            v-if="localValue"
            type="button"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-full hover:bg-[var(--color-forest)]/10 transition-colors"
            aria-label="Suche löschen"
        >
            <X class="w-4 h-4 text-[var(--color-warm-gray)]" />
        </button>
    </div>
</template>
