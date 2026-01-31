<script setup lang="ts">
import { Motion } from 'motion-v'
import RecipeCard from './RecipeCard.vue'

defineProps<{
    recipes: Array<{
        id: number
        title: string
        description: string
        image: string | null
    }>
}>()
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
                <h2 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] text-center mb-12">
                    Rezepte
                </h2>
            </Motion>
            <div v-if="recipes.length === 0" class="text-center text-[var(--color-warm-gray)]">
                Noch keine Rezepte vorhanden.
            </div>
            <div v-else class="flex flex-wrap justify-center gap-6 md:gap-8">
                <div
                    v-for="recipe in recipes"
                    :key="recipe.id"
                    class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.5rem)] max-w-sm"
                >
                    <RecipeCard :recipe="recipe" />
                </div>
            </div>
        </div>
    </section>
</template>
