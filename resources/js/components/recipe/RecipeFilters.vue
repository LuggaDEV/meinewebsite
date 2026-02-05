<script setup lang="ts">
import { computed } from 'vue'
import { Filter } from 'lucide-vue-next'
import Button from '@/components/ui/button/Button.vue'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuCheckboxItem,
    DropdownMenuGroup
} from '@/components/ui/dropdown-menu'
import type { ActiveFilters, FilterOption } from '@/types/recipe'

const props = defineProps<{
    modelValue: ActiveFilters
}>()

const emit = defineEmits<{
    'update:modelValue': [value: ActiveFilters]
}>()

const prepTimeOptions: FilterOption[] = [
    { value: '< 15', label: 'Unter 15 Min.' },
    { value: '15-30', label: '15-30 Min.' },
    { value: '30-60', label: '30-60 Min.' },
    { value: '> 60', label: 'Über 60 Min.' }
]

const cookTimeOptions: FilterOption[] = [
    { value: '< 30', label: 'Unter 30 Min.' },
    { value: '30-60', label: '30-60 Min.' },
    { value: '> 60', label: 'Über 60 Min.' }
]

const servingsOptions: FilterOption[] = [
    { value: '1-2', label: '1-2 Portionen' },
    { value: '3-4', label: '3-4 Portionen' },
    { value: '5-6', label: '5-6 Portionen' },
    { value: '> 6', label: 'Über 6 Portionen' }
]

const filterCount = computed(() => {
    return props.modelValue.prepTime.length +
           props.modelValue.cookTime.length +
           props.modelValue.servings.length
})

function toggleFilter(category: keyof ActiveFilters, value: string, checked: boolean | string) {
    // Handle both boolean and CheckedState ('indeterminate' | boolean)
    const isChecked = checked === true || checked === 'indeterminate'

    const currentFilters = [...props.modelValue[category]]
    const index = currentFilters.indexOf(value)

    if (isChecked && index === -1) {
        // Add filter if checked and not present
        currentFilters.push(value)
    } else if (!isChecked && index > -1) {
        // Remove filter if unchecked and present
        currentFilters.splice(index, 1)
    }

    const newFilters = {
        ...props.modelValue,
        [category]: currentFilters
    }

    emit('update:modelValue', newFilters)
}

function clearFilters() {
    emit('update:modelValue', {
        prepTime: [],
        cookTime: [],
        servings: []
    })
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="outline"
                class="gap-2 transition-all duration-200 hover:border-[var(--color-forest)] hover:bg-[var(--color-forest)]/5"
                :class="filterCount > 0 ? 'border-[var(--color-terracotta)] bg-[var(--color-terracotta)]/5' : ''"
            >
                <Filter class="w-4 h-4" :class="filterCount > 0 ? 'text-[var(--color-terracotta)]' : ''" />
                <span :class="filterCount > 0 ? 'font-semibold text-[var(--color-terracotta)]' : ''">
                    {{ filterCount > 0 ? `Filter (${filterCount})` : 'Filter' }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
            class="w-64 p-2"
            side="bottom"
            align="start"
            :side-offset="8"
            :modal="false"
            :avoid-collisions="false"
        >
            <div class="space-y-3">
                <div>
                    <DropdownMenuLabel class="text-xs font-semibold text-[var(--color-forest)] uppercase tracking-wider px-2">
                        Zubereitungszeit
                    </DropdownMenuLabel>
                    <DropdownMenuGroup class="mt-1">
                        <DropdownMenuCheckboxItem
                            v-for="option in prepTimeOptions"
                            :key="option.value"
                            :checked="modelValue.prepTime.includes(option.value)"
                            :class="{
                                'bg-[var(--color-terracotta)]/10 text-[var(--color-terracotta)] font-medium':
                                    modelValue.prepTime.includes(option.value)
                            }"
                            class="cursor-pointer hover:bg-[var(--color-forest)]/5 transition-all duration-200 rounded-md"
                            @select.prevent="(event) => {
                                event.preventDefault()
                                toggleFilter('prepTime', option.value, !modelValue.prepTime.includes(option.value))
                            }"
                        >
                            {{ option.label }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuGroup>
                </div>

                <DropdownMenuSeparator class="bg-[var(--color-forest)]/10" />

                <div>
                    <DropdownMenuLabel class="text-xs font-semibold text-[var(--color-forest)] uppercase tracking-wider px-2">
                        Kochzeit
                    </DropdownMenuLabel>
                    <DropdownMenuGroup class="mt-1">
                        <DropdownMenuCheckboxItem
                            v-for="option in cookTimeOptions"
                            :key="option.value"
                            :checked="modelValue.cookTime.includes(option.value)"
                            :class="{
                                'bg-[var(--color-terracotta)]/10 text-[var(--color-terracotta)] font-medium':
                                    modelValue.cookTime.includes(option.value)
                            }"
                            class="cursor-pointer hover:bg-[var(--color-forest)]/5 transition-all duration-200 rounded-md"
                            @select.prevent="(event) => {
                                event.preventDefault()
                                toggleFilter('cookTime', option.value, !modelValue.cookTime.includes(option.value))
                            }"
                        >
                            {{ option.label }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuGroup>
                </div>

                <DropdownMenuSeparator class="bg-[var(--color-forest)]/10" />

                <div>
                    <DropdownMenuLabel class="text-xs font-semibold text-[var(--color-forest)] uppercase tracking-wider px-2">
                        Portionen
                    </DropdownMenuLabel>
                    <DropdownMenuGroup class="mt-1">
                        <DropdownMenuCheckboxItem
                            v-for="option in servingsOptions"
                            :key="option.value"
                            :checked="modelValue.servings.includes(option.value)"
                            :class="{
                                'bg-[var(--color-terracotta)]/10 text-[var(--color-terracotta)] font-medium':
                                    modelValue.servings.includes(option.value)
                            }"
                            class="cursor-pointer hover:bg-[var(--color-forest)]/5 transition-all duration-200 rounded-md"
                            @select.prevent="(event) => {
                                event.preventDefault()
                                toggleFilter('servings', option.value, !modelValue.servings.includes(option.value))
                            }"
                        >
                            {{ option.label }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuGroup>
                </div>

                <template v-if="filterCount > 0">
                    <DropdownMenuSeparator class="bg-[var(--color-forest)]/10" />
                    <button
                        @click="clearFilters"
                        class="w-full px-3 py-2 text-sm font-medium text-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/10 rounded-md transition-all duration-200 text-left flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Alle Filter zurücksetzen
                    </button>
                </template>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
