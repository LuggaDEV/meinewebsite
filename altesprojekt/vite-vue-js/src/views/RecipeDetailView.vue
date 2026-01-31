<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Motion } from 'motion-v'
import { getRecipe } from '../services/recipeApi.js'

const route = useRoute()
const router = useRouter()

const recipe = ref(null)
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const id = parseInt(route.params.id)
    recipe.value = await getRecipe(id)
    if (!recipe.value) {
      error.value = 'Rezept nicht gefunden'
    }
  } catch (err) {
    error.value = 'Fehler beim Laden des Rezepts'
    console.error(err)
  } finally {
    loading.value = false
  }
})

function goBack() {
  router.push('/')
}
</script>

<template>
  <div v-if="loading" class="py-12 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <p class="text-[var(--color-warm-gray)]">Rezept wird geladen...</p>
    </div>
  </div>

  <div v-else-if="error || !recipe" class="py-12 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h1 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] mb-4">
        {{ error || 'Rezept nicht gefunden' }}
      </h1>
      <p class="text-[var(--color-warm-gray)] mb-6">
        Das angeforderte Rezept konnte nicht gefunden werden.
      </p>
      <router-link
        to="/"
        class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[var(--color-forest)]"
      >
        Zurück zu den Rezepten
      </router-link>
    </div>
  </div>

  <div v-else class="py-12 md:py-16">
    <Motion
      :initial="{ opacity: 0 }"
      :animate="{ opacity: 1 }"
      :transition="{ duration: 0.4 }"
      class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"
    >
      <Motion
        :initial="{ opacity: 0, x: -20 }"
        :animate="{ opacity: 1, x: 0 }"
        :transition="{ duration: 0.4, delay: 0.1 }"
      >
        <button
          @click="goBack"
          class="mb-6 inline-flex items-center gap-2 text-[var(--color-forest)] hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Zurück zu den Rezepten
        </button>
      </Motion>

      <article>
        <Motion
          :initial="{ opacity: 0, scale: 0.95 }"
          :animate="{ opacity: 1, scale: 1 }"
          :transition="{ duration: 0.5, delay: 0.2 }"
        >
          <div class="aspect-[16/9] overflow-hidden rounded-xl mb-8 bg-[var(--color-forest)]/5">
            <img
              :src="recipe.image"
              :alt="recipe.title"
              class="w-full h-full object-cover"
            />
          </div>
        </Motion>

        <Motion
          :initial="{ opacity: 0, y: 20 }"
          :animate="{ opacity: 1, y: 0 }"
          :transition="{ duration: 0.5, delay: 0.3 }"
        >
          <header class="mb-8">
            <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-semibold text-[var(--color-forest)] mb-4">
              {{ recipe.title }}
            </h1>
            <p class="text-lg text-[var(--color-warm-gray)] mb-6">
              {{ recipe.description }}
            </p>
            <div class="flex flex-wrap gap-4 text-sm text-[var(--color-warm-gray)]">
            <span v-if="recipe.servings" class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {{ recipe.servings }} Portionen
            </span>
            <span v-if="recipe.prepTime" class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Vorbereitung: {{ recipe.prepTime }} Min.
            </span>
            <span v-if="recipe.cookTime" class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Zubereitung: {{ recipe.cookTime }} Min.
            </span>
          </div>
          </header>
        </Motion>

        <Motion
          :initial="{ opacity: 0, y: 20 }"
          :animate="{ opacity: 1, y: 0 }"
          :transition="{ duration: 0.5, delay: 0.4 }"
        >
          <div class="grid md:grid-cols-2 gap-8 md:gap-12">
          <section>
            <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-6">
              Zutaten
            </h2>
            <ul class="space-y-3">
              <li
                v-for="(ingredient, index) in recipe.ingredients"
                :key="index"
                class="flex items-start gap-3 text-[var(--color-warm-gray)]"
              >
                <span class="text-[var(--color-terracotta)] mt-1">•</span>
                <span>{{ ingredient }}</span>
              </li>
            </ul>
          </section>

          <section>
            <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-6">
              Zubereitung
            </h2>
            <ol class="space-y-4">
              <li
                v-for="(instruction, index) in recipe.instructions"
                :key="index"
                class="flex gap-4 text-[var(--color-warm-gray)]"
              >
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-terracotta)] text-white font-semibold flex items-center justify-center text-sm">
                  {{ index + 1 }}
                </span>
                <span class="flex-1">{{ instruction }}</span>
              </li>
            </ol>
          </section>
          </div>
        </Motion>
      </article>
    </Motion>
  </div>
</template>
