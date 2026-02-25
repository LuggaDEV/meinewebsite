<script setup lang="ts">
import { ref, computed } from 'vue'
import { Motion } from 'motion-v'
import { Search } from 'lucide-vue-next'
import RecipeCard from './RecipeCard.vue'
import RecipeSearchBar from './RecipeSearchBar.vue'
import RecipeFilters from './RecipeFilters.vue'
import ActiveFilterBadges from './ActiveFilterBadges.vue'
import type { Recipe, ActiveFilters } from '@/types/recipe'

const props = defineProps<{
    recipes: Recipe[]
}>()

const searchQuery = ref<string>('')
const activeFilters = ref<ActiveFilters>({
    prepTime: [],
    cookTime: [],
    servings: []
})

// Filter logic functions
function matchesPrepTimeFilter(recipe: Recipe, filters: string[]): boolean {
    if (!recipe.prep_time) return false

    return filters.some((filter) => {
        switch (filter) {
            case '< 15':
                return recipe.prep_time! < 15
            case '15-30':
                return recipe.prep_time! >= 15 && recipe.prep_time! <= 30
            case '30-60':
                return recipe.prep_time! > 30 && recipe.prep_time! <= 60
            case '> 60':
                return recipe.prep_time! > 60
            default:
                return false
        }
    })
}

function matchesCookTimeFilter(recipe: Recipe, filters: string[]): boolean {
    if (!recipe.cook_time) return false

    return filters.some((filter) => {
        switch (filter) {
            case '< 30':
                return recipe.cook_time! < 30
            case '30-60':
                return recipe.cook_time! >= 30 && recipe.cook_time! <= 60
            case '> 60':
                return recipe.cook_time! > 60
            default:
                return false
        }
    })
}

function matchesServingsFilter(recipe: Recipe, filters: string[]): boolean {
    if (!recipe.servings) return false

    return filters.some((filter) => {
        switch (filter) {
            case '1-2':
                return recipe.servings! <= 2
            case '3-4':
                return recipe.servings! >= 3 && recipe.servings! <= 4
            case '5-6':
                return recipe.servings! >= 5 && recipe.servings! <= 6
            case '> 6':
                return recipe.servings! > 6
            default:
                return false
        }
    })
}

// Main filtered recipes computed property
const filteredRecipes = computed(() => {
    return props.recipes.filter((recipe) => {
        // 1. Search query filter
        if (searchQuery.value) {
            const query = searchQuery.value.toLowerCase()
            const matchesTitle = recipe.title.toLowerCase().includes(query)
            const matchesDesc = recipe.description.toLowerCase().includes(query)
            if (!matchesTitle && !matchesDesc) return false
        }

        // 2. Prep time filter
        if (activeFilters.value.prepTime.length > 0) {
            if (!matchesPrepTimeFilter(recipe, activeFilters.value.prepTime)) {
                return false
            }
        }

        // 3. Cook time filter
        if (activeFilters.value.cookTime.length > 0) {
            if (!matchesCookTimeFilter(recipe, activeFilters.value.cookTime)) {
                return false
            }
        }

        // 4. Servings filter
        if (activeFilters.value.servings.length > 0) {
            if (!matchesServingsFilter(recipe, activeFilters.value.servings)) {
                return false
            }
        }

        return true
    })
})

const hasActiveFilters = computed(() => {
    return (
        searchQuery.value !== '' ||
        activeFilters.value.prepTime.length > 0 ||
        activeFilters.value.cookTime.length > 0 ||
        activeFilters.value.servings.length > 0
    )
})

function removeFilter(category: keyof ActiveFilters, value: string) {
    const currentFilters = [...activeFilters.value[category]]
    const index = currentFilters.indexOf(value)

    if (index > -1) {
        currentFilters.splice(index, 1)
        activeFilters.value = {
            ...activeFilters.value,
            [category]: currentFilters
        }
    }
}

function clearAllFilters() {
    searchQuery.value = ''
    activeFilters.value = {
        prepTime: [],
        cookTime: [],
        servings: []
    }
}
</script>

<template>
    <section id="recipes" class="py-16 md:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, ease: 'easeOut' }"
                :viewport="{ once: true }"
            >
                <h2 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] dark:text-foreground text-center mb-12">
                    Rezepte
                </h2>
            </Motion>

            <!-- Search and Filter Controls -->
            <div class="mb-8 space-y-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <RecipeSearchBar v-model="searchQuery" class="flex-1" />
                    <RecipeFilters v-model="activeFilters" />
                </div>

                <ActiveFilterBadges
                    v-if="hasActiveFilters"
                    :search-query="searchQuery"
                    :active-filters="activeFilters"
                    @clear-search="searchQuery = ''"
                    @remove-filter="removeFilter"
                    @clear-all="clearAllFilters"
                />

                <!-- Result Count -->
                <div v-if="hasActiveFilters && filteredRecipes.length > 0" class="text-center text-sm text-[var(--color-warm-gray)] dark:text-muted-foreground">
                    {{ filteredRecipes.length }} Rezept{{ filteredRecipes.length !== 1 ? 'e' : '' }} gefunden
                </div>
            </div>

            <!-- Empty State: No recipes in database -->
            <div v-if="recipes.length === 0" class="text-center text-[var(--color-warm-gray)]">
                Noch keine Rezepte vorhanden.
            </div>

            <!-- Empty State: No search results -->
            <div v-else-if="filteredRecipes.length === 0" class="text-center py-12">
                <Search class="w-16 h-16 mx-auto mb-4 text-[var(--color-warm-gray)]/30" />
                <p class="text-lg font-medium text-[var(--color-forest)] mb-2">Keine Rezepte gefunden</p>
                <p class="text-sm text-[var(--color-warm-gray)] mb-6">
                    Versuche es mit anderen Suchbegriffen oder Filtern.
                </p>
                <button
                    @click="clearAllFilters"
                    class="px-6 py-3 text-[var(--color-forest)] font-medium rounded-lg border-2 border-[var(--color-forest)]/20 hover:border-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/5 transition-colors"
                >
                    Alle Filter zurücksetzen
                </button>
            </div>

            <!-- Recipe Grid -->
            <div v-else class="flex flex-wrap justify-center gap-6 md:gap-8">
                <div
                    v-for="recipe in filteredRecipes"
                    :key="recipe.id"
                    class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)] max-w-sm"
                >
                    <RecipeCard :recipe="recipe" />
                </div>
            </div>
        </div>
    </section>
</template>
