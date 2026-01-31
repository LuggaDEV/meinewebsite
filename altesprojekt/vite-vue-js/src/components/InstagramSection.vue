<script setup>
import { ref, onMounted } from 'vue'
import { Motion } from 'motion-v'
import { RouterLink } from 'vue-router'
import { getRecipes } from '../services/recipeApi.js'

const instagramUrl = 'https://www.instagram.com/luca_themann/'
const recipes = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const allRecipes = await getRecipes()
    // Zeige die letzten 6 Rezepte (oder alle, wenn weniger vorhanden)
    recipes.value = allRecipes.slice(-6).reverse()
  } catch (error) {
    console.error('Error loading recipes:', error)
    recipes.value = []
  } finally {
    loading.value = false
  }
})
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
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 md:gap-4 justify-items-center">
        <div
          v-for="i in 6"
          :key="i"
          class="aspect-square bg-[var(--color-forest)]/10 rounded-lg animate-pulse w-full max-w-[200px]"
        />
      </div>
      <div v-else-if="recipes.length === 0" class="text-center text-[var(--color-warm-gray)] py-8">
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
          <RouterLink
            :to="{ name: 'recipe', params: { id: recipe.id } }"
            class="block aspect-square overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] group w-full"
          >
            <img
              :src="recipe.image"
              :alt="recipe.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          </RouterLink>
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
