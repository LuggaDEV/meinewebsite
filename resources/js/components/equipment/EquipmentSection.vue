<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { Search } from 'lucide-vue-next'
import EquipmentCard from './EquipmentCard.vue'
import type { ActiveFilters } from '@/types/equipment'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'

const props = defineProps<{
    equipment: any,
    filters: { search: string, categories: string[] },
    allCategories: string[],
}>()

const searchQuery = ref<string>(props.filters?.search ?? '')
const activeFilters = ref<ActiveFilters>({
    categories: [...(props.filters?.categories ?? [])]
})

// Sync local state when props change (e.g. Inertia visit, back/forward)
let isSyncingFromProps = false
watch(() => props.filters, (newFilters) => {
    if (!newFilters) return
    isSyncingFromProps = true
    searchQuery.value = newFilters.search ?? ''
    activeFilters.value.categories = Array.isArray(newFilters.categories) ? [...newFilters.categories] : []
    nextTick(() => { isSyncingFromProps = false })
}, { deep: true })

// Extract unique categories from server-provided allCategories (safe default)
const availableCategories = computed(() => {
    const list = props.allCategories ?? []
    if (!Array.isArray(list)) return []
    return list.slice().sort()
})

// Items from server (paginated) or raw array fallback (always return array)
const items = computed(() => {
    const eq = props.equipment
    if (!eq) return []
    // Laravel paginator: serialized as { data: [...], current_page, ... }
    const data = eq.data ?? eq.items
    if (Array.isArray(data)) return data
    if (Array.isArray(eq)) return eq
    return []
})

let searchTimeout: ReturnType<typeof setTimeout> | null = null

function submitFilters(): void {
    if (isSyncingFromProps) return
    const params: Record<string, string> = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (activeFilters.value.categories.length > 0) params.categories = activeFilters.value.categories.join(',')

    router.get('/equipment', params, { preserveScroll: true })
}

// Toggle category filter and submit to server
function toggleCategory(category: string): void {
    const index = activeFilters.value.categories.indexOf(category)
    if (index === -1) {
        activeFilters.value.categories.push(category)
    } else {
        activeFilters.value.categories.splice(index, 1)
    }
    submitFilters()
}

// Clear all filters and submit
function clearAllFilters(): void {
    activeFilters.value.categories = []
    searchQuery.value = ''
    submitFilters()
}

// Watch search input and debounce submissions (only when user changes input, not when syncing from props)
watch(searchQuery, () => {
    if (isSyncingFromProps) return
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        submitFilters()
    }, 500)
})

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return activeFilters.value.categories.length > 0 || (searchQuery.value && searchQuery.value.length > 0)
})
</script>

<template>
    <section id="equipment" class="py-12 md:py-16 lg:py-20 bg-[var(--color-cream)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Search and Filter Header -->
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.5, delay: 0.2 }"
            >
                <div class="mb-8 md:mb-12">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <Label for="search" class="sr-only">Equipment suchen</Label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[var(--color-warm-gray)]" />
                            <Input
                                id="search"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Equipment suchen..."
                                class="pl-10"
                            />
                        </div>
                    </div>

                    <!-- Category Filters -->
                    <div v-if="availableCategories.length > 0" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium text-[var(--color-forest)]">
                                Kategorien
                            </h3>
                            <button
                                v-if="hasActiveFilters"
                                @click="clearAllFilters"
                                class="text-sm text-[var(--color-terracotta)] hover:underline"
                            >
                                Alle Filter zurücksetzen
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <label
                                v-for="category in availableCategories"
                                :key="category"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border border-[var(--color-forest)]/20 hover:border-[var(--color-forest)]/40 cursor-pointer transition-colors"
                                :class="{
                                    'bg-[var(--color-forest)] text-white border-[var(--color-forest)]': activeFilters.categories.includes(category),
                                    'bg-white': !activeFilters.categories.includes(category)
                                }"
                                @click.prevent="toggleCategory(category)"
                            >
                                <Checkbox
                                    :id="`category-${category}`"
                                    :checked="activeFilters.categories.includes(category)"
                                    class="hidden"
                                />
                                <span class="text-sm font-medium">{{ category }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </Motion>

            <!-- Equipment Grid -->
            <div v-if="items.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <EquipmentCard
                    v-for="item in items"
                    :key="item.id"
                    :equipment="item"
                />
            </div>

            <!-- Empty State -->
            <Motion
                v-else
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.3 }"
                class="text-center py-12"
            >
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto text-[var(--color-warm-gray)]/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-xl font-heading font-semibold text-[var(--color-forest)] mb-2">
                        Keine Ergebnisse gefunden
                    </h3>
                    <p class="text-[var(--color-warm-gray)] mb-6">
                        Versuche es mit einem anderen Suchbegriff oder passe deine Filter an.
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        @click="clearAllFilters"
                        class="px-6 py-2 bg-[var(--color-forest)] text-white rounded-lg hover:bg-[var(--color-terracotta)] transition-colors"
                    >
                        Filter zurücksetzen
                    </button>
                </div>
            </Motion>
        </div>
    </section>
</template>
