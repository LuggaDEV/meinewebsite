<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'

const instagramUrl = 'https://www.instagram.com/luca_themann/'

defineProps<{
    recipes: Array<{
        id: number
        title: string
        image: string | null
    }>
}>()
</script>

<template>
    <section id="instagram" class="py-16 md:py-24 bg-white/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, ease: 'easeOut' }"
                :viewport="{ once: true }"
            >
                <h2 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] text-center mb-4">
                    Folge mir auf Instagram
                </h2>
                <p class="text-center text-[var(--color-warm-gray)] mb-12 max-w-xl mx-auto">
                    Aktuelle Kreationen und Einblicke hinter die Kulissen.
                </p>
            </Motion>
            <div v-if="recipes.length === 0" class="text-center text-[var(--color-warm-gray)] py-8">
                Noch keine Rezepte vorhanden.
            </div>
            <div v-else class="flex flex-wrap justify-center gap-2 md:gap-4">
                <Motion
                    v-for="(recipe, i) in recipes"
                    :key="recipe.id"
                    :initial="{ opacity: 0, scale: 0.9 }"
                    :in-view="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.4, delay: i * 0.1, ease: 'easeOut' }"
                    :viewport="{ once: true, margin: '-50px' }"
                    class="w-[calc(50%-0.25rem)] sm:w-[calc(33.333%-0.67rem)] md:w-[calc(25%-0.75rem)] lg:w-[calc(16.666%-0.83rem)] max-w-[200px]"
                >
                    <Link
                        :href="`/recipe/${recipe.id}`"
                        class="block aspect-square overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] group w-full"
                    >
                        <img
                            v-if="recipe.image"
                            :src="recipe.image"
                            :alt="recipe.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-[var(--color-forest)]/5">
                            <svg class="w-12 h-12 text-[var(--color-forest)]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </Link>
                </Motion>
            </div>
            <div class="mt-10 text-center">
                <a
                    :href="instagramUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                    @luca_themann auf Instagram
                </a>
            </div>
        </div>
    </section>
</template>
