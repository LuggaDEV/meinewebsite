<script setup lang="ts">
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import Badge from '@/components/ui/badge/Badge.vue'
import type { ActiveFilters } from '@/types/recipe'

const props = defineProps<{
    searchQuery: string
    activeFilters: ActiveFilters
}>()

const emit = defineEmits<{
    'clear-search': []
    'remove-filter': [category: keyof ActiveFilters, value: string]
    'clear-all': []
}>()

interface FilterBadge {
    category: keyof ActiveFilters
    value: string
    label: string
}

const filterLabels: Record<keyof ActiveFilters, Record<string, string>> = {
    prepTime: {
        '< 15': 'Unter 15 Min.',
        '15-30': '15-30 Min.',
        '30-60': '30-60 Min.',
        '> 60': 'Über 60 Min.'
    },
    cookTime: {
        '< 30': 'Unter 30 Min.',
        '30-60': '30-60 Min.',
        '> 60': 'Über 60 Min.'
    },
    servings: {
        '1-2': '1-2 Portionen',
        '3-4': '3-4 Portionen',
        '5-6': '5-6 Portionen',
        '> 6': 'Über 6 Portionen'
    }
}

const categoryLabels: Record<keyof ActiveFilters, string> = {
    prepTime: 'Zubereitungszeit',
    cookTime: 'Kochzeit',
    servings: 'Portionen'
}

const activeBadges = computed<FilterBadge[]>(() => {
    const badges: FilterBadge[] = []

    Object.entries(props.activeFilters).forEach(([category, values]) => {
        const cat = category as keyof ActiveFilters
        values.forEach((value) => {
            badges.push({
                category: cat,
                value,
                label: `${categoryLabels[cat]}: ${filterLabels[cat][value]}`
            })
        })
    })

    return badges
})

const hasAnyFilters = computed(() => {
    return props.searchQuery !== '' || activeBadges.value.length > 0
})

const shouldShowClearAll = computed(() => {
    const filterCount = activeBadges.value.length + (props.searchQuery ? 1 : 0)
    return filterCount > 1
})
</script>

<template>
    <div v-if="hasAnyFilters" class="flex flex-wrap items-center gap-2">
        <Badge
            v-if="searchQuery"
            variant="secondary"
            class="gap-1 pr-1"
        >
            <span class="text-xs">Suche: {{ searchQuery }}</span>
            <button
                type="button"
                @click="emit('clear-search')"
                class="ml-1 p-0.5 rounded-full hover:bg-[var(--color-forest)]/20 transition-colors"
                aria-label="Suche entfernen"
            >
                <X class="w-3 h-3" />
            </button>
        </Badge>

        <Badge
            v-for="badge in activeBadges"
            :key="`${badge.category}-${badge.value}`"
            variant="secondary"
            class="gap-1 pr-1"
        >
            <span class="text-xs">{{ badge.label }}</span>
            <button
                type="button"
                @click="emit('remove-filter', badge.category, badge.value)"
                class="ml-1 p-0.5 rounded-full hover:bg-[var(--color-forest)]/20 transition-colors"
                aria-label="Filter entfernen"
            >
                <X class="w-3 h-3" />
            </button>
        </Badge>

        <button
            v-if="shouldShowClearAll"
            type="button"
            @click="emit('clear-all')"
            class="text-xs text-[var(--color-terracotta)] hover:underline font-medium"
        >
            Alle Filter zurücksetzen
        </button>
    </div>
</template>
